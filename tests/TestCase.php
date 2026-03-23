<?php

namespace Tests;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
    }

    protected function createChurchWithAdmin(): array
    {
        $church = Church::factory()->create();
        $admin = User::factory()->admin()->create([
            'church_id' => $church->id,
        ]);
        // Ensure church_user pivot exists for multi-church belongsToChurch() check
        if (!\Illuminate\Support\Facades\DB::table('church_user')->where('user_id', $admin->id)->where('church_id', $church->id)->exists()) {
            $admin->churches()->attach($church->id, [
                'church_role_id' => $admin->church_role_id,
            ]);
        }
        $admin->refresh();

        return [$church, $admin];
    }

    protected function createUserWithRole(Church $church, string $roleType = 'volunteer'): User
    {
        $user = User::factory()->{$roleType}()->create([
            'church_id' => $church->id,
        ]);
        // Ensure church_user pivot exists
        if (!\Illuminate\Support\Facades\DB::table('church_user')->where('user_id', $user->id)->where('church_id', $church->id)->exists()) {
            $user->churches()->attach($church->id, [
                'church_role_id' => $user->church_role_id,
            ]);
        }
        $user->refresh();

        return $user;
    }
}
