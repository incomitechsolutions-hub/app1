<?php

namespace App\Console\Commands;

use Database\Seeders\DefaultAdminSeeding;
use Illuminate\Console\Command;

class SeedDefaultAdminCommand extends Command
{
    protected $signature = 'admin:seed-default-user
                            {--force : Erforderlich wenn APP_ENV=production}';

    protected $description = 'Standard-Admin anlegen oder aktualisieren (idempotent, ohne übrige Seeders)';

    public function handle(): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            $this->error('In Produktion bitte --force verwenden, z. B.: php artisan admin:seed-default-user --force');

            return self::FAILURE;
        }

        DefaultAdminSeeding::seed();

        $this->info('Admin '.DefaultAdminSeeding::DEFAULT_EMAIL.' wurde angelegt oder aktualisiert.');

        return self::SUCCESS;
    }
}
