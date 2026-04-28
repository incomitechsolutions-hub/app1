<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generateKeywordCluster(array $context): array;

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generateCourseContent(array $context): array;

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function regenerateField(array $context): array;
}

