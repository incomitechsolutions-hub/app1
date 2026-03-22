# Laravel unter Plesk: Datenbank und Aufruf

Diese Anleitung ergänzt die Projektbaseline (MySQL, Laravel, `public/` als Document Root).

## Wichtig: `db:seed` allein initialisiert keine Tabellen

- **`php artisan db:seed`** (auch mit `--force`) führt nur **Seeder** aus (`database/seeders/DatabaseSeeder.php`).
- **Tabellen** (inkl. `users`, Sessions, Cache, Jobs, …) entstehen durch **`php artisan migrate`**, nicht durch Seed.
- Ohne vorherige Migrationen schlagen Seeds typischerweise fehl (z. B. „Table ‚users‘ doesn’t exist“).

### Empfohlene Reihenfolge (Projektroot, wo `artisan` liegt)

```bash
php artisan migrate --force
php artisan db:seed --force
```

Nur den Standard-Admin anlegen (ohne übrige Seed-Daten aus `DatabaseSeeder`):

```bash
php artisan admin:seed-default-user --force
```

In **Produktion** ist `--force` bei `migrate` und `seed` erforderlich, wenn `APP_ENV=production` ist.

---

## 1. `.env` prüfen (MySQL, APP_KEY)

Im Projektroot eine `.env` anlegen (von `.env.example` kopieren) und setzen:

| Variable | Hinweis |
|----------|---------|
| `APP_ENV` | `production` auf dem Live-Server |
| `APP_DEBUG` | `false` in Produktion |
| `APP_KEY` | Muss gesetzt sein; falls leer: `php artisan key:generate` |
| `APP_URL` | Vollständige Basis-URL, z. B. `https://app1.hostn.de` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | In Plesk angegebener Host (oft `localhost` oder Socket) |
| `DB_PORT` | Meist `3306` |
| `DB_DATABASE` | Name der MySQL-Datenbank aus Plesk |
| `DB_USERNAME` / `DB_PASSWORD` | Zugangsdaten aus Plesk (Datenbank-Benutzer) |

Optional: `ADMIN_SEED_PASSWORD` für das Passwort des Seed-Admins (`admin@app1.hostn.de`).

---

## 2. Migrationen ausführen

```bash
php artisan migrate:status
php artisan migrate --force
```

Bei Fehlern die **vollständige** Konsolen-Ausgabe lesen (Laravel Toolkit zeigt manchmal nur die erste Zeile).

---

## 3. Seeder ausführen

```bash
php artisan db:seed --force
```

Oder nur Admin:

```bash
php artisan admin:seed-default-user --force
```

Vorher `composer install --no-dev --optimize-autoloader` im Projektroot ausführen, falls noch nicht geschehen.

---

## 4. Document Root in Plesk (`public/`)

Wenn im Browser die **Plesk-Standardseite** („default webpage“) erscheint statt Laravel, liegt das Document Root meist **nicht** auf dem Laravel-`public/`-Ordner.

- In Plesk: **Hosting-Einstellungen** der Domain → **Document Root** auf den Unterordner **`public`** der Installation setzen (z. B. `httpdocs/app/public`, je nach Upload-Pfad).
- Im Document Root muss die **`public/index.php`** von Laravel liegen (Front-Controller).

Erst wenn die Laravel-Startseite oder `/login` erreichbar ist, ist der Webserver-Teil korrekt; das ist **unabhängig** davon, ob die Datenbank schon befüllt ist.

---

## Kurz-Checkliste

1. `.env` mit MySQL-Zugang und `APP_KEY`
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan migrate --force`
4. `php artisan db:seed --force` oder `php artisan admin:seed-default-user --force`
5. Plesk Document Root = **`public/`**
