<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eloquage\FilamentVectorSearch\Policies\VectorDocumentPolicy as BaseVectorDocumentPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
use Workbench\App\Models\User;

class VectorDocumentPolicy extends BaseVectorDocumentPolicy
{
    protected function canManage(Authenticatable $user): bool
    {
        return $user instanceof User && $user->isAdmin();
    }
}
