<?php

namespace App\Http\Controllers\Traits;

trait RequiresChurch
{
    protected function getChurchOrFail()
    {
        $church = auth()->user()->church;

        if (!$church) {
            abort(redirect()->route('dashboard')
                ->with('error', 'Ця функція доступна тільки для користувачів з церквою.'));
        }

        return $church;
    }
}
