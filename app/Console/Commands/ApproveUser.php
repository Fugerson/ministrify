<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApproveUser extends Command
{
    protected $signature = 'app:approve-user {email : User email}';

    protected $description = 'Approve a pending user role request';

    public function handle()
    {
        $email = $this->argument('email');
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: $email");
            return;
        }

        $this->info("Found: {$user->name} ({$user->email})");
        $this->info("Status: " . ($user->servant_approval_status ?? 'no status'));
        $this->info("Requested role: " . ($user->requestedChurchRole?->name ?? 'none'));

        if (!$user->requested_church_role_id) {
            $this->error("No requested role to approve");
            return;
        }

        // Approve
        $user->update([
            'church_role_id' => $user->requested_church_role_id,
            'servant_approval_status' => 'approved',
            'servant_approved_at' => now(),
        ]);

        // Update pivot
        \DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $user->church_id)
            ->update([
                'church_role_id' => $user->requested_church_role_id,
                'role_approval_status' => 'approved',
                'updated_at' => now(),
            ]);

        $this->info("✅ Approved! Role: " . $user->requestedChurchRole->name);
    }
}
