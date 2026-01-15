<?php

use App\Models\Church;
use App\Models\ChurchRole;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Add admin role to all existing churches that don't have one
        $churches = Church::all();

        foreach ($churches as $church) {
            // Check if admin role already exists
            $hasAdminRole = ChurchRole::where('church_id', $church->id)
                ->where('is_admin_role', true)
                ->exists();

            if (!$hasAdminRole) {
                ChurchRole::create([
                    'church_id' => $church->id,
                    'name' => 'Адміністратор церкви',
                    'slug' => 'admin',
                    'color' => '#dc2626',
                    'sort_order' => 0,
                    'is_admin_role' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Remove admin roles created by this migration
        ChurchRole::where('slug', 'admin')
            ->where('is_admin_role', true)
            ->delete();
    }
};
