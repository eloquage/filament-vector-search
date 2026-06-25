<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Policies;

use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class VectorIndexPolicy
{
    abstract protected function canManage(Authenticatable $user): bool;

    public function viewAny(Authenticatable $user): bool
    {
        return $this->canManage($user);
    }

    public function view(Authenticatable $user, VectorIndex $vectorIndex): bool
    {
        return $this->canManage($user);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->canManage($user);
    }

    public function update(Authenticatable $user, VectorIndex $vectorIndex): bool
    {
        return $this->canManage($user);
    }

    public function delete(Authenticatable $user, VectorIndex $vectorIndex): bool
    {
        return $this->canManage($user);
    }
}
