<?php

namespace Tests;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createChurchWithAdmin(): array
    {
        $church = Church::factory()->create();
        $admin = User::factory()->admin()->create([
            'church_id' => $church->id,
        ]);
        $admin->refresh();

        return [$church, $admin];
    }

    protected function createUserWithRole(Church $church, string $roleType = 'volunteer'): User
    {
        $user = User::factory()->{$roleType}()->create([
            'church_id' => $church->id,
        ]);
        $user->refresh();

        return $user;
    }
}
