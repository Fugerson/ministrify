#!/usr/bin/env php
<?php

/**
 * i18n transformation script for Blade files.
 * Wraps Ukrainian UI strings in __() translation helper.
 */

$basePath = __DIR__ . '/resources/views';

$files = [
    // Ministries
    'ministries/index.blade.php',
    'ministries/create.blade.php',
    'ministries/edit.blade.php',
    'ministries/show.blade.php',
    'ministries/resources.blade.php',
    'ministries/worship-events.blade.php',
    'ministries/worship-stats.blade.php',
    'ministries/goals/index.blade.php',
    // Finances
    'finances/index.blade.php',
    'finances/journal.blade.php',
    'finances/partials/tabs.blade.php',
    'finances/partials/filters.blade.php',
    'finances/partials/content/analytics.blade.php',
    'finances/incomes/index.blade.php',
    'finances/incomes/create.blade.php',
    'finances/incomes/edit.blade.php',
    'finances/expenses/index.blade.php',
    'finances/expenses/create.blade.php',
    'finances/expenses/edit.blade.php',
    'finances/budgets/index.blade.php',
    'finances/monobank/index.blade.php',
    'finances/privatbank/index.blade.php',
    'finances/income-categories/index.blade.php',
    'finances/cards/index.blade.php',
    'finances/cards/monobank-content.blade.php',
    'finances/cards/privatbank-content.blade.php',
    'finances/exchange/create.blade.php',
    // Settings
    'settings/index.blade.php',
    'settings/audit-logs.blade.php',
    'settings/users/index.blade.php',
    'settings/users/create.blade.php',
    'settings/users/edit.blade.php',
    'settings/church-roles/index.blade.php',
    'settings/shepherds/index.blade.php',
];

// Ukrainian text patterns - characters that indicate Ukrainian text
// We need to match sequences containing Cyrillic characters
$cyrillicPattern = '/[\x{0400}-\x{04FF}]/u';

function hasCyrillic($str) {
    return preg_match('/[\x{0400}-\x{04FF}]/u', $str);
}

function isAlreadyWrapped($content, $pos, $text) {
    // Check if the text is already wrapped in __()
    $before = substr($content, max(0, $pos - 10), 10);
    if (preg_match("/__\(\s*['\"]$/", $before)) {
        return true;
    }
    return false;
}

$totalChanges = 0;

