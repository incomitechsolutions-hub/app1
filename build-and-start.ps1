# Training platform: Composer + Vite build + Laravel dev server
# Requires: PHP, Composer, Node.js (npm) on PATH
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

function Test-Cmd($name) {
    return [bool](Get-Command $name -ErrorAction SilentlyContinue)
}

if (-not (Test-Cmd "php")) {
    Write-Error "PHP nicht gefunden. Bitte PHP installieren und in den PATH aufnehmen."
    exit 1
}

if (-not (Test-Path "vendor\autoload.php")) {
    if (-not (Test-Cmd "composer")) {
        Write-Error "Composer nicht gefunden. Installiere Abhängigkeiten: composer install"
        exit 1
    }
    Write-Host "composer install..."
    composer install --no-interaction
}

if (-not (Test-Cmd "npm")) {
    Write-Warning "npm nicht gefunden — Vite-Build übersprungen. Installiere Node.js und führe aus: npm install && npm run build"
} else {
    if (-not (Test-Path "node_modules")) {
        Write-Host "npm install..."
        npm install
    }
    Write-Host "npm run build..."
    npm run build
}

Write-Host ""
Write-Host "Starte Laravel-Server: http://127.0.0.1:8000"
Write-Host "Beenden: Strg+C"
Write-Host ""

php artisan serve
