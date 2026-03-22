<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Default admin login for initial / server setup.
 *
 * Password: set ADMIN_SEED_PASSWORD in .env for production, or use the built-in default below
 * (change it immediately after first login). Re-running this seeder resets the password to the
 * effective plain value (env or default).
 */
class AdminUserSeeder extends Seeder
{
    private const DEFAULT_EMAIL = 'admin@app1.hostn.de';

    private const DEFAULT_NAME = 'Admin';

    /**
     * Strong default for first deploy; override with ADMIN_SEED_PASSWORD in .env.
     */
    private const DEFAULT_PASSWORD = 'App1-Hostn-Adm!2025-Secure';

    public function run(): void
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
