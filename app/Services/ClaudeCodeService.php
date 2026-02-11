<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class ClaudeCodeService
{
    private string $projectRoot;
    private string $apiKey;
    private string $model;
    private int $maxTokens;

    private const SESSION_TTL = 1800; // 30 min
    private const HISTORY_LIMIT = 40;
    private const EDIT_TTL = 600; // 10 min
    private const MAX_TOOL_ITERATIONS = 10;
    private const MAX_FILE_SIZE = 50 * 1024; // 50KB

    private const FORBIDDEN_PATHS = [
        '.env',
        'storage/',
        'vendor/',
        'node_modules/',
        '.git/',
    ];

    private const ALLOWED_COMMANDS = [
        'php artisan',
        'git status',
        'git log',
        'git diff',
        'composer show',
    ];

    public function __construct()
    {
        $this->projectRoot = '/var/www/html';
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.anthropic.model');
        $this->maxTokens = config('services.anthropic.max_tokens');
    }

    public static function make(): self
    {
        return new self();
    }

    public static function isAdmin(string $chatId): bool
    {
        $adminChatId = config('services.anthropic.admin_chat_id');

        return $adminChatId && (string) $chatId === (string) $adminChatId;
    }

    // ── Sessions ──

    public function isSessionActive(string $chatId): bool
    {
        return Cache::has("claude_session:{$chatId}");
    }

    public function startSession(string $chatId): void
    {
        Cache::put("claude_session:{$chatId}", true, self::SESSION_TTL);
        $this->clearHistory($chatId);
    }

    public function endSession(string $chatId): void
    {
        Cache::forget("claude_session:{$chatId}");
        $this->clearHistory($chatId);
    }

    // ── History ──

    private function getHistory(string $chatId): array
    {
        return Cache::get("claude_history:{$chatId}", []);
    }

    private function addToHistory(string $chatId, array $message): void
    {
        $history = $this->getHistory($chatId);
        $history[] = $message;

        // Keep only last N messages
        if (count($history) > self::HISTORY_LIMIT) {
            $history = array_slice($history, -self::HISTORY_LIMIT);
        }

        Cache::put("claude_history:{$chatId}", $history, self::SESSION_TTL);
    }

    private function clearHistory(string $chatId): void
    {
        Cache::forget("claude_history:{$chatId}");
    }

    // ── Main Chat ──

    /**
     * @return array<array{type: string, content: string, edit_id?: string, file?: string, description?: string, diff?: string}>
     */
    public function chat(string $chatId, string $message): array
    {
        // Refresh session TTL
        Cache::put("claude_session:{$chatId}", true, self::SESSION_TTL);

        // Add user message to history
        $this->addToHistory($chatId, ['role' => 'user', 'content' => $message]);

        $actions = [];
        $iterations = 0;

        while ($iterations < self::MAX_TOOL_ITERATIONS) {
            $iterations++;

            $response = $this->callApi($chatId);

            if (!$response) {
                $actions[] = ['type' => 'text', 'content' => 'API error — no response from Claude.'];
                break;
            }

            $stopReason = $response['stop_reason'] ?? 'end_turn';
            $content = $response['content'] ?? [];

            // Collect text blocks
            $textParts = [];
            $toolUses = [];

            foreach ($content as $block) {
                if ($block['type'] === 'text' && !empty($block['text'])) {
                    $textParts[] = $block['text'];
                } elseif ($block['type'] === 'tool_use') {
                    $toolUses[] = $block;
                }
            }

            if (!empty($textParts)) {
                $text = implode("\n", $textParts);
                $actions[] = ['type' => 'text', 'content' => $text];
            }

            // Save assistant message to history
            $this->addToHistory($chatId, ['role' => 'assistant', 'content' => $content]);

            // If no tool calls, we're done
            if ($stopReason !== 'tool_use' || empty($toolUses)) {
                break;
            }

            // Execute tools and add results
            $toolResults = [];
            foreach ($toolUses as $toolUse) {
                $result = $this->executeTool($toolUse['name'], $toolUse['input'] ?? []);

                // Handle pending edits specially
                if ($result['type'] === 'pending_edit') {
                    $actions[] = $result;
                    $toolResults[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUse['id'],
                        'content' => "Edit saved as pending (ID: {$result['edit_id']}). Awaiting user approval via Telegram button.",
                    ];
                } else {
                    $toolResults[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUse['id'],
                        'content' => $result['content'],
                    ];
                }
            }

            $this->addToHistory($chatId, ['role' => 'user', 'content' => $toolResults]);
        }

        return $actions;
    }

    // ── Anthropic API ──

    private function callApi(string $chatId): ?array
    {
        $messages = $this->getHistory($chatId);

        $response = Http::timeout(120)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'system' => $this->getSystemPrompt(),
                'tools' => $this->getToolDefinitions(),
                'messages' => $messages,
            ]);

        if (!$response->ok()) {
            logger()->error('Claude API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a code assistant for the ChurchHub (Ministrify) project — a Laravel 10 + Blade + Alpine.js + Tailwind CSS church management app.

Project root: /var/www/html
Stack: PHP 8.2, Laravel 10, MySQL 8, Redis, Docker

RULES:
- Answer in Russian
- When editing files, use the edit_file tool. Edits require user approval before applying.
- NEVER read or modify .env files — they contain secrets
- NEVER run destructive commands (migrate:fresh, db:wipe, rm -rf, etc.)
- Keep edits minimal and focused
- When showing code changes, explain what and why
- For file reads, show relevant parts, not entire files
- Use search_code to find things before making assumptions
PROMPT;
    }

    private function getToolDefinitions(): array
    {
        return [
            [
                'name' => 'read_file',
                'description' => 'Read the contents of a file. Returns file content with line numbers.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'path' => [
                            'type' => 'string',
                            'description' => 'File path relative to project root (e.g. app/Models/User.php)',
                        ],
                        'start_line' => [
                            'type' => 'integer',
                            'description' => 'Start reading from this line (1-based). Optional.',
                        ],
                        'end_line' => [
                            'type' => 'integer',
                            'description' => 'Stop reading at this line (inclusive). Optional.',
                        ],
                    ],
                    'required' => ['path'],
                ],
            ],
            [
                'name' => 'list_files',
                'description' => 'List files and directories in a given path.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'path' => [
                            'type' => 'string',
                            'description' => 'Directory path relative to project root (e.g. app/Models). Defaults to root.',
                        ],
                    ],
                    'required' => [],
                ],
            ],
            [
                'name' => 'search_code',
                'description' => 'Search for a pattern in project files using grep. Returns matching lines with file paths and line numbers.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'pattern' => [
                            'type' => 'string',
                            'description' => 'Search pattern (regex supported)',
                        ],
                        'path' => [
                            'type' => 'string',
                            'description' => 'Limit search to this directory (relative to project root). Optional.',
                        ],
                        'file_pattern' => [
                            'type' => 'string',
                            'description' => 'File glob pattern, e.g. "*.php", "*.blade.php". Optional.',
                        ],
                    ],
                    'required' => ['pattern'],
                ],
            ],
            [
                'name' => 'edit_file',
                'description' => 'Propose an edit to a file. The edit will be saved as pending and requires user approval. Use old_string/new_string for precise replacements.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'path' => [
                            'type' => 'string',
                            'description' => 'File path relative to project root',
                        ],
                        'old_string' => [
                            'type' => 'string',
                            'description' => 'The exact text to find and replace',
                        ],
                        'new_string' => [
                            'type' => 'string',
                            'description' => 'The replacement text',
                        ],
                        'description' => [
                            'type' => 'string',
                            'description' => 'Brief description of the change',
                        ],
                    ],
                    'required' => ['path', 'old_string', 'new_string', 'description'],
                ],
            ],
            [
                'name' => 'run_command',
                'description' => 'Run a whitelisted command. Allowed: php artisan (non-destructive), git status, git log, git diff, composer show.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'command' => [
                            'type' => 'string',
                            'description' => 'The command to run',
                        ],
                    ],
                    'required' => ['command'],
                ],
            ],
        ];
    }

    // ── Tool Execution ──

    private function executeTool(string $name, array $input): array
    {
        return match ($name) {
            'read_file' => $this->toolReadFile($input),
            'list_files' => $this->toolListFiles($input),
            'search_code' => $this->toolSearchCode($input),
            'edit_file' => $this->toolEditFile($input),
            'run_command' => $this->toolRunCommand($input),
            default => ['type' => 'text', 'content' => "Unknown tool: {$name}"],
        };
    }

    private function toolReadFile(array $input): array
    {
        $path = $input['path'] ?? '';

        if (!$this->validatePath($path)) {
            return ['type' => 'text', 'content' => "Access denied: {$path}"];
        }

        $fullPath = $this->projectRoot . '/' . $path;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return ['type' => 'text', 'content' => "File not found: {$path}"];
        }

        if (filesize($fullPath) > self::MAX_FILE_SIZE) {
            return ['type' => 'text', 'content' => "File too large (>" . (self::MAX_FILE_SIZE / 1024) . "KB). Use start_line/end_line to read a portion."];
        }

        $lines = file($fullPath);
        $startLine = max(1, $input['start_line'] ?? 1);
        $endLine = $input['end_line'] ?? count($lines);
        $endLine = min($endLine, count($lines));

        $output = '';
        for ($i = $startLine - 1; $i < $endLine; $i++) {
            $lineNum = $i + 1;
            $output .= sprintf("%4d | %s", $lineNum, $lines[$i]);
        }

        return ['type' => 'text', 'content' => $output];
    }

    private function toolListFiles(array $input): array
    {
        $path = $input['path'] ?? '';

        if ($path && !$this->validatePath($path)) {
            return ['type' => 'text', 'content' => "Access denied: {$path}"];
        }

        $fullPath = $this->projectRoot . ($path ? '/' . $path : '');

        if (!is_dir($fullPath)) {
            return ['type' => 'text', 'content' => "Directory not found: {$path}"];
        }

        $items = scandir($fullPath);
        $result = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $itemPath = $fullPath . '/' . $item;
            $type = is_dir($itemPath) ? 'dir' : 'file';
            $size = is_file($itemPath) ? $this->humanFileSize(filesize($itemPath)) : '';
            $result[] = ($type === 'dir' ? '📁 ' : '📄 ') . $item . ($size ? " ({$size})" : '');
        }

        return ['type' => 'text', 'content' => implode("\n", $result) ?: '(empty directory)'];
    }

    private function toolSearchCode(array $input): array
    {
        $pattern = $input['pattern'] ?? '';
        $path = $input['path'] ?? '';
        $filePattern = $input['file_pattern'] ?? '';

        if ($path && !$this->validatePath($path)) {
            return ['type' => 'text', 'content' => "Access denied: {$path}"];
        }

        $searchPath = $this->projectRoot . ($path ? '/' . $path : '');

        $cmd = ['grep', '-rn', '--max-count=50', '--include=*.php'];

        if ($filePattern) {
            $cmd = ['grep', '-rn', '--max-count=50', "--include={$filePattern}"];
        }

        // Exclude forbidden dirs
        $cmd[] = '--exclude-dir=vendor';
        $cmd[] = '--exclude-dir=node_modules';
        $cmd[] = '--exclude-dir=.git';
        $cmd[] = '--exclude-dir=storage';
        $cmd[] = '--exclude=.env';
        $cmd[] = '--exclude=.env.*';

        $cmd[] = $pattern;
        $cmd[] = $searchPath;

        $result = Process::timeout(10)->run($cmd);

        $output = $result->output();

        if (empty($output)) {
            return ['type' => 'text', 'content' => "No matches found for: {$pattern}"];
        }

        // Strip project root prefix for readability
        $output = str_replace($this->projectRoot . '/', '', $output);

        // Truncate if too long
        if (strlen($output) > 8000) {
            $output = substr($output, 0, 8000) . "\n... (truncated)";
        }

        return ['type' => 'text', 'content' => $output];
    }

    private function toolEditFile(array $input): array
    {
        $path = $input['path'] ?? '';
        $oldString = $input['old_string'] ?? '';
        $newString = $input['new_string'] ?? '';
        $description = $input['description'] ?? 'Edit';

        if (!$this->validatePath($path)) {
            return ['type' => 'text', 'content' => "Access denied: {$path}"];
        }

        $fullPath = $this->projectRoot . '/' . $path;

        if (!file_exists($fullPath)) {
            return ['type' => 'text', 'content' => "File not found: {$path}"];
        }

        $content = file_get_contents($fullPath);

        if (strpos($content, $oldString) === false) {
            return ['type' => 'text', 'content' => "old_string not found in {$path}. Make sure it matches exactly (including whitespace)."];
        }

        // Count occurrences
        $count = substr_count($content, $oldString);
        if ($count > 1) {
            return ['type' => 'text', 'content' => "old_string found {$count} times in {$path}. It must be unique. Add more surrounding context."];
        }

        // Save as pending edit
        $editId = Str::random(8);
        Cache::put("claude_edit:{$editId}", [
            'path' => $path,
            'full_path' => $fullPath,
            'old_string' => $oldString,
            'new_string' => $newString,
            'description' => $description,
        ], self::EDIT_TTL);

        // Generate diff preview
        $diff = $this->generateDiff($oldString, $newString);

        return [
            'type' => 'pending_edit',
            'edit_id' => $editId,
            'file' => $path,
            'description' => $description,
            'diff' => $diff,
        ];
    }

    private function toolRunCommand(array $input): array
    {
        $command = $input['command'] ?? '';

        // Validate against whitelist
        $allowed = false;
        foreach (self::ALLOWED_COMMANDS as $prefix) {
            if (str_starts_with($command, $prefix)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return ['type' => 'text', 'content' => "Command not allowed. Permitted: " . implode(', ', self::ALLOWED_COMMANDS)];
        }

        // Block destructive artisan commands
        $dangerous = ['migrate:fresh', 'migrate:reset', 'db:wipe', 'db:seed', 'key:generate'];
        foreach ($dangerous as $cmd) {
            if (str_contains($command, $cmd)) {
                return ['type' => 'text', 'content' => "Destructive command blocked: {$cmd}"];
            }
        }

        $result = Process::timeout(30)->path($this->projectRoot)->run($command);

        $output = $result->output() . $result->errorOutput();

        if (empty($output)) {
            $output = $result->successful() ? '(no output, exit code 0)' : "(exit code {$result->exitCode()})";
        }

        // Truncate
        if (strlen($output) > 8000) {
            $output = substr($output, 0, 8000) . "\n... (truncated)";
        }

        return ['type' => 'text', 'content' => $output];
    }

    // ── Apply / Cancel Edits ──

    public function applyEdit(string $editId): array
    {
        $edit = Cache::get("claude_edit:{$editId}");

        if (!$edit) {
            return ['success' => false, 'message' => 'Edit expired or not found.'];
        }

        $fullPath = $edit['full_path'];
        $path = $edit['path'];

        if (!file_exists($fullPath)) {
            return ['success' => false, 'message' => "File not found: {$path}"];
        }

        $content = file_get_contents($fullPath);

        if (strpos($content, $edit['old_string']) === false) {
            return ['success' => false, 'message' => "File has changed since edit was proposed. old_string no longer matches."];
        }

        // Apply edit
        $newContent = str_replace($edit['old_string'], $edit['new_string'], $content);
        file_put_contents($fullPath, $newContent);

        // Git commit
        $commitMessage = "claude: {$edit['description']}";
        Process::path($this->projectRoot)->run("git add " . escapeshellarg($path));
        Process::path($this->projectRoot)->run(['git', 'commit', '-m', $commitMessage]);

        Cache::forget("claude_edit:{$editId}");

        return ['success' => true, 'message' => "Applied: {$edit['description']}\nFile: {$path}"];
    }

    public function cancelEdit(string $editId): bool
    {
        return Cache::forget("claude_edit:{$editId}");
    }

    // ── Helpers ──

    private function validatePath(string $path): bool
    {
        // Block path traversal
        if (str_contains($path, '..')) {
            return false;
        }

        // Block forbidden paths
        foreach (self::FORBIDDEN_PATHS as $forbidden) {
            if (str_starts_with($path, $forbidden) || str_starts_with($path, '/' . $forbidden)) {
                return false;
            }
        }

        // Also block if path equals just ".env"
        if ($path === '.env' || str_ends_with($path, '/.env')) {
            return false;
        }

        return true;
    }

    private function generateDiff(string $old, string $new): string
    {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);

        $diff = '';
        foreach ($oldLines as $line) {
            $diff .= "- {$line}\n";
        }
        foreach ($newLines as $line) {
            $diff .= "+ {$line}\n";
        }

        return $diff;
    }

    private function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
