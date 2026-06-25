<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Workbench\App\Models\User;

class VectorIndexPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, VectorIndex $vectorIndex): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, VectorIndex $vectorIndex): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, VectorIndex $vectorIndex): bool
    {
        return $user->isAdmin();
    }
}
