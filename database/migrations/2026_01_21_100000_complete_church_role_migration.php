<?php

use App\Models\ChurchRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assign church_role_id to users who don't have it yet
        $usersWithoutRole = User::whereNull('church_role_id')
            ->whereNotNull('church_id')
            ->get();

        foreach ($usersWithoutRole as $user) {
            // Find an admin role for this church
            $adminRole = ChurchRole::where('church_id', $user->church_id)
                ->where('is_admin_role', true)
                ->first();

            if ($adminRole) {
                $user->update(['church_role_id' => $adminRole->id]);
            }
        }

        // Remove the legacy 'role' column (keep for now, just make nullable)
        // We'll remove it completely in a future migration after verifying everything works
    }

    public function down(): void
    {
        // Nothing to reverse - this is a data migration
    }
};
