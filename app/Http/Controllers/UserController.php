<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
use App\Models\Person;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Rules\SecurePassword;
use App\Notifications\AccessGranted;

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

        // If person_id provided, name/email are optional (will be taken from Person)
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'church_role_id' => ['required', Rule::exists('church_roles', 'id')->where('church_id', $church->id)],
            'person_id' => 'nullable|exists:people,id',
        ]);

        // Get name and email from Person if person_id provided
        $name = $validated['name'] ?? null;
        $email = $validated['email'] ?? null;

        if (!empty($validated['person_id'])) {
            $person = Person::where('id', $validated['person_id'])
                ->where('church_id', $church->id)
                ->firstOrFail();

            $name = $person->full_name;
            $email = $person->email;

            if (empty($email)) {
                return back()->withInput()->withErrors(['person_id' => 'Ця людина не має email. Додайте email в профілі або створіть користувача без прив\'язки.']);
            }

            // Check if email is already taken by another active user
            if (User::where('email', $email)->exists()) {
                return back()->withInput()->withErrors(['person_id' => 'Користувач з таким email вже існує.']);
            }
        } else {
            // No person selected - require name and email
            if (empty($name) || empty($email)) {
                return back()->withInput()->withErrors(['name' => 'Вкажіть ім\'я та email, або оберіть людину.']);
            }
        }

        // Generate secure random password (user will reset via email)
        $password = Str::random(32);

        // Restore soft-deleted user or create new
        $trashedUser = User::onlyTrashed()->where('email', $email)->first();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update([
                'church_id' => $church->id,
                'name' => $name,
                'password' => Hash::make($password),
                'church_role_id' => $validated['church_role_id'],
            ]);
            $user = $trashedUser;

            Log::channel('security')->info('Soft-deleted user restored via admin user creation', [
                'user_id' => $user->id,
                'email' => $email,
                'church_id' => $church->id,
            ]);
        } else {
            $user = User::create([
                'church_id' => $church->id,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'church_role_id' => $validated['church_role_id'],
            ]);
        }

        // Link to person if provided
        if (!empty($validated['person_id'])) {
            $person->update(['user_id' => $user->id]);
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

        // If person_id provided, name/email are optional (will be taken from Person)
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'church_role_id' => ['nullable', Rule::exists('church_roles', 'id')->where('church_id', $church->id)],
            'person_id' => 'nullable|exists:people,id',
            'password' => ['nullable', 'string', 'min:10', new SecurePassword],
        ]);

        // Get name and email from Person if person_id provided
        $name = $validated['name'] ?? null;
        $email = $validated['email'] ?? null;
        $churchRoleId = $validated['church_role_id'] ?: null; // Convert empty string to null
        $hadNoRole = $user->church_role_id === null; // Track if user had no role before

        if (!empty($validated['person_id'])) {
            $person = Person::where('id', $validated['person_id'])
                ->where('church_id', $church->id)
                ->firstOrFail();

            $name = $person->full_name;
            $email = $person->email;

            if (empty($email)) {
                return back()->withInput()->withErrors(['person_id' => 'Ця людина не має email. Додайте email в профілі або відв\'яжіть користувача.']);
            }

            // Check if email is already taken by another user
            if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
                return back()->withInput()->withErrors(['person_id' => 'Користувач з таким email вже існує.']);
            }
        } else {
            // No person selected - require name and email
            if (empty($name) || empty($email)) {
                return back()->withInput()->withErrors(['name' => 'Вкажіть ім\'я та email, або оберіть людину.']);
            }
        }

        $user->update([
            'name' => $name,
            'email' => $email,
            'church_role_id' => $churchRoleId,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update person link
        // Remove old link
        Person::where('user_id', $user->id)->update(['user_id' => null]);

        // Add new link
        if (!empty($validated['person_id'])) {
            $person->update(['user_id' => $user->id]);
        }

        // Send notification if user was granted access (had no role before, now has one)
        if ($hadNoRole && $churchRoleId !== null) {
            $role = ChurchRole::find($churchRoleId);
            $user->notify(new AccessGranted($role->name, $church->name));
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
