<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Workbench\App\Models\User;

class VectorDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, VectorDocument $vectorDocument): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, VectorDocument $vectorDocument): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, VectorDocument $vectorDocument): bool
    {
        return $user->isAdmin();
    }
}
