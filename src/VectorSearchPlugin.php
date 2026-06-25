<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch;

use Eloquage\FilamentVectorSearch\Pages\VectorSearchDashboard;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource;
use Eloquage\FilamentVectorSearch\Support\VectorSearchNavigation;
use Eloquage\FilamentVectorSearch\Widgets\IndexingPipelineWidget;
use Eloquage\FilamentVectorSearch\Widgets\VectorEngineStatsWidget;
use Filament\Contracts\Plugin;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;
use InvalidArgumentException;
use UnitEnum;

class VectorSearchPlugin implements Plugin
{
    /** @var class-string<Page>|null */
    private ?string $dashboardPage = null;

    /** @var class-string<resource>|null */
    private ?string $indexResource = null;

    /** @var class-string<resource>|null */
    private ?string $documentResource = null;

    /** @var array<class-string<Widget>>|null */
    private ?array $widgets = null;

    private string|UnitEnum|null $navigationGroup = null;

    public function getId(): string
    {
        return 'filament-vector-search';
    }

    public function navigationGroup(string|UnitEnum|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    /**
     * @param  class-string<Page>  $page
     */
    public function dashboardPage(string $page): static
    {
        $this->dashboardPage = $page;

        return $this;
    }

    /**
     * @param  class-string<resource>  $resource
     */
    public function indexResource(string $resource): static
    {
        $this->indexResource = $resource;

        return $this;
    }

    /**
     * @param  class-string<resource>  $resource
     */
    public function documentResource(string $resource): static
    {
        $this->documentResource = $resource;

        return $this;
    }

    /**
     * @param  array<class-string<Widget>>  $widgets
     */
    public function widgets(array $widgets): static
    {
        $this->widgets = $widgets;

        return $this;
    }

    public function register(Panel $panel): void
    {
        $navigationGroup = $this->navigationGroup ?? VectorSearchNavigation::DEFAULT_GROUP;

        VectorSearchNavigation::setGroup($navigationGroup);

        $groupLabel = VectorSearchNavigation::groupLabel();

        if ($groupLabel !== null) {
            $panel->navigationGroups([$groupLabel]);
        }

        $panel
            ->pages([
                $this->resolveDashboardPage(),
            ])
            ->resources([
                $this->resolveIndexResource(),
                $this->resolveDocumentResource(),
            ])
            ->widgets($this->resolveWidgets());
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return class-string<Page>
     */
    private function resolveDashboardPage(): string
    {
        $class = $this->dashboardPage ?? VectorSearchDashboard::class;

        if (! is_subclass_of($class, Page::class)) {
            throw new InvalidArgumentException("Dashboard page [{$class}] must extend [".Page::class.'].');
        }

        return $class;
    }

    /**
     * @return class-string<resource>
     */
    private function resolveIndexResource(): string
    {
        $class = $this->indexResource ?? VectorIndexResource::class;

        if (! is_subclass_of($class, Resource::class)) {
            throw new InvalidArgumentException("Index resource [{$class}] must extend [".Resource::class.'].');
        }

        return $class;
    }

    /**
     * @return class-string<resource>
     */
    private function resolveDocumentResource(): string
    {
        $class = $this->documentResource ?? VectorDocumentResource::class;

        if (! is_subclass_of($class, Resource::class)) {
            throw new InvalidArgumentException("Document resource [{$class}] must extend [".Resource::class.'].');
        }

        return $class;
    }

    /**
     * @return array<class-string<Widget>>
     */
    private function resolveWidgets(): array
    {
        $widgets = $this->widgets ?? [
            VectorEngineStatsWidget::class,
            IndexingPipelineWidget::class,
        ];

        foreach ($widgets as $widget) {
            if (! is_subclass_of($widget, Widget::class)) {
                throw new InvalidArgumentException("Widget [{$widget}] must extend [".Widget::class.'].');
            }
        }

        return $widgets;
    }
}
