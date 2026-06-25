<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-[2fr_1fr]">
        <div class="space-y-3">
            <label for="semantic-query" class="text-sm font-medium text-gray-700 dark:text-gray-200">Semantic query</label>
            <textarea
                id="semantic-query"
                wire:model.live.debounce.500ms="query"
                rows="3"
                placeholder="Ask anything about your indexed knowledge base..."
                aria-label="Semantic query"
                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            ></textarea>
        </div>

        <div class="space-y-3">
            <label for="vector-index" class="text-sm font-medium text-gray-700 dark:text-gray-200">Vector index</label>
            <select
                id="vector-index"
                wire:model.live="indexId"
                aria-label="Vector index"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            >
                @foreach ($this->indexOptions as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>

            <label for="result-limit" class="text-sm font-medium text-gray-700 dark:text-gray-200">Result limit</label>
            <input
                id="result-limit"
                type="number"
                min="1"
                max="20"
                wire:model.live="limit"
                aria-label="Result limit"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" role="list" aria-label="Search results">
        @forelse ($results as $chunk)
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950" role="listitem">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $chunk->vectorDocument?->title ?? 'Untitled document' }}
                    </p>
                    <span class="rounded-full bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                        Chunk #{{ $chunk->chunk_index + 1 }}
                    </span>
                </div>
                <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ \Illuminate\Support\Str::limit($chunk->content, 280) }}
                </p>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400" role="status">
                @if (blank($query))
                    Start typing to explore semantic matches across your vector indexes.
                @else
                    No relevant chunks found for this query.
                @endif
            </div>
        @endforelse
    </div>
</div>
