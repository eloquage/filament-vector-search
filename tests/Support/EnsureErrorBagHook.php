<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tests\Support;

use Illuminate\Support\MessageBag;
use Livewire\ComponentHook;

use function Livewire\store;

class EnsureErrorBagHook extends ComponentHook
{
    public function boot(): void
    {
        if (! store($this->component)->has('errorBag') || store($this->component)->get('errorBag') === null) {
            $this->component->resetErrorBag();
        }

        if (store($this->component)->get('errorBag') === null) {
            store($this->component)->set('errorBag', new MessageBag);
        }
    }
}
