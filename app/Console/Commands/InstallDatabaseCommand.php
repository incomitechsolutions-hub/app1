<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallDatabaseCommand extends Command
{
    protected $signature = 'app:install-database
                            {--fresh : Alle Tabellen droppen und Migrationen neu ausführen}
                            {--no-seed : DatabaseSeeder nicht ausführen}
                            {--force : Erforderlich wenn APP_ENV=production}';

    protected $description = 'Datenbank prüfen (optional SQLite-Datei anlegen), Migrationen ausführen und optional seeden';

    public function handle(): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            $this->error('In Produktion bitte --force verwenden, z. B.: php artisan app:install-database --force');

            return self::FAILURE;
        }

        $connectionName = (string) config('database.default');
        $driver = (string) config("database.connections.{$connectionName}.driver");

        if ($driver === 'sqlite') {
            $this->ensureSqliteDatabaseFileExists($connectionName);
        }

        try {
            DB::connection($connectionName)->getPdo();
        } catch (\Throwable $e) {
            $this->error('Datenbankverbindung fehlgeschlagen: '.$e->getMessage());
            $this->newLine();
            $this->line('Prüfen Sie DB_* in .env. Unter MySQL/MariaDB muss die Datenbank existieren (z. B. in phpMyAdmin anlegen).');

            return self::FAILURE;
        }

        $this->info("Verbunden ({$driver}, connection: {$connectionName}).");

        $migrateOpts = $this->laravel->environment('production') ? ['--force' => true] : [];

        if ($this->option('fresh')) {
            $code = $this->call('migrate:fresh', $migrateOpts);
        } else {
            $code = $this->call('migrate', $migrateOpts);
        }

        if ($code !== self::SUCCESS) {
            return $code;
        }

        if (! $this->option('no-seed')) {
            $code = $this->call('db:seed', $migrateOpts);
            if ($code !== self::SUCCESS) {
                return $code;
            }
        }

        $this->info('Datenbank-Installation abgeschlossen.');

        return self::SUCCESS;
    }

    private function ensureSqliteDatabaseFileExists(string $connectionName): void
    {
        $path = (string) config("database.connections.{$connectionName}.database");

        if ($path === ':memory:') {
            return;
        }

        if (! File::exists($path)) {
            $dir = dirname($path);
            if ($dir !== '.' && $dir !== '') {
                File::ensureDirectoryExists($dir);
            }
            File::put($path, '');
            $this->info("SQLite-Datei angelegt: {$path}");
        }
    }
}
