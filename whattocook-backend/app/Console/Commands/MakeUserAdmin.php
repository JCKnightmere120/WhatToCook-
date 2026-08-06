<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin {email : Email address of an existing WhatToCook account}';

    protected $description = 'Grant an existing user access to the WhatToCook admin recipe manager';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user was found for {$email}. Register that account first.");

            return self::FAILURE;
        }

        // Keep role assignment out of normal mass assignment. This command is
        // the explicit, local-only way to promote an existing account.
        $user->forceFill(['is_admin' => true])->save();
        $this->info("{$user->email} can now use /admin/recipes.");

        return self::SUCCESS;
    }
}
