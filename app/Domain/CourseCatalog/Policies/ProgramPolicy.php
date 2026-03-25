<?php

namespace App\Domain\CourseCatalog\Policies;

use App\Domain\CourseCatalog\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Program $program): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Program $program): bool
    {
        return true;
    }

    public function delete(User $user, Program $program): bool
    {
        return true;
    }
}
