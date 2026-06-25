<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eloquage\FilamentVectorSearch\Policies\VectorIndexPolicy as BaseVectorIndexPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
use Workbench\App\Models\User;

class VectorIndexPolicy extends BaseVectorIndexPolicy
{
    protected function canManage(Authenticatable $user): bool
    {
        return $user instanceof User && $user->isAdmin();
    }
}
