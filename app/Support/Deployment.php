<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Hostinger's Git deploy only pulls files; it can't run `php artisan migrate`. So the first request after a
 * deploy that changed migrations, routes or config runs, once and under a file lock:
 *   - `migrate --force` (new migrations only; existing ones are never edited),
 *   - `optimize:clear` if Laravel's caches exist, so stale cached routes/config can't hide new pages.
 * No SSH needed. Only in production; tests and local dev are untouched.
 *
 * The release fingerprint is built from file names and modification times (stat calls only, no reads):
 * Hostinger rewrites a file only when its content changes, so the fingerprint changes exactly when a deploy
 * brings new migrations, routes or config.
 */
final class Deployment
{
    public static function enabled(): bool
    {
        return app()->environment('production');
    }

    public static function fingerprint(): string
    {
        $parts = [];
        foreach (glob(database_path('migrations/*.php')) ?: [] as $f) {
            $parts[] = basename($f);
        }
        foreach (array_merge([base_path('routes/web.php')], glob(config_path('*.php')) ?: []) as $f) {
            $parts[] = basename($f).':'.@filemtime($f);
        }

        return sha1(implode('|', $parts));
    }

    public static function markerFile(): string
    {
        return storage_path('app/private/deployed-release');
    }

    public static function isPending(): bool
    {
        if (! self::enabled()) {
            return false;
        }
        $done = is_file(self::markerFile()) ? trim((string) @file_get_contents(self::markerFile())) : '';

        return $done !== self::fingerprint();
    }

    /** True when the app is current; false if the update failed (retried after a minute). */
    public static function run(): bool
    {
        $dir = dirname(self::markerFile());
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $failFile = $dir.'/deploy-failed-at';
        if (is_file($failFile) && (time() - (int) @file_get_contents($failFile)) < 60) {
            return false;
        }
        $lock = @fopen($dir.'/deploy.lock', 'c');
        if ($lock === false) {
            return false;
        }

        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                return true; // another request is doing it
            }
            if (! self::isPending()) {
                return true;
            }
            $fingerprint = self::fingerprint();
            Artisan::call('migrate', ['--force' => true]);
            $cached = array_filter(['config.php', 'routes-v7.php', 'events.php'], fn ($f) => is_file(base_path('bootstrap/cache/'.$f)));
            if ($cached) {
                Artisan::call('optimize:clear');
            }
            file_put_contents(self::markerFile(), $fingerprint);
            @unlink($failFile);
            Log::info('kustore.post_deploy_done', ['fingerprint' => $fingerprint]);

            return true;
        } catch (\Throwable $e) {
            @file_put_contents($failFile, (string) time());
            Log::error('kustore.post_deploy_failed', ['error' => $e->getMessage()]);

            return false;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
