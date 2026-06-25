<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Livewire;

use Eloquage\LaravelVectorSearch\Facades\LaravelVectorSearch;
use Eloquage\LaravelVectorSearch\Models\VectorChunk;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SearchPlayground extends Component
{
    public string $query = '';

    public ?string $indexId = null;

    public int $limit = 5;

    /** @var Collection<int, VectorChunk> */
    public Collection $results;

    public function mount(): void
    {
        $this->resetErrorBag();
        $this->results = collect();

        $preferredName = config('document-archive.vector_index');
        $preferredName = is_string($preferredName) && $preferredName !== '' ? $preferredName : null;

        $this->indexId = $preferredName !== null
            ? VectorIndex::query()->where('name', $preferredName)->value('id')
            : null;

        $this->indexId ??= VectorIndex::query()->value('id');
    }

    public function search(): void
    {
        if (blank($this->query) || blank($this->indexId)) {
            $this->results = collect();

            return;
        }

        $index = VectorIndex::query()->find($this->indexId);

        if ($index === null) {
            $this->results = collect();

            return;
        }

        $this->results = LaravelVectorSearch::forIndex($index)->search($this->query, $this->limit);
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= 3) {
            $this->search();
        }
    }

    public function updatedIndexId(): void
    {
        if (filled($this->query)) {
            $this->search();
        }
    }

    /**
     * @return array<string, string>
     */
    public function getIndexOptionsProperty(): array
    {
        return VectorIndex::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function render(): View
    {
        return view()->file(__DIR__.'/../../resources/views/livewire/search-playground.blade.php');
    }
}
