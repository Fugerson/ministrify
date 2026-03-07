<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'required|in:android,ios',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Upsert: update if token exists, create if not
        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'platform' => $request->platform,
                'device_name' => $request->device_name,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:500',
        ]);

        DeviceToken::where('token', $request->token)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
