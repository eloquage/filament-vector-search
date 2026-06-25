<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tests\Support;

use Illuminate\Support\MessageBag;
use Livewire\ComponentHook;

class EnsureErrorBagHook extends ComponentHook
{
    public function boot(): void
    {
        $this->component->setErrorBag(new MessageBag);
    }

    public function hydrate($memo): void
    {
        $this->component->setErrorBag(new MessageBag);
    }
}
