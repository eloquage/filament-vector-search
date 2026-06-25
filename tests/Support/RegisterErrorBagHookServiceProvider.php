<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tests\Support;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class RegisterErrorBagHookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Livewire::componentHook(EnsureErrorBagHook::class);
    }
}
