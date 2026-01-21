<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
use App\Models\Person;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Rules\SecurePassword;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('church_id', $this->getCurrentChurch()->id)
            ->with(['person', 'churchRole'])
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

        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->get();

        return view('settings.users.create', compact('people', 'churchRoles'));
    }

    public function store(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'church_role_id' => ['required', Rule::exists('church_roles', 'id')->where('church_id', $church->id)],
            'person_id' => 'nullable|exists:people,id',
        ]);

        // Generate secure random password (user will reset via email)
        $password = Str::random(32);

        $user = User::create([
            'church_id' => $church->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'church_role_id' => $validated['church_role_id'],
        ]);

        // Link to person if provided
        if (!empty($validated['person_id'])) {
            $person = Person::findOrFail($validated['person_id']);
            if ($person->church_id === $church->id) {
                $person->update(['user_id' => $user->id]);
            }
        }

        // Try to send password reset link
        $message = 'Користувача створено.';
        try {
            $status = Password::sendResetLink(['email' => $user->email]);
            if ($status === Password::RESET_LINK_SENT) {
                $message .= ' Посилання для входу надіслано на email.';
            }
        } catch (\Exception $e) {
            $message .= ' Налаштуйте пароль вручну або надішліть запрошення пізніше.';
        }

        return redirect()->route('settings.users.index')
            ->with('success', $message);
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

        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->get();

        return view('settings.users.edit', compact('user', 'people', 'churchRoles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeChurch($user);

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'church_role_id' => ['required', Rule::exists('church_roles', 'id')->where('church_id', $church->id)],
            'person_id' => 'nullable|exists:people,id',
            'password' => ['nullable', 'string', 'min:10', new SecurePassword],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'church_role_id' => $validated['church_role_id'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update person link
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

        return back()->with('success', 'Користувача видалено.');
    }

    public function sendInvite(User $user)
    {
        $this->authorizeChurch($user);

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
            if ($status !== Password::RESET_LINK_SENT) {
                return back()->with('error', 'Не вдалося надіслати запрошення.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Помилка надсилання: ' . $e->getMessage());
        }

        return back()->with('success', 'Запрошення надіслано на ' . $user->email);
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
