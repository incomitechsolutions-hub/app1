<?php

namespace Database\Seeders;

use App\Models\User;

/**
 * Single place for default admin credentials (used by DatabaseSeeder and artisan admin:seed-default-user).
 */
final class DefaultAdminSeeding
{
    public const DEFAULT_EMAIL = 'admin@app1.hostn.de';

    public const DEFAULT_NAME = 'Admin';

    public const DEFAULT_PASSWORD = 'App1-Hostn-Adm!2025-Secure';

    public static function seed(): void
    {
        $plain = env('ADMIN_SEED_PASSWORD', self::DEFAULT_PASSWORD);

        User::updateOrCreate(
            ['email' => self::DEFAULT_EMAIL],
            [
                'name' => self::DEFAULT_NAME,
                'password' => $plain,
            ]
        );
    }
}
