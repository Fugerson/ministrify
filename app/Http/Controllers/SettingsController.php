<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $expenseCategories = $church->expenseCategories;
        $tags = $church->tags;
        $users = $church->users()->with('person')->get();

        return view('settings.index', compact('church', 'expenseCategories', 'tags', 'users'));
    }

    public function updateChurch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048',
        ]);

        $church = $this->getCurrentChurch();

        if ($request->hasFile('logo')) {
            if ($church->logo) {
                Storage::disk('public')->delete($church->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $church->update($validated);

        return back()->with('success', 'Налаштування церкви оновлено.');
    }

    public function updateTelegram(Request $request)
    {
        $validated = $request->validate([
            'telegram_bot_token' => 'nullable|string|max:100',
        ]);

        $church = $this->getCurrentChurch();
        $church->update($validated);

        return back()->with('success', 'Налаштування Telegram оновлено.');
    }

    public function testTelegram()
    {
        $church = $this->getCurrentChurch();

        if (!$church->telegram_bot_token) {
            return back()->with('error', 'Токен бота не налаштовано.');
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);
            $botInfo = $telegram->getMe();

            return back()->with('success', "Бот підключено: @{$botInfo['username']}");
        } catch (\Exception $e) {
            return back()->with('error', 'Помилка підключення: ' . $e->getMessage());
        }
    }

    public function updateNotifications(Request $request)
    {
        $church = $this->getCurrentChurch();

        $settings = $church->settings ?? [];
        $settings['notifications'] = [
            'reminder_day_before' => $request->boolean('reminder_day_before'),
            'reminder_same_day' => $request->boolean('reminder_same_day'),
            'notify_leader_on_decline' => $request->boolean('notify_leader_on_decline'),
        ];

        $church->update(['settings' => $settings]);

        return back()->with('success', 'Налаштування сповіщень оновлено.');
    }
}
