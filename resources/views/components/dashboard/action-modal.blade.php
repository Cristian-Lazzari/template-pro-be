@props([
    'title' => null,
    'titleId' => null,
    'eyebrow' => null,
    'description' => null,
    'entityLabel' => null,
    'subject' => null,
    'dateSlot' => null,
    'tone' => 'neutral',
    'preview' => false,
])

@php
    $hasDetails = isset($details) && trim((string) $details) !== '';
    $hasFooter = isset($footer) && trim((string) $footer) !== '';
    $hasContent = trim((string) $slot) !== '';
    $hasTitleIcon = isset($titleIcon) && trim((string) $titleIcon) !== '';
    $resolvedTitle = $title ?: $eyebrow;
    $showEyebrow = $eyebrow && $title;
@endphp

<div {{ $attributes->class(['modal-content mymodal_make_res dashboard-action-modal', 'dashboard-action-modal--' . $tone]) }}>
    <div class="modal-header">
        <div class="dashboard-action-modal__heading">
            @if ($showEyebrow)
                <p class="dashboard-action-modal__eyebrow">{{ $eyebrow }}</p>
            @endif

            <div class="dashboard-action-modal__title-copy">
                <div class="dashboard-action-modal__title-row">
                    @if ($hasTitleIcon)
                        <span class="dashboard-action-modal__title-icon" aria-hidden="true">
                            {{ $titleIcon }}
                        </span>
                    @endif

                    @if ($resolvedTitle)
                        <h2 @if ($titleId) id="{{ $titleId }}" @endif class="modal-title">{{ $resolvedTitle }}</h2>
                    @endif
                </div>

                @if ($description)
                    <p class="dashboard-action-modal__description">{{ $description }}</p>
                @endif
            </div>
        </div>

        <button
            type="button"
            class="btn-close dashboard-action-modal__close"
            @unless ($preview)
                data-bs-dismiss="modal"
                aria-label="Close"
            @else
                tabindex="-1"
                aria-hidden="true"
            @endunless
        ></button>
    </div>

    <div class="modal-body">
        @if ($entityLabel || $subject || $dateSlot)
            <div class="dashboard-action-modal__summary">
                @if ($entityLabel)
                    <span class="dashboard-action-modal__summary-label">{{ $entityLabel }}</span>
                @endif

                @if ($subject)
                    <strong>{{ $subject }}</strong>
                @endif

                @if ($dateSlot)
                    <p>{{ $dateSlot }}</p>
                @endif
            </div>
        @endif

        @if ($hasDetails)
            <div class="dashboard-action-modal__details">
                {{ $details }}
            </div>
        @endif

        @if ($hasContent)
            <div class="dashboard-action-modal__content">
                {{ $slot }}
            </div>
        @endif
    </div>

    @if ($hasFooter)
        <div class="modal-footer">
            {{ $footer }}
        </div>
    @endif
</div>