foreach ($files as $relPath) {
    $filePath = $basePath . '/' . $relPath;
    if (!file_exists($filePath)) {
        echo "SKIP: $relPath (not found)\n";
        continue;
    }

    $content = file_get_contents($filePath);
    $original = $content;
    $changes = 0;

    // 1. @section('title', 'Ukrainian Text') -> @section('title', __('Ukrainian Text'))
    $content = preg_replace_callback(
        "/@section\('title',\s*'([^']*[\x{0400}-\x{04FF}][^']*)'\)/u",
        function($m) use (&$changes) {
            $changes++;
            return "@section('title', __('".addcslashes($m[1], "'")."'))";
        },
        $content
    );

    // Also handle double-quoted title sections
    $content = preg_replace_callback(
        '/@section\("title",\s*"([^"]*[\x{0400}-\x{04FF}][^"]*)"\)/u',
        function($m) use (&$changes) {
            $changes++;
            return "@section('title', __('".str_replace("'", "\\'", $m[1])."'))";
        },
        $content
    );

    // 2. placeholder="Ukrainian Text" -> placeholder="{{ __('Ukrainian Text') }}"
    // But NOT if already wrapped
    $content = preg_replace_callback(
        '/placeholder="([^"]*[\x{0400}-\x{04FF}][^"]*)"/u',
        function($m) use (&$changes) {
            // Skip if already wrapped
            if (strpos($m[1], "__(") !== false || strpos($m[1], "{{") !== false) {
                return $m[0];
            }
            $changes++;
            return 'placeholder="{{ __(\'' . str_replace("'", "\\'", $m[1]) . '\') }}"';
        },
        $content
    );

    // 3. title="Ukrainian Text" -> title="{{ __('Ukrainian Text') }}"
    $content = preg_replace_callback(
        '/title="([^"]*[\x{0400}-\x{04FF}][^"]*)"/u',
        function($m) use (&$changes) {
            if (strpos($m[1], "__(") !== false || strpos($m[1], "{{") !== false) {
                return $m[0];
            }
            $changes++;
            return 'title="{{ __(\'' . str_replace("'", "\\'", $m[1]) . '\') }}"';
        },
        $content
    );

    // 4. Static Ukrainian text inside HTML tags (not in attributes, not in {{ }}, not in PHP)
    // This handles lines like:  <span>Ukrainian Text</span> or plain text nodes
    // We need to be very careful here - process line by line

    $lines = explode("\n", $content);
    $processedLines = [];

    foreach ($lines as $line) {
        // Skip lines that are pure PHP, comments, or Blade directives
        if (preg_match('/^\s*(@php|@endphp|@csrf|@method|@include|@extends|@push|@endpush|@if|@else|@endif|@foreach|@endforeach|@forelse|@empty|@endforelse|@can|@endcan|@error|@enderror|@for|@endfor|@while|@endwhile|@switch|@case|@break|@default|@endswitch|@section|@endsection|@yield|@stack|@prepend|@slot|@endslot|@component|@endcomponent|@php|<script|<\/script|\/\/|\/\*|\*\/|\*|{!!)/', $line)) {
            $processedLines[] = $line;
            continue;
        }

        // Skip lines inside <script> blocks (handled separately)
        // Skip lines that are only whitespace
        if (trim($line) === '') {
            $processedLines[] = $line;
            continue;
        }

        // Process Ukrainian text between HTML tags: >Ukrainian text<
        $line = preg_replace_callback(
            '/>(\s*)([\x{0400}-\x{04FF}][\x{0400}-\x{04FF}\s\p{P}0-9a-zA-Z()«»:*!?.,;\/\-+=%]+?)(\s*)</u',
            function($m) use (&$changes) {
                $text = $m[2];
                // Skip if already wrapped
                if (strpos($text, '{{') !== false || strpos($text, '__(') !== false) {
                    return $m[0];
                }
                // Skip if it's a Blade variable
                if (preg_match('/^\s*\$/', $text)) {
                    return $m[0];
                }
                // Skip pure numbers or symbols
                if (!preg_match('/[\x{0400}-\x{04FF}]/u', $text)) {
                    return $m[0];
                }
                $trimmed = trim($text);
                if (empty($trimmed)) {
                    return $m[0];
                }
                $changes++;
                return '>' . $m[1] . "{{ __('".str_replace("'", "\\'", $trimmed)."') }}" . $m[3] . '<';
            },
            $line
        );

        $processedLines[] = $line;
    }

    $content = implode("\n", $processedLines);

    // 5. Handle 'confirm(...)' JS calls with Ukrainian text
    $content = preg_replace_callback(
        "/confirm\('([^']*[\x{0400}-\x{04FF}][^']*)'\)/u",
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return "confirm('{{ __(\\'".str_replace("'", "\\\\'", $m[1])."\\') }}')";
        },
        $content
    );

    // Handle confirm("...") with double quotes
    $content = preg_replace_callback(
        '/confirm\("([^"]*[\x{0400}-\x{04FF}][^"]*)"\)/u',
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return "confirm('{{ __(\"".str_replace('"', '\\"', $m[1])."\") }}')";
        },
        $content
    );

    // 6. Handle alert() calls with Ukrainian text
    $content = preg_replace_callback(
        "/alert\('([^']*[\x{0400}-\x{04FF}][^']*)'\)/u",
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return "alert('{{ __(\\'".str_replace("'", "\\\\'", $m[1])."\\') }}')";
        },
        $content
    );

    // 7. Handle showToast calls with Ukrainian text
    $content = preg_replace_callback(
        "/showToast\('([^']*)',\s*'([^']*[\x{0400}-\x{04FF}][^']*)'\)/u",
        function($m) use (&$changes) {
            if (strpos($m[2], '__(') !== false) return $m[0];
            $changes++;
            return "showToast('".$m[1]."', '{{ __(\\'".$m[2]."\\') }}')";
        },
        $content
    );

    // 8. Handle PHP array values with Ukrainian text like 'label' => 'Ukrainian Text'
    $content = preg_replace_callback(
        "/'label'\s*=>\s*'([^']*[\x{0400}-\x{04FF}][^']*)'/u",
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return "'label' => __('".str_replace("'", "\\'", $m[1])."')";
        },
        $content
    );

    // 9. Handle match() expressions with Ukrainian values: 'key' => 'Ukrainian',
    $content = preg_replace_callback(
        "/=>\s*'([^']*[\x{0400}-\x{04FF}][^']*)',$/mu",
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            // Skip if this is inside a match() or PHP array context and the key is a PHP variable
            $changes++;
            return "=> __('".str_replace("'", "\\'", $m[1])."'),";
        },
        $content
    );

    // 10. Handle x-text with Ukrainian strings: x-text="condition ? 'Ukrainian' : 'Ukrainian'"
    $content = preg_replace_callback(
        "/x-text=\"([^\"]*?)'/u",
        function($m) use (&$changes) {
            // This is tricky - just leave for manual review
            return $m[0];
        },
        $content
    );

    // 11. Handle null-text="Ukrainian" and similar component attributes
    $content = preg_replace_callback(
        '/null-text="([^"]*[\x{0400}-\x{04FF}][^"]*)"/u',
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return 'null-text="{{ __(\'' . str_replace("'", "\\'", $m[1]) . '\') }}"';
        },
        $content
    );

    // 12. Handle nullText="Ukrainian"
    $content = preg_replace_callback(
        '/nullText="([^"]*[\x{0400}-\x{04FF}][^"]*)"/u',
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return 'nullText="{{ __(\'' . str_replace("'", "\\'", $m[1]) . '\') }}"';
        },
        $content
    );

    // 13. Handle button-text="Ukrainian"
    $content = preg_replace_callback(
        '/button-text="([^"]*[\x{0400}-\x{04FF}][^"]*)"/u',
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return 'button-text="{{ __(\'' . str_replace("'", "\\'", $m[1]) . '\') }}"';
        },
        $content
    );

    // 14. Handle message="Ukrainian" (component attribute)
    $content = preg_replace_callback(
        '/\bmessage="([^"]*[\x{0400}-\x{04FF}][^"]*)"/u',
        function($m) use (&$changes) {
            if (strpos($m[1], '__(') !== false) return $m[0];
            $changes++;
            return 'message="{{ __(\'' . str_replace("'", "\\'", $m[1]) . '\') }}"';
        },
        $content
    );

    if ($content !== $original) {
        file_put_contents($filePath, $content);
        $totalChanges += $changes;
        echo "OK: $relPath ($changes changes)\n";
    } else {
        echo "UNCHANGED: $relPath\n";
    }
}

echo "\nTotal changes: $totalChanges\n";
echo "Done! Please review the changes manually.\n";
