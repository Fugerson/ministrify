<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('church_id', $this->getCurrentChurch()->id)
            ->with('person')
            ->orderBy('name')
            ->get();

        return view('settings.users.index', compact('users'));
    }

    public function create()
    {
        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)
            ->whereDoesntHave('user')
            ->orderBy('last_name')
            ->get();

        return view('settings.users.create', compact('people'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'leader', 'volunteer'])],
            'person_id' => 'nullable|exists:people,id',
            'send_invite' => 'boolean',
        ]);

        $church = $this->getCurrentChurch();

        // Generate random password
        $password = Str::random(12);

        $user = User::create([
            'church_id' => $church->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => $validated['role'],
        ]);

        // Link to person if provided
        if (!empty($validated['person_id'])) {
            $person = Person::findOrFail($validated['person_id']);
            if ($person->church_id === $church->id) {
                $person->update(['user_id' => $user->id]);
            }
        }

        // TODO: Send invite email with password

        return redirect()->route('settings.users.index')
            ->with('success', "Користувача створено. Тимчасовий пароль: {$password}");
    }

    public function edit(User $user)
    {
        $this->authorizeChurch($user);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('user')
                  ->orWhere('user_id', $user->id);
            })
            ->orderBy('last_name')
            ->get();

        return view('settings.users.edit', compact('user', 'people'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeChurch($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'leader', 'volunteer'])],
            'person_id' => 'nullable|exists:people,id',
            'password' => 'nullable|string|min:8',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update person link
        $church = $this->getCurrentChurch();

        // Remove old link
        Person::where('user_id', $user->id)->update(['user_id' => null]);

        // Add new link
        if (!empty($validated['person_id'])) {
            $person = Person::findOrFail($validated['person_id']);
            if ($person->church_id === $church->id) {
                $person->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('settings.users.index')
            ->with('success', 'Користувача оновлено.');
    }

    public function destroy(User $user)
    {
        $this->authorizeChurch($user);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Ви не можете видалити свій акаунт.');
        }

        // Remove person link
        Person::where('user_id', $user->id)->update(['user_id' => null]);

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'Користувача видалено.');
    }

    public function sendInvite(User $user)
    {
        $this->authorizeChurch($user);

        // TODO: Send password reset email as invite

        return back()->with('success', 'Запрошення надіслано.');
    }

    private function authorizeChurch(User $user): void
    {
        if ($user->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
