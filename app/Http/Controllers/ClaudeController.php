<?php

namespace App\Http\Controllers;

use App\Services\ClaudeCodeService;
use Illuminate\Http\Request;

class ClaudeController extends Controller
{
    public function index()
    {
        // Start a session for this user if not active
        $claude = ClaudeCodeService::make();
        $userId = (string) auth()->id();

        if (!$claude->isSessionActive($userId)) {
            $claude->startSession($userId);
        }

        return view('system-admin.claude');
    }

    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:10000']);

        $userId = (string) auth()->id();
        $claude = ClaudeCodeService::make();

        if (!$claude->isSessionActive($userId)) {
            $claude->startSession($userId);
        }

        $actions = $claude->chat($userId, $request->message);

        return response()->json(['actions' => $actions]);
    }

    public function applyEdit(string $editId)
    {
        $result = ClaudeCodeService::make()->applyEdit($editId);

        return response()->json($result);
    }

    public function applyClearEdit(string $editId)
    {
        $claude = ClaudeCodeService::make();
        $result = $claude->applyEdit($editId);

        if ($result['success']) {
            \Illuminate\Support\Facades\Process::path('/var/www/html')->timeout(30)->run('php artisan optimize:clear');
            $result['message'] .= "\n\nCache cleared (optimize:clear)";
        }

        return response()->json($result);
    }

    public function cancelEdit(string $editId)
    {
        ClaudeCodeService::make()->cancelEdit($editId);

        return response()->json(['success' => true, 'message' => 'Edit cancelled.']);
    }

    public function clearSession()
    {
        $userId = (string) auth()->id();
        ClaudeCodeService::make()->endSession($userId);

        return response()->json(['success' => true]);
    }
}
