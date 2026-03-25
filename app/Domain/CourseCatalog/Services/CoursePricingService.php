<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\CourseCatalog\Models\CourseDiscountTier;
use App\Domain\CourseCatalog\Models\CourseGroupDiscountTier;

class CoursePricingService
{
    /**
     * Basis-Listenpreis pro Teilnehmer: Kurspreis setzt Standard überschreibt.
     * Ohne Kurspreis: globaler Standard-Listenpreis (falls gesetzt).
     */
    public function baseListUnitPrice(Course $course): ?float
    {
        if ($course->price !== null) {
            return (float) $course->price;
        }

        $g = CourseCatalogGlobalSetting::singleton();
        if ($g->default_list_price !== null) {
            return (float) $g->default_list_price;
        }

        return null;
    }

    /**
     * Effektive Rabattstufen: eigene Kurs-Stufen, sonst globale (wenn Gruppenrabatt aktiv).
     *
     * @return array<int, array{min_participants: int, discount_percent: float}>
     */
    public function effectiveDiscountTiers(Course $course): array
    {
        $course->loadMissing('discountTiers');

        if ($course->discountTiers->isNotEmpty()) {
            return $course->discountTiers
                ->sortBy('sort_order')
                ->values()
                ->map(fn (CourseDiscountTier $t) => [
                    'min_participants' => $t->min_participants,
                    'discount_percent' => (float) $t->discount_percent,
                ])
                ->all();
        }

        $global = CourseCatalogGlobalSetting::singleton()->load('groupDiscountTiers');
        if (! $global->group_discount_enabled) {
            return [];
        }

        return $global->groupDiscountTiers
            ->sortBy('sort_order')
            ->values()
            ->map(fn (CourseGroupDiscountTier $t) => [
                'min_participants' => $t->min_participants,
                'discount_percent' => (float) $t->discount_percent,
            ])
            ->all();
    }

    /**
     * Höchste passende Rabattstufe für die Teilnehmerzahl (Listenpreis pro Person).
     */
    public function discountPercentForParticipants(Course $course, int $participants): float
    {
        $participants = max(1, $participants);
        $tiers = $this->effectiveDiscountTiers($course);
        if ($tiers === []) {
            return 0.0;
        }

        usort($tiers, fn ($a, $b) => $b['min_participants'] <=> $a['min_participants']);
        foreach ($tiers as $tier) {
            if ($participants >= $tier['min_participants']) {
                return $tier['discount_percent'];
            }
        }

        return 0.0;
    }

    /**
     * Daten für die öffentliche Kursseite (Preisrechner).
     *
     * @return array<string, mixed>|null
     */
    public function buildPublicPayload(Course $course): ?array
    {
        $listUnit = $this->baseListUnitPrice($course);
        if ($listUnit === null) {
            return null;
        }

        $currency = strtoupper((string) ($course->currency_code ?? 'EUR'));
        $tiers = $this->effectiveDiscountTiers($course);

        return [
            'listUnit' => $listUnit,
            'currency' => $currency,
            'tiers' => $tiers,
        ];
    }

    /**
     * @return array{list_unit: float, discount_percent: float, unit_after_discount: float, total: float, currency: string}
     */
    public function quote(Course $course, int $participants): array
    {
        $participants = max(1, $participants);
        $listUnit = $this->baseListUnitPrice($course) ?? 0.0;
        $pct = $this->discountPercentForParticipants($course, $participants);
        $factor = max(0.0, 1 - ($pct / 100));
        $unitAfter = round($listUnit * $factor, 2);
        $total = round($unitAfter * $participants, 2);
        $currency = strtoupper((string) ($course->currency_code ?? 'EUR'));

        return [
            'list_unit' => $listUnit,
            'discount_percent' => $pct,
            'unit_after_discount' => $unitAfter,
            'total' => $total,
            'currency' => $currency,
        ];
    }
}
