@props([
    'items' => collect(),
    'inputName',
    'labelField' => 'name',
    'itemLabel' => __('admin.catalog.reorder_item_label'),
    'emptyText' => __('admin.catalog.reorder_empty'),
])

@php
    $items = collect($items);
@endphp

<div {{ $attributes->class(['catalog-reorder']) }} data-reorder-list>
    <ul class="list-group catalog-reorder__list" data-reorder-items>
        @forelse ($items as $item)
            @php
                $itemId = data_get($item, 'id');
                $itemName = (string) data_get($item, $labelField, '');
            @endphp

            <li class="list-group-item catalog-reorder__item" data-reorder-item data-id="{{ $itemId }}">
                <span class="catalog-reorder__position" data-reorder-position>{{ $loop->iteration }}</span>

                <div class="catalog-reorder__copy">
                    <strong>{{ $itemName }}</strong>
                </div>

                <button
                    type="button"
                    class="catalog-reorder__handle"
                    data-reorder-handle
                    aria-label="{{ __('admin.catalog.drag_item_named', ['item' => $itemLabel, 'name' => $itemName]) }}"
                    title="{{ __('admin.catalog.drag_item', ['item' => $itemLabel]) }}"
                >
                    <i class="bi bi-grip-vertical"></i>
                </button>

                <input type="hidden" name="{{ $inputName }}" value="{{ $itemId }}">
            </li>
        @empty
            <li class="list-group-item catalog-reorder__empty">
                {{ $emptyText }}
            </li>
        @endforelse
    </ul>
</div>
