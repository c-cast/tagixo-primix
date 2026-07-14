@php
    $isImage = $record->isImage();
    $isVideo = $record->isVideo();
    $previewUrl = $record->thumbnail_url ?? $record->url;

    $typeColors = [
        'image'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'video'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'document' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'other'    => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    ];
    $typeColor = $typeColors[$record->type] ?? $typeColors['other'];
    $rowActions = $table->getRowActions();
@endphp

<div class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800">

    {{-- Preview area --}}
    <div class="relative w-full overflow-hidden bg-gray-100 dark:bg-gray-700" style="height: 11rem;">

        @if($isImage && $previewUrl)
            <img
                src="{{ $previewUrl }}"
                alt="{{ $record->alt_text ?? $record->filename }}"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            />
        @elseif($isVideo)
            <div class="flex h-full w-full flex-col items-center justify-center gap-2 bg-gray-900">
                <svg class="h-14 w-14 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <span class="text-xs font-medium text-gray-400 uppercase">{{ $record->extension }}</span>
            </div>
        @else
            <div class="flex h-full w-full flex-col items-center justify-center gap-1 bg-gray-50 dark:bg-gray-800">
                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-xs text-gray-400 uppercase font-medium">{{ $record->extension }}</span>
            </div>
        @endif

        {{-- Type badge --}}
        <span class="absolute left-2 top-2 rounded-md px-1.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide {{ $typeColor }}">
            {{ $record->type }}
        </span>

        {{-- Checkbox --}}
        <div class="absolute right-2 top-2">
            <input
                type="checkbox"
                wire:model.live="selectedRecords"
                value="{{ $record->getKey() }}"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
        </div>
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-3 py-2 dark:border-gray-700">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-900 dark:text-white" title="{{ $record->filename }}">
                {{ $record->title ?: $record->filename }}
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $record->formatted_size }}</p>
        </div>
        @if(count($rowActions))
            <div class="flex shrink-0 items-center gap-1">
                @foreach($rowActions as $action)
                    @php($action->record($record)->recordTitle(data_get($record, $table->getRecordTitleAttribute() ?? 'id')))
                    @if($action->isVisible())
                        {{ $action }}
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
