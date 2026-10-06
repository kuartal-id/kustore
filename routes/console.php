<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

/*
 * Grant or revoke the admin flag. Accepts a user id, a Kuartal ID "sub", or an
 * email that matches exactly one user (emails are not unique across Kuartal ID
 * and local accounts).
 *   php artisan kustore:admin 1
 *   php artisan kustore:admin you@example.com --revoke
 */
Artisan::command('kustore:admin {user} {--revoke}', function (string $user) {
    $matches = User::query()
        ->where(fn ($q) => $q->where('kuartal_id_sub', $user)->orWhere('email', strtolower($user))
            ->when(ctype_digit($user), fn ($q) => $q->orWhere('id', (int) $user)))
        ->get();

    if ($matches->count() !== 1) {
        $this->error($matches->isEmpty() ? 'No matching user.' : 'More than one user matches; use the user id.');
        $matches->each(fn ($u) => $this->line("  #{$u->id} {$u->name} <{$u->email}> ".($u->kuartal_id_sub ? 'Kuartal ID' : 'email')));

        return 1;
    }

    $target = $matches->first();
    $target->forceFill(['is_admin' => ! $this->option('revoke')])->save();
    $this->info(($this->option('revoke') ? 'Revoked admin from ' : 'Granted admin to ')."#{$target->id} {$target->name}.");

    return 0;
})->purpose('Grant or revoke Kustore admin access');
