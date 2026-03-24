<?php

namespace App\Services\Admin;

use App\Models\ModuleState;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class ModuleRegistry
{
    /**
     * @return array<string, array{name: string, version: string, description?: string}>
     */
    public function definitions(): array
    {
        /** @var array<string, array{name: string, version: string, description?: string}> $definitions */
        $definitions = config('modules', []);

        return $definitions;
    }

    public function isEnabled(string $moduleKey): bool
    {
        $this->assertDefined($moduleKey);

        return Cache::rememberForever($this->cacheKey($moduleKey), function () use ($moduleKey): bool {
            return (bool) ModuleState::query()
                ->where('module_key', $moduleKey)
                ->value('enabled') ?? true;
        });
    }

    /**
     * @return array<int, array{key: string, name: string, version: string, description: string, enabled: bool}>
     */
    public function allForOverview(): array
    {
        $rows = [];

        foreach ($this->definitions() as $key => $meta) {
            $rows[] = [
                'key' => $key,
                'name' => $meta['name'],
                'version' => $meta['version'],
                'description' => (string) ($meta['description'] ?? ''),
                'enabled' => $this->isEnabled($key),
            ];
        }

        return $rows;
    }

    public function setEnabled(string $moduleKey, bool $enabled): void
    {
        $this->assertDefined($moduleKey);

        ModuleState::query()->updateOrCreate(
            ['module_key' => $moduleKey],
            ['enabled' => $enabled]
        );

        Cache::forget($this->cacheKey($moduleKey));
    }

    private function cacheKey(string $moduleKey): string
    {
        return 'admin.module.enabled.'.$moduleKey;
    }

    private function assertDefined(string $moduleKey): void
    {
        if (! array_key_exists($moduleKey, $this->definitions())) {
            throw new InvalidArgumentException('Unknown module key: '.$moduleKey);
        }
    }
}
