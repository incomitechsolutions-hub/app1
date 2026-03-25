<?php

namespace App\Domain\PromptManagement\Policies;

use App\Domain\PromptManagement\Models\AiPrompt;
use App\Models\User;

class AiPromptPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AiPrompt $aiPrompt): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AiPrompt $aiPrompt): bool
    {
        return true;
    }

    public function delete(User $user, AiPrompt $aiPrompt): bool
    {
        return true;
    }
}
