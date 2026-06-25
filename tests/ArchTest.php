<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('package source does not depend on the workbench app')
    ->expect('Eloquage\FilamentVectorSearch')
    ->not->toUse('Workbench\App');
