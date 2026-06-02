@php
    $errors          = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $isEdit          = $model->exists;
    $bodyHtmlValue   = old('body_html', $model->body_html ?: $model->body);
    $endingValue     = old('ending', $model->ending);
    $statusValue     = old('status', $model->status ?: 'draft');
    $objectValue     = old('object', $model->object);
    $headingValue    = old('heading', $model->heading);
    $senderValue     = old('sender', $model->sender);
    $hasPromotion = old('has_promotion', $model->has_promotion ?? false);

    $variableGroups = [
        __('admin.marketing.mailer.variable_group_customer') => [
            'customer_name'       => 'Nome e cognome',
            'customer_first_name' => 'Nome',
            'customer_last_name'  => 'Cognome',
            'customer_email'      => 'Email',
            'customer_phone'      => 'Telefono',
            'customer_age'        => 'Età',
            'customer_gender'     => 'Sesso',
        ],
    ];

    $appName    = config('configurazione.APP_NAME', config('app.name', 'R'));
    $logoLetter = mb_strtoupper(mb_substr($appName, 0, 1));

    $mailerImageUrl = static function (?string $path): ?string {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/storage/') || str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('public/storage/' . $path);
    };

    $imageOneUrl = $mailerImageUrl($model->img_1);
    $imageTwoUrl = $mailerImageUrl($model->img_2);
@endphp

@include('admin.Marketing.partials.form-style')

<style>
    .mail-model-form {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .mail-model-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.28fr) minmax(320px, .72fr);
        gap: 18px;
        align-items: start;
        width: 100%;
        min-width: 0;
    }

    .mail-model-form .order-detail__section { min-width: 0; }

    .mail-model-form .split {
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
    }

    .mail-model-form .label_c {
        flex-wrap: wrap;
        line-height: 1.25;
    }

    .mail-model-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
    .mail-model-form select {
        opacity: 1;
        text-align: left;
        color: var(--c3);
        background: rgba(216, 221, 232, 0.06);
        border: 1px solid rgba(216, 221, 232, 0.16);
        border-radius: 12px;
        min-height: 44px;
        height: auto;
        padding: 10px 14px;
    }

    .mail-model-form input[type="file"] {
        min-height: 44px;
        height: auto;
        padding: 10px;
        cursor: pointer;
        background: rgba(216, 221, 232, 0.06);
        border: 1px solid rgba(216, 221, 232, 0.16);
        border-radius: 12px;
        color: var(--c3);
    }

    .mail-model-file-field {
        display: grid;
        gap: 8px;
    }

    .mail-model-file-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        min-height: 32px;
    }

    .mail-model-file-clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 7px 10px;
        border-radius: 10px;
        border: 1px solid rgba(255, 141, 141, 0.22);
        background: rgba(206, 59, 59, 0.1);
        color: rgba(255, 210, 210, 0.95);
        font: inherit;
        font-size: var(--fs-100);
        font-weight: 900;
        cursor: pointer;
    }

    .mail-model-file-clear:hover,
    .mail-model-file-clear:focus-visible {
        background: rgba(206, 59, 59, 0.18);
        border-color: rgba(255, 141, 141, 0.36);
        outline: none;
    }

    .mail-model-file-status {
        color: rgba(216, 221, 232, 0.66);
        font-size: var(--fs-100);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .mail-model-form input::placeholder {
        color: rgba(216, 221, 232, 0.52);
        text-align: left;
    }

    .mail-model-form input:focus,
    .mail-model-form select:focus,
    .mail-model-form input:hover,
    .mail-model-form select:hover {
        opacity: 1;
        border-color: rgba(14, 183, 146, 0.55);
        background: rgba(216, 221, 232, 0.08);
    }

    /* ── Contenteditable editor ─────────────────────────────── */
    .var-editor-wrap { position: relative; }

    .var-editor {
        position: relative;
        min-height: 180px;
        max-height: 520px;
        overflow-y: auto;
        padding: 14px;
        border-radius: 12px;
        border: 1px solid rgba(216, 221, 232, 0.16);
        background: rgba(216, 221, 232, 0.06);
        color: var(--c3);
        font-size: inherit;
        font-family: inherit;
        line-height: 1.55;
        outline: none;
        cursor: text;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .var-editor--small {
        min-height: 90px;
        max-height: 220px;
    }

    .var-editor:focus,
    .var-editor:hover {
        border-color: rgba(14, 183, 146, 0.55);
        background: rgba(216, 221, 232, 0.08);
    }

    .var-editor[data-empty="1"]::before {
        content: attr(data-placeholder);
        color: rgba(216, 221, 232, 0.4);
        pointer-events: none;
        position: absolute;
        top: 14px;
        left: 14px;
        right: 14px;
    }

    /* ── Inline chips (dentro l'editor) ─────────────────────── */
    .var-chip-inline {
        display: inline-flex;
        align-items: center;
        padding: 1px 7px 2px;
        margin: 0 1px;
        border-radius: 5px;
        background: rgba(14, 183, 146, 0.16);
        border: 1px solid rgba(14, 183, 146, 0.3);
        color: rgba(14, 183, 146, 0.96);
        font-size: 0.88em;
        font-weight: 700;
        cursor: default;
        user-select: none;
        white-space: nowrap;
        vertical-align: middle;
        font-family: inherit;
    }

    .var-chip-inline::before {
        content: '@';
        font-size: 0.8em;
        opacity: 0.65;
        margin-right: 2px;
    }

    /* ── Chips lista variabili ──────────────────────────────── */
    .mail-model-variable-list { display: grid; gap: 14px; }
    .mail-model-variable-group { display: grid; gap: 8px; }
    .mail-model-variable-group strong { color: var(--c3); font-size: var(--fs-300); }
    .mail-model-variable-chips { display: flex; flex-wrap: wrap; gap: 8px; }

    .mail-model-variable-chip {
        display: inline-flex;
        max-width: 100%;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.14);
        background: rgba(216, 221, 232, 0.06);
        color: rgba(216, 221, 232, 0.88);
        font-size: var(--fs-200);
        overflow-wrap: anywhere;
        font-family: inherit;
    }

    .mail-model-variable-chip.var-chip {
        cursor: pointer;
        transition: background .12s, border-color .12s;
    }

    .mail-model-variable-chip.var-chip:hover {
        background: rgba(14, 183, 146, 0.14);
        border-color: rgba(14, 183, 146, 0.32);
        color: rgba(14, 183, 146, 0.98);
    }

    .mail-model-variable-chip.var-chip.is-disabled {
        cursor: not-allowed;
        opacity: .45;
        filter: grayscale(.35);
    }

    .mail-model-variable-chip.var-chip.is-disabled:hover {
        background: rgba(216, 221, 232, 0.06);
        border-color: rgba(216, 221, 232, 0.14);
        color: rgba(216, 221, 232, 0.88);
    }

    /* ── Preview panel ──────────────────────────────────────── */
    .mail-model-preview {
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: #e9f0fb;
        color: #04001d;
    }

    .mail-model-preview__inner {
        max-width: 620px;
        margin: 0 auto;
        padding: 26px 22px;
        text-align: center;
    }

    .mail-model-preview__logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin-bottom: 18px;
        border-radius: 18px;
        background: #090333;
        color: #e9f0fb;
        font-weight: 800;
    }

    .mail-model-preview h4,
    .mail-model-preview p { overflow-wrap: anywhere; }

    .mail-model-preview__image {
        display: block;
        max-width: 450px;
        max-height: 260px;
        height: auto;
        margin: 20px auto;
        border-radius: 10px;
        object-fit: contain;
        box-shadow: 0 10px 24px rgba(4, 0, 29, .12);
    }

    .mail-model-preview__image[hidden] {
        display: none;
    }

    .mail-model-preview__image--top {
        width: min(60%, 450px);
    }

    .mail-model-preview__image--bottom {
        width: min(70%, 450px);
    }

    .mail-model-preview__subject {
        margin: 0 0 12px;
        color: rgba(4, 0, 29, .68);
        font-size: 13px;
        text-transform: uppercase;
    }

    .mail-model-preview__body {
        margin: 22px 0;
        text-align: left;
        line-height: 1.55;
    }

    .mail-model-preview__cta {
        display: inline-flex;
        margin: 10px auto 18px;
        padding: 12px 20px;
        border-radius: 10px;
        background: #04001d;
        color: #e9f0fb;
        text-decoration: none;
        font-weight: 800;
    }

    .mail-model-preview__footer {
        margin: 24px -22px -26px;
        padding: 18px 22px;
        background: #090333;
        color: #fff;
        font-size: 12px;
    }

    /* ── Token variabile nella preview ──────────────────────── */
    .var-token {
        background: rgba(14, 183, 146, 0.18);
        color: rgba(14, 183, 146, 0.98);
        border-radius: 3px;
        padding: 1px 4px;
        font-size: 0.88em;
        font-weight: 700;
        white-space: nowrap;
    }

    .preview-placeholder,
    .is-placeholder {
        color: rgba(4, 0, 29, .35) !important;
        font-style: italic;
        font-weight: 600 !important;
    }

    /* ── Dropdown autocomplete ──────────────────────────────── */
    .var-autocomplete {
        position: fixed;
        z-index: 9999;
        min-width: 220px;
        max-width: 320px;
        max-height: 260px;
        overflow-y: auto;
        padding: 6px;
        border-radius: 10px;
        border: 1px solid rgba(216, 221, 232, 0.18);
        background: rgba(9, 3, 51, 0.97);
        box-shadow: 0 14px 34px rgba(0, 0, 0, .32);
        display: none;
    }

    .var-autocomplete__item {
        display: flex;
        align-items: baseline;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 7px;
        cursor: pointer;
        color: rgba(216, 221, 232, 0.9);
        font-size: 13px;
    }

    .var-autocomplete__item strong {
        font-family: 'Courier New', monospace;
        color: rgba(14, 183, 146, 0.96);
        font-size: 12px;
        flex-shrink: 0;
    }

    .var-autocomplete__item small {
        color: rgba(216, 221, 232, 0.55);
        font-size: 11px;
    }

    .var-autocomplete__item:hover,
    .var-autocomplete__item.is-selected {
        background: rgba(216, 221, 232, 0.08);
    }

    /* ── Blocco promozione nella preview ────────────────────── */
    .preview-promotion-block {
        margin: 16px 0;
        padding: 20px;
        border-radius: 12px;
        background: #eef2ff;
        border: 1px solid rgba(4, 0, 29, .1);
        text-align: center;
    }

    .preview-promotion-block__discount {
        font-size: 36px;
        font-weight: 900;
        color: #04001d;
        margin: 0 0 4px;
        line-height: 1;
    }

    .preview-promotion-block__name {
        font-size: 14px;
        font-weight: 700;
        color: #04001d;
        margin: 4px 0;
    }

    .preview-promotion-block__expiry {
        font-size: 11px;
        color: rgba(4, 0, 29, .5);
        margin: 6px 0 0;
    }

    @media (max-width: 1100px) {
        .mail-model-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .mail-model-preview__image,
        .mail-model-preview__image--top,
        .mail-model-preview__image--bottom {
            width: 100%;
        }
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger">
        {{ __('admin.marketing.mailer.check_fields') }}
    </div>
@endif

<form class="creation marketing-form-shell mail-model-form mt-4" action="{{ $action }}" enctype="multipart/form-data" method="POST">
    @csrf
    @if ($isEdit)
        <input type="hidden" name="id" value="{{ $model->id }}">
    @endif
    <input type="hidden" name="type" value="marketing">
    <input type="hidden" name="channel" value="email">

    <div class="mail-model-grid">
        {{-- ── Colonna sinistra: campi form ─────────────────────── --}}
        <div class="mail-model-form">
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="card-text" /></span>
                        {{ __('admin.marketing.mailer.model_info') }}
                    </h3>
                </div>

                <div class="split">
                    <div>
                        <label class="label_c" for="name">
                            <x-icon name="type" />
                            {{ __('admin.marketing.mailer.model_name') }}
                        </label>
                        <p>
                            <input value="{{ old('name', $model->name) }}" type="text" name="name" id="name"
                                   placeholder="{{ __('admin.marketing.mailer.name_placeholder') }}">
                        </p>
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="status">
                            <x-icon name="toggle-on" />
                            {{ __('admin.marketing.mailer.status') }}
                        </label>
                        <p>
                            <select name="status" id="status">
                                <option value="draft"    @selected($statusValue === 'draft')>{{ __('admin.marketing.mailer.draft') }}</option>
                                <option value="active"   @selected($statusValue === 'active')>{{ __('admin.marketing.mailer.active') }}</option>
                                <option value="archived" @selected($statusValue === 'archived')>{{ __('admin.marketing.mailer.archived') }}</option>
                            </select>
                        </p>
                        @error('status') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="split">
                    <div>
                        <label class="label_c" for="object">
                            <x-icon name="envelope-fill" />
                            {{ __('admin.marketing.mailer.email_subject') }}
                        </label>
                        <p>
                            <input value="{{ $objectValue }}" type="text" name="object" id="object"
                                   placeholder="{{ __('admin.marketing.mailer.object_placeholder') }}">
                        </p>
                        @error('object') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="sender">
                            <x-icon name="person-lines-fill" />
                            {{ __('admin.marketing.mailer.sender') }}
                        </label>
                        <p>
                            <input value="{{ $senderValue }}" type="text" name="sender" id="sender"
                                   placeholder="{{ __('admin.marketing.mailer.sender_placeholder') }}">
                        </p>
                        @error('sender') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Selettore tipo modello --}}
                <input type="hidden" name="has_promotion" value="0">
                <div class="model-type-picker" id="model-type-picker">
                    <label class="model-type-option">
                        <input type="radio" name="has_promotion" id="has_promotion_no" value="0"
                               {{ ! $hasPromotion ? 'checked' : '' }}>
                        <div class="model-type-option__card">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="model-type-option__icon">
                                    <x-icon name="envelope-fill" />
                                </span>
                                <span class="model-type-option__dot"></span>
                            </div>
                            <div class="model-type-option__label">
                                <strong>Solo messaggio</strong>
                                <small>Nessun blocco promozione, nessun bottone CTA</small>
                            </div>
                        </div>
                    </label>

                    <label class="model-type-option">
                        <input type="radio" name="has_promotion" id="has_promotion_yes" value="1"
                               {{ $hasPromotion ? 'checked' : '' }}>
                        <div class="model-type-option__card">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="model-type-option__icon">
                                    <x-icon name="gift-fill" />
                                </span>
                                <span class="model-type-option__dot"></span>
                            </div>
                            <div class="model-type-option__label">
                                <strong>Con promozione</strong>
                                <small>Mostra sconto, nome e bottone CTA contestuale</small>
                            </div>
                        </div>
                    </label>
                </div>

            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="file-earmark-richtext-fill" /></span>
                        {{ __('admin.marketing.mailer.email_content') }}
                    </h3>
                </div>

                <div>
                    <label class="label_c" for="heading">
                        <x-icon name="type-h1" />
                        {{ __('admin.marketing.mailer.main_title') }}
                    </label>
                    <p>
                        <input value="{{ $headingValue }}" type="text" name="heading" id="heading"
                               placeholder="{{ __('admin.marketing.mailer.heading_placeholder') }}">
                    </p>
                    @error('heading') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label_c">
                        <x-icon name="body-text" />
                        {{ __('admin.marketing.mailer.html_body') }}
                    </label>
                    <div class="var-editor-wrap">
                        <div class="var-editor"
                             id="editor_body_html"
                             contenteditable="true"
                             data-field="body_html"
                             data-placeholder="{{ __('admin.marketing.mailer.html_body_placeholder') }}"></div>
                        <textarea name="body_html" id="body_html" hidden>{{ $bodyHtmlValue }}</textarea>
                    </div>
                    @error('body_html') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label_c">
                        <x-icon name="chat-quote-fill" />
                        {{ __('admin.marketing.mailer.ending') }}
                    </label>
                    <div class="var-editor-wrap">
                        <div class="var-editor var-editor--small"
                             id="editor_ending"
                             contenteditable="true"
                             data-field="ending"
                             data-placeholder="{{ __('admin.marketing.mailer.ending_placeholder') }}"></div>
                        <textarea name="ending" id="ending" hidden>{{ $endingValue }}</textarea>
                    </div>
                    @error('ending') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="split">
                    <div>
                        <label class="label_c" for="img_1">
                            <x-icon name="image-fill" />
                            {{ __('admin.marketing.mailer.top_image') }}
                        </label>
                        <div class="mail-model-file-field" data-image-upload>
                            <input type="file"
                                   id="img_1"
                                   name="img_1"
                                   accept="image/*"
                                   data-image-input
                                   data-preview-target="preview-img-1"
                                   data-existing-src="{{ $imageOneUrl ?? '' }}">
                            <div class="mail-model-file-actions">
                                <button class="mail-model-file-clear"
                                        type="button"
                                        data-image-clear
                                        hidden
                                        aria-label="{{ __('admin.Rimuovi') }} {{ __('admin.marketing.mailer.top_image') }}"
                                        title="{{ __('admin.Rimuovi') }} {{ __('admin.marketing.mailer.top_image') }}">
                                    <x-icon name="x-circle-fill" />
                                    <span>{{ __('admin.Rimuovi') }}</span>
                                </button>
                                <span class="mail-model-file-status"
                                      data-image-status
                                      data-default-text="{{ $model->img_1 ? __('admin.marketing.mailer.image_present', ['name' => basename($model->img_1)]) : '' }}">
                                    @if ($model->img_1)
                                        {{ __('admin.marketing.mailer.image_present', ['name' => basename($model->img_1)]) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @error('img_1') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="img_2">
                            <x-icon name="image-fill" />
                            {{ __('admin.marketing.mailer.bottom_image') }}
                        </label>
                        <div class="mail-model-file-field" data-image-upload>
                            <input type="file"
                                   id="img_2"
                                   name="img_2"
                                   accept="image/*"
                                   data-image-input
                                   data-preview-target="preview-img-2"
                                   data-existing-src="{{ $imageTwoUrl ?? '' }}">
                            <div class="mail-model-file-actions">
                                <button class="mail-model-file-clear"
                                        type="button"
                                        data-image-clear
                                        hidden
                                        aria-label="{{ __('admin.Rimuovi') }} {{ __('admin.marketing.mailer.bottom_image') }}"
                                        title="{{ __('admin.Rimuovi') }} {{ __('admin.marketing.mailer.bottom_image') }}">
                                    <x-icon name="x-circle-fill" />
                                    <span>{{ __('admin.Rimuovi') }}</span>
                                </button>
                                <span class="mail-model-file-status"
                                      data-image-status
                                      data-default-text="{{ $model->img_2 ? __('admin.marketing.mailer.image_present', ['name' => basename($model->img_2)]) : '' }}">
                                    @if ($model->img_2)
                                        {{ __('admin.marketing.mailer.image_present', ['name' => basename($model->img_2)]) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @error('img_2') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        </div>

        {{-- ── Colonna destra: preview + variabili ──────────────── --}}
        <aside class="mail-model-form">
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="eye-fill" /></span>
                        {{ __('admin.marketing.mailer.structure_preview') }}
                    </h3>
                </div>

                <div class="mail-model-preview" id="live-preview">
                    <div class="mail-model-preview__inner">
                        <span class="mail-model-preview__logo"
                              id="preview-app-name"
                              data-value="{{ $logoLetter }}">{{ $logoLetter }}</span>

                        <p class="mail-model-preview__subject"
                           id="preview-subject"
                           data-placeholder="{{ __('admin.marketing.mailer.preview_object') }}"></p>

                        <h4 id="preview-heading"
                            data-placeholder="{{ __('admin.marketing.mailer.preview_heading') }}"></h4>

                        <img class="mail-model-preview__image mail-model-preview__image--top"
                             id="preview-img-1"
                             @if ($imageOneUrl) src="{{ $imageOneUrl }}" @endif
                             alt="{{ __('admin.marketing.mailer.top_image') }}"
                             @unless ($imageOneUrl) hidden @endunless>

                        <div class="mail-model-preview__body"
                             id="preview-body"
                             data-placeholder="{{ __('admin.marketing.mailer.preview_body') }}"></div>

                        <div id="preview-promotion-block" style="{{ $hasPromotion ? '' : 'display:none;' }}">
                            <div class="preview-promotion-block">
                                <p class="preview-promotion-block__discount">20%</p>
                                <p class="preview-promotion-block__name">Promozione esempio</p>
                                <p class="preview-promotion-block__expiry">Valida fino al 31/12/2026</p>
                            </div>
                        </div>

                        <div id="preview-cta-wrap" style="{{ $hasPromotion ? '' : 'display:none;' }}">
                            <a class="mail-model-preview__cta" href="#" id="preview-cta-btn">
                                Scopri la promozione
                            </a>
                            <p style="font-size:11px;color:rgba(4,0,29,.45);margin:4px 0 0;">
                                Il testo verrà adattato al contesto: "Ordina ora" (asporto/delivery), "Prenota ora" (tavolo)
                            </p>
                        </div>

                        <img class="mail-model-preview__image mail-model-preview__image--bottom"
                             id="preview-img-2"
                             @if ($imageTwoUrl) src="{{ $imageTwoUrl }}" @endif
                             alt="{{ __('admin.marketing.mailer.bottom_image') }}"
                             @unless ($imageTwoUrl) hidden @endunless>

                        <div id="preview-ending-wrap">
                            <p id="preview-ending"></p>
                        </div>

                        <div>
                            <p id="preview-sender" style="font-weight:900;font-size:15px;margin:1rem 0 0;"></p>
                        </div>

                        <div class="mail-model-preview__footer">
                            {{ __('admin.marketing.mailer.preview_footer') }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="braces" /></span>
                        {{ __('admin.marketing.mailer.available_variables') }}
                    </h3>
                </div>

                <p class="menu-dashboard__copy">
                    {{ __('admin.marketing.mailer.variables_help') }}
                    Clicca una variabile per inserirla nel campo attivo, oppure scrivi <strong>@</strong> nel testo.
                </p>

                <div class="mail-model-variable-list">
                    {{-- Blocco promozione (solo se has_promotion attivo) --}}
                    <div class="mail-model-variable-group" id="promotion-var-group" style="{{ $hasPromotion ? '' : 'display:none;' }}">
                        <strong>Promozione</strong>
                        <div class="mail-model-variable-chips">
                            <code class="mail-model-variable-chip var-chip" data-var="promotion"
                                  style="border-color:rgba(14,183,146,.35);background:rgba(14,183,146,.1);color:rgba(14,183,146,.96);">
                                &#64;Blocco promozione
                            </code>
                        </div>
                        <p class="menu-dashboard__copy" style="margin:4px 0 0;">
                            Inserisce il blocco con nome, sconto e scadenza della promozione associata.
                        </p>
                    </div>

                    @foreach ($variableGroups as $group => $items)
                        <div class="mail-model-variable-group">
                            <strong>{{ $group }}</strong>
                            <div class="mail-model-variable-chips">
                                @foreach ($items as $variable => $label)
                                    <code class="mail-model-variable-chip var-chip" data-var="{{ $variable }}">&#64;{{ $label }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <section class="order-detail__section">
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <button class="order-detail__contact" type="submit">
                <x-icon name="check2-circle" />
                <span>{{ $isEdit ? __('admin.marketing.mailer.save_model') : __('admin.marketing.mailer.create_model') }}</span>
            </button>
            <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.index') }}">
                <x-icon name="arrow-left-circle-fill" />
                <span>{{ __('admin.common.cancel') }}</span>
            </a>
        </div>
        <p class="menu-dashboard__copy mt-3">
            {{ __('admin.marketing.mailer.model_form_note') }}
        </p>
    </section>

    <div id="var-autocomplete" class="var-autocomplete" role="listbox"></div>
</form>

@verbatim
<script>
(function () {
    // ─── VARIABILI ────────────────────────────────────────────────
    const vars = [
        { name: 'customer_name',       label: 'Nome e cognome' },
        { name: 'customer_first_name', label: 'Nome' },
        { name: 'customer_last_name',  label: 'Cognome' },
        { name: 'customer_email',      label: 'Email' },
        { name: 'customer_phone',      label: 'Telefono' },
        { name: 'customer_age',        label: 'Età' },
        { name: 'customer_gender',     label: 'Sesso' },
        { name: 'promotion',           label: 'Blocco promozione', isBlock: true },
    ];

    const PROMO_BLOCK_PLACEHOLDER =
        '<div style="margin:12px 0;padding:18px 16px;border-radius:10px;background:#eef2ff;border:1px dashed rgba(4,0,29,.15);text-align:center;">' +
        '<p style="font-size:30px;font-weight:900;color:#04001d;margin:0 0 3px;line-height:1.1;">20%</p>' +
        '<p style="font-size:13px;font-weight:700;color:#04001d;margin:4px 0;">Promozione esempio</p>' +
        '<p style="font-size:11px;color:rgba(4,0,29,.45);margin:5px 0 0;">Valida fino al 31/12/2026</p>' +
        '</div>';

    function findVar(name) {
        return vars.find(v => v.name === name) || null;
    }

    function promotionMarkerPattern(flags = 'gi') {
        return new RegExp('(?:@promotion\\b|\\{\\{\\s*promotion\\s*\\}\\})', flags);
    }

    function isPromotionVar(name) {
        return name === 'promotion';
    }

    function isBodyEditor(editorEl) {
        return editorEl?.dataset?.field === 'body_html';
    }

    function promotionBodyValue() {
        return document.getElementById('body_html')?.value || '';
    }

    function hasManualPromotionBlock() {
        return promotionMarkerPattern('i').test(promotionBodyValue());
    }

    function normalizePromotionMarkers(text, allowOne) {
        let seen = false;

        return String(text || '').replace(promotionMarkerPattern(), match => {
            if (!allowOne || seen) return '';
            seen = true;
            return match;
        });
    }

    function canInsertPromotionBlock(editorEl) {
        return hasPromotionEnabled() && isBodyEditor(editorEl) && !hasManualPromotionBlock();
    }

    function setCaretAtEnd(el) {
        if (!el) return;
        el.focus();

        const range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);

        const sel = window.getSelection();
        if (!sel) return;
        sel.removeAllRanges();
        sel.addRange(range);
    }

    function syncPromotionUi(hasPromo = hasPromotionEnabled()) {
        const manualBlock   = hasManualPromotionBlock();
        const promoBlock    = document.getElementById('preview-promotion-block');
        const ctaPreview    = document.getElementById('preview-cta-wrap');
        const promoVarGroup = document.getElementById('promotion-var-group');

        if (promoBlock)    promoBlock.style.display    = hasPromo && !manualBlock ? '' : 'none';
        if (ctaPreview)    ctaPreview.style.display    = hasPromo ? '' : 'none';
        if (promoVarGroup) promoVarGroup.style.display = hasPromo ? '' : 'none';

        document.querySelectorAll('.var-chip[data-var="promotion"]').forEach(chip => {
            const disabled = !hasPromo || manualBlock;
            chip.classList.toggle('is-disabled', disabled);
            chip.setAttribute('aria-disabled', disabled ? 'true' : 'false');
            chip.title = disabled && manualBlock
                ? 'Il blocco promozione è già presente nel modello'
                : '';
        });
    }

    // ─── CHIP ─────────────────────────────────────────────────────
    function createChip(varDef) {
        const span = document.createElement('span');
        span.className = 'var-chip-inline';
        span.dataset.var = varDef.name;
        span.contentEditable = 'false';
        span.textContent = varDef.label;
        return span;
    }

    // ─── SERIALIZE (editor → stringa @var con \n) ─────────────────
    function serialize(node) {
        let out = '';
        for (const child of node.childNodes) {
            if (child.nodeType === Node.TEXT_NODE) {
                out += child.textContent.replace(/​/g, '');
            } else if (child.nodeType === Node.ELEMENT_NODE) {
                if (child.classList.contains('var-chip-inline')) {
                    out += '@' + child.dataset.var;
                } else if (child.tagName === 'BR') {
                    out += '\n';
                } else {
                    out += serialize(child);
                }
            }
        }
        return out;
    }

    // ─── DESERIALIZE (stringa → editor con chip) ──────────────────
    function appendLines(parent, text) {
        const lines = text.split('\n');
        lines.forEach((line, i) => {
            if (i > 0) parent.appendChild(document.createElement('br'));
            if (line) parent.appendChild(document.createTextNode(line));
        });
    }

    function deserialize(editor, text) {
        editor.innerHTML = '';
        if (!text) return;
        // normalizza legacy {{ var }} → @var
        text = text.replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, '@$1');
        // normalizza <br> → \n e strappa HTML residuo
        text = text.replace(/<br\s*\/?>/gi, '\n');
        text = text.replace(/<[^>]*>/g, '');
        text = text.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&').replace(/&quot;/g, '"');

        const parts = text.split(/(@[a-zA-Z0-9_]+)/g);
        for (const part of parts) {
            if (!part) continue;
            const m = part.match(/^@([a-zA-Z0-9_]+)$/);
            if (m) {
                const vd = findVar(m[1]);
                if (vd) { editor.appendChild(createChip(vd)); continue; }
            }
            appendLines(editor, part);
        }
    }

    // ─── SYNC textarea nascosta ───────────────────────────────────
    function syncHidden(editorEl) {
        const hidden = document.getElementById(editorEl.dataset.field);
        if (!hidden) return;
        hidden.value = serialize(editorEl).replace(/^\n/, '');
        updatePreview();
    }

    // ─── PLACEHOLDER ──────────────────────────────────────────────
    function updatePlaceholder(editorEl) {
        const isEmpty = editorEl.textContent.replace(/​/g, '').trim() === ''
            && !editorEl.querySelector('.var-chip-inline');
        editorEl.toggleAttribute('data-empty', isEmpty);
    }

    // ─── AUTOCOMPLETE STATE ───────────────────────────────────────
    let acTarget = null, acQuery = '', acIdx = 0, acIsEditor = false;
    const acDrop = document.getElementById('var-autocomplete');

    function escHtml(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ─── PREVIEW ─────────────────────────────────────────────────
    function renderInlineVar(text) {
        return escHtml(text).replace(/@([a-zA-Z0-9_]+)/g, (_, n) => {
            const vd = findVar(n);
            return '<span class="var-token">' + (vd ? vd.label : '@' + n) + '</span>';
        });
    }

    function hasPromotionEnabled() {
        return document.querySelector('input[name="has_promotion"][type="radio"]:checked')?.value === '1';
    }

    function renderPreviewVariable(name, hasPromo, fallbackPrefix = '@', state = null) {
        if (isPromotionVar(name)) {
            if (!hasPromo || state?.promotionRendered) return '';
            if (state) state.promotionRendered = true;
            return PROMO_BLOCK_PLACEHOLDER;
        }

        const vd = findVar(name);
        return '<span class="var-token">' + (vd ? vd.label : fallbackPrefix + name) + '</span>';
    }

    function renderBodyForPreview(text, hasPromo = hasPromotionEnabled()) {
        if (!text || !text.trim()) return '';
        const state = { promotionRendered: false };
        if (/<[a-z]/i.test(text)) {
            // corpo HTML — gestisce {{ legacy }} e variabili @ inserite nell'editor
            return text
                .replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (_, n) => renderPreviewVariable(n, hasPromo, '', state))
                .replace(/@([a-zA-Z0-9_]+)/g, (_, n) => renderPreviewVariable(n, hasPromo, '@', state));
        }
        // testo semplice con @var, @promotion e \n
        return escHtml(text)
            .replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (_, n) => renderPreviewVariable(n, hasPromo, '', state))
            .replace(/@([a-zA-Z0-9_]+)/g, (_, n) => renderPreviewVariable(n, hasPromo, '@', state))
            .replace(/\n/g, '<br>');
    }

    function updatePreview() {
        const hasPromo = hasPromotionEnabled();
        const appName = document.getElementById('preview-app-name')?.dataset.value || 'R';
        const obj  = (document.getElementById('object')?.value || '').trim();
        const hdg  = (document.getElementById('heading')?.value || '').trim();
        const body = document.getElementById('body_html')?.value || '';
        const end  = (document.getElementById('ending')?.value || '').trim();
        const snd  = (document.getElementById('sender')?.value || '').trim() || appName;

        const pSubj    = document.getElementById('preview-subject');
        const pHdg     = document.getElementById('preview-heading');
        const pBody    = document.getElementById('preview-body');
        const pEnd     = document.getElementById('preview-ending');
        const pSnd     = document.getElementById('preview-sender');
        const pEndWrap = document.getElementById('preview-ending-wrap');

        syncPromotionUi(hasPromo);

        if (pSubj) {
            if (obj) { pSubj.innerHTML = renderInlineVar(obj); pSubj.classList.remove('is-placeholder'); }
            else { pSubj.textContent = pSubj.dataset.placeholder || ''; pSubj.classList.add('is-placeholder'); }
        }
        if (pHdg) {
            if (hdg) { pHdg.innerHTML = renderInlineVar(hdg); pHdg.classList.remove('is-placeholder'); }
            else { pHdg.textContent = pHdg.dataset.placeholder || ''; pHdg.classList.add('is-placeholder'); }
        }
        if (pBody) {
            const rendered = renderBodyForPreview(body, hasPromo);
            pBody.innerHTML = rendered || '<span class="preview-placeholder">' + escHtml(pBody.dataset.placeholder || '') + '</span>';
        }
        if (pEnd && pEndWrap) {
            if (end) { pEndWrap.style.display = ''; pEnd.innerHTML = renderInlineVar(end); }
            else { pEndWrap.style.display = 'none'; }
        }
        if (pSnd) pSnd.textContent = snd;
    }

    ['object', 'heading', 'sender'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updatePreview);
    });

    // ─── ANTEPRIMA IMMAGINI ──────────────────────────────────────
    function clearPreviewObjectUrl(input) {
        const objectUrl = input?.dataset?.previewObjectUrl;
        if (!objectUrl) return;
        URL.revokeObjectURL(objectUrl);
        delete input.dataset.previewObjectUrl;
        input.removeAttribute('data-preview-object-url');
    }

    function setPreviewImage(input, src) {
        const preview = document.getElementById(input?.dataset?.previewTarget || '');
        if (!preview) return;

        if (src) {
            preview.src = src;
            preview.hidden = false;
            return;
        }

        preview.removeAttribute('src');
        preview.hidden = true;
    }

    function updateImageInput(input) {
        if (!input) return;

        const field = input.closest('[data-image-upload]');
        const clearButton = field?.querySelector('[data-image-clear]');
        const status = field?.querySelector('[data-image-status]');
        const file = input.files && input.files.length ? input.files[0] : null;

        clearPreviewObjectUrl(input);

        if (file) {
            const objectUrl = URL.createObjectURL(file);
            input.dataset.previewObjectUrl = objectUrl;
            setPreviewImage(input, objectUrl);

            if (clearButton) clearButton.hidden = false;
            if (status) status.textContent = file.name;
            return;
        }

        setPreviewImage(input, input.dataset.existingSrc || '');
        if (clearButton) clearButton.hidden = true;
        if (status) status.textContent = status.dataset.defaultText || '';
    }

    function clearImageInput(input) {
        if (!input) return;

        clearPreviewObjectUrl(input);

        try {
            input.value = '';
        } catch (error) {
            // Some mobile browsers are conservative with file inputs; replacing the node is a reliable fallback.
        }

        if (input.files && input.files.length) {
            const clone = input.cloneNode(true);
            clone.removeAttribute('data-preview-object-url');
            input.replaceWith(clone);
            updateImageInput(clone);
            return;
        }

        updateImageInput(input);
    }

    document.querySelectorAll('[data-image-input]').forEach(updateImageInput);

    document.addEventListener('change', event => {
        if (event.target?.matches?.('[data-image-input]')) {
            updateImageInput(event.target);
        }
    });

    document.addEventListener('click', event => {
        const clearButton = event.target?.closest?.('[data-image-clear]');
        if (!clearButton) return;

        event.preventDefault();
        const input = clearButton.closest('[data-image-upload]')?.querySelector('[data-image-input]');
        clearImageInput(input);
    });

    window.addEventListener('beforeunload', () => {
        document.querySelectorAll('[data-image-input]').forEach(clearPreviewObjectUrl);
    });

    // ─── SELETTORE TIPO MODELLO ──────────────────────────────────
    (function () {
        const radios = document.querySelectorAll('input[name="has_promotion"][type="radio"]');
        radios.forEach(r => r.addEventListener('change', updatePreview));
        syncPromotionUi();
    })();

    // ─── AUTOCOMPLETE UI ─────────────────────────────────────────
    function showAc(anchorEl, query, isEditor) {
        const filtered = vars.filter(v =>
            (!query || v.name.includes(query.toLowerCase()) || v.label.toLowerCase().includes(query.toLowerCase()))
            && (!isPromotionVar(v.name) || canInsertPromotionBlock(anchorEl))
        );
        if (!filtered.length) { hideAc(); return; }
        acIdx = 0;
        acIsEditor = isEditor;
        acDrop.innerHTML = filtered.map((v, i) =>
            `<div class="var-autocomplete__item${i === 0 ? ' is-selected' : ''}" data-var="${v.name}">` +
            `<strong>@${v.name}</strong><small>${v.label}</small></div>`
        ).join('');
        acDrop.querySelectorAll('.var-autocomplete__item').forEach(item => {
            item.addEventListener('mousedown', e => {
                e.preventDefault();
                if (isEditor) insertChipFromAc(acTarget, item.dataset.var);
                else insertVarInInput(acTarget, acQuery, item.dataset.var);
            });
        });

        let rect;
        if (isEditor) {
            const sel = window.getSelection();
            if (sel && sel.rangeCount) rect = sel.getRangeAt(0).getBoundingClientRect();
        }
        if (!rect || rect.width === 0) rect = anchorEl.getBoundingClientRect();
        acDrop.style.top  = (rect.bottom + 4) + 'px';
        acDrop.style.left = rect.left + 'px';
        acDrop.style.display = 'block';
    }

    function hideAc() {
        if (acDrop) acDrop.style.display = 'none';
        acTarget = null; acIsEditor = false;
    }

    function moveAcSel(dir) {
        const items = acDrop.querySelectorAll('.var-autocomplete__item');
        if (!items.length) return;
        items[acIdx]?.classList.remove('is-selected');
        acIdx = (acIdx + dir + items.length) % items.length;
        items[acIdx]?.classList.add('is-selected');
        items[acIdx]?.scrollIntoView({ block: 'nearest' });
    }

    // ─── INSERIMENTO IN INPUT NORMALI ─────────────────────────────
    function getAtMatchInput(el) {
        const val = el.value;
        const pos = typeof el.selectionStart === 'number' ? el.selectionStart : val.length;
        const m = val.substring(0, pos).match(/@([a-zA-Z0-9_]*)$/);
        return m ? { query: m[1] } : null;
    }

    function insertVarInInput(el, query, varName) {
        if (!el) return;
        if (isPromotionVar(varName)) {
            hideAc();
            return;
        }

        const val = el.value;
        const pos = typeof el.selectionStart === 'number' ? el.selectionStart : val.length;
        const newBefore = val.substring(0, pos).replace(/@([a-zA-Z0-9_]*)$/, '@' + varName);
        el.value = newBefore + val.substring(pos);
        el.setSelectionRange(newBefore.length, newBefore.length);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        hideAc();
        el.focus();
    }

    function setupAcInput(el) {
        el.addEventListener('input', () => {
            const m = getAtMatchInput(el);
            if (m) { acTarget = el; acQuery = m.query; showAc(el, m.query, false); }
            else if (!acIsEditor) hideAc();
        });
        el.addEventListener('keydown', e => {
            if (acDrop.style.display === 'none') return;
            if (e.key === 'ArrowDown') { e.preventDefault(); moveAcSel(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); moveAcSel(-1); }
            else if (e.key === 'Enter') {
                if (!acIsEditor) {
                    e.preventDefault();
                    const sel = acDrop.querySelector('.var-autocomplete__item.is-selected');
                    if (sel) insertVarInInput(acTarget, acQuery, sel.dataset.var);
                }
            }
            else if (e.key === 'Escape') hideAc();
        });
        el.addEventListener('blur', () => setTimeout(() => { if (!acIsEditor) hideAc(); }, 150));
    }

    ['object', 'heading', 'sender', 'name'].forEach(id => {
        const el = document.getElementById(id);
        if (el) setupAcInput(el);
    });

    // ─── INSERIMENTO CHIP IN CONTENTEDITABLE ──────────────────────
    function getAtMatchInEditor() {
        const sel = window.getSelection();
        if (!sel || !sel.rangeCount) return null;
        const range = sel.getRangeAt(0);
        if (!range.collapsed) return null;
        const node = range.startContainer;
        if (node.nodeType !== Node.TEXT_NODE) return null;
        const before = node.textContent.substring(0, range.startOffset);
        const m = before.match(/@([a-zA-Z0-9_]*)$/);
        return m ? { query: m[1], node, offset: range.startOffset } : null;
    }

    function insertChipFromAc(editorEl, varName) {
        if (!editorEl) return;
        const vd = findVar(varName);
        if (!vd) return;
        if (isPromotionVar(varName) && !canInsertPromotionBlock(editorEl)) {
            hideAc();
            return;
        }

        const sel = window.getSelection();
        if (!sel || !sel.rangeCount) return;
        const range = sel.getRangeAt(0);
        const textNode = range.startContainer;
        if (textNode.nodeType !== Node.TEXT_NODE) return;

        const offset = range.startOffset;
        const text = textNode.textContent;
        const atMatch = text.substring(0, offset).match(/@([a-zA-Z0-9_]*)$/);
        if (!atMatch) return;

        const atStart = offset - atMatch[0].length;
        const before  = text.substring(0, atStart);
        const after   = text.substring(offset);
        const parent  = textNode.parentNode;
        const chip    = createChip(vd);
        const afterNode = document.createTextNode(after !== '' ? after : '​');

        parent.insertBefore(afterNode, textNode.nextSibling);
        parent.insertBefore(chip, afterNode);
        if (before) { textNode.textContent = before; }
        else { parent.removeChild(textNode); }

        const nr = document.createRange();
        nr.setStart(afterNode, 0);
        nr.collapse(true);
        sel.removeAllRanges();
        sel.addRange(nr);

        syncHidden(editorEl);
        updatePlaceholder(editorEl);
        hideAc();
    }

    function insertChipAtCursor(editorEl, vd) {
        if (isPromotionVar(vd.name) && !canInsertPromotionBlock(editorEl)) return;

        editorEl.focus();
        const sel = window.getSelection();
        if (!sel || !sel.rangeCount) {
            editorEl.appendChild(createChip(vd));
            editorEl.appendChild(document.createTextNode('​'));
            syncHidden(editorEl);
            return;
        }
        const range = sel.getRangeAt(0);
        range.deleteContents();
        const chip      = createChip(vd);
        const afterNode = document.createTextNode('​');
        range.insertNode(afterNode);
        range.insertNode(chip);
        const nr = document.createRange();
        nr.setStartAfter(afterNode);
        nr.collapse(true);
        sel.removeAllRanges();
        sel.addRange(nr);
        syncHidden(editorEl);
        updatePlaceholder(editorEl);
    }

    // ─── SETUP EDITOR ────────────────────────────────────────────
    function setupEditor(editorEl) {
        const hidden = document.getElementById(editorEl.dataset.field);
        if (hidden && hidden.value) deserialize(editorEl, hidden.value);
        updatePlaceholder(editorEl);

        editorEl.addEventListener('keydown', e => {
            // navigazione autocomplete
            if (acDrop.style.display !== 'none' && acIsEditor) {
                if (e.key === 'ArrowDown') { e.preventDefault(); moveAcSel(1); return; }
                if (e.key === 'ArrowUp')   { e.preventDefault(); moveAcSel(-1); return; }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const sel = acDrop.querySelector('.var-autocomplete__item.is-selected');
                    if (sel) insertChipFromAc(editorEl, sel.dataset.var);
                    return;
                }
                if (e.key === 'Escape') { hideAc(); return; }
            }

            // Enter → <br> (evita che il browser crei <div>)
            if (e.key === 'Enter') {
                e.preventDefault();
                const sel = window.getSelection();
                if (!sel || !sel.rangeCount) return;
                const range = sel.getRangeAt(0);
                range.deleteContents();
                const br       = document.createElement('br');
                const spacer   = document.createTextNode('​');
                range.insertNode(spacer);
                range.insertNode(br);
                const nr = document.createRange();
                nr.setStartAfter(spacer);
                nr.collapse(true);
                sel.removeAllRanges();
                sel.addRange(nr);
                syncHidden(editorEl);
                return;
            }

            // Backspace su chip
            if (e.key === 'Backspace') {
                const sel = window.getSelection();
                if (!sel || !sel.rangeCount) return;
                const range = sel.getRangeAt(0);
                if (!range.collapsed) return;
                let prev;
                const node = range.startContainer;
                const offset = range.startOffset;
                if (node.nodeType === Node.TEXT_NODE && offset === 0) {
                    prev = node.previousSibling;
                } else if (node.nodeType === Node.ELEMENT_NODE && offset > 0) {
                    prev = node.childNodes[offset - 1];
                }
                if (prev && prev.classList?.contains('var-chip-inline')) {
                    e.preventDefault();
                    prev.remove();
                    syncHidden(editorEl);
                    updatePlaceholder(editorEl);
                }
            }
        });

        editorEl.addEventListener('input', () => {
            syncHidden(editorEl);
            updatePlaceholder(editorEl);
            const m = getAtMatchInEditor();
            if (m) { acTarget = editorEl; acQuery = m.query; showAc(editorEl, m.query, true); }
            else if (acIsEditor) hideAc();
        });

        editorEl.addEventListener('paste', e => {
            e.preventDefault();
            const text = e.clipboardData?.getData('text/plain') || '';
            if (!text) return;
            const sel = window.getSelection();
            if (!sel || !sel.rangeCount) return;
            const range = sel.getRangeAt(0);
            range.deleteContents();
            const frag  = document.createDocumentFragment();
            const parts = text.split(/(@[a-zA-Z0-9_]+)/g);
            let pastedPromotionBlock = hasManualPromotionBlock();

            parts.forEach(part => {
                const m = part.match(/^@([a-zA-Z0-9_]+)$/);
                if (m) {
                    const vd = findVar(m[1]);
                    if (vd && isPromotionVar(vd.name)) {
                        if (!hasPromotionEnabled() || !isBodyEditor(editorEl) || pastedPromotionBlock) return;
                        pastedPromotionBlock = true;
                    }
                    if (vd) { frag.appendChild(createChip(vd)); return; }
                }
                part.split('\n').forEach((line, i) => {
                    if (i > 0) frag.appendChild(document.createElement('br'));
                    if (line) frag.appendChild(document.createTextNode(line));
                });
            });
            range.insertNode(frag);
            range.collapse(false);
            sel.removeAllRanges();
            sel.addRange(range);
            syncHidden(editorEl);
            updatePlaceholder(editorEl);
        });

        editorEl.addEventListener('blur', () => {
            syncHidden(editorEl);
            setTimeout(() => { if (acIsEditor) hideAc(); }, 150);
        });
    }

    document.querySelectorAll('.var-editor[data-field]').forEach(setupEditor);

    // ─── CLICK SU CHIP NELLA LISTA VARIABILI ─────────────────────
    let lastFocusedEditor = null;
    document.querySelectorAll('.var-editor[data-field]').forEach(ed => {
        ed.addEventListener('focus', () => { lastFocusedEditor = ed; });
    });

    document.querySelectorAll('.var-chip[data-var]').forEach(chip => {
        chip.addEventListener('mousedown', e => { e.preventDefault(); }); // non perdere il focus
        chip.addEventListener('click', () => {
            const vd = findVar(chip.dataset.var);
            if (!vd) return;

            if (isPromotionVar(vd.name)) {
                const bodyEditor = document.getElementById('editor_body_html');
                if (!canInsertPromotionBlock(bodyEditor)) return;

                if (lastFocusedEditor !== bodyEditor) setCaretAtEnd(bodyEditor);
                insertChipAtCursor(bodyEditor, vd);
                return;
            }

            // Se c'è un editor focalizzato (o l'ultimo era un editor)
            if (lastFocusedEditor) {
                insertChipAtCursor(lastFocusedEditor, vd);
                return;
            }

            // Altrimenti input testuale
            const active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA') && active.closest('.mail-model-form')) {
                const val = active.value;
                const pos = active.selectionStart ?? val.length;
                const ins = '@' + vd.name;
                active.value = val.substring(0, pos) + ins + val.substring(pos);
                active.setSelectionRange(pos + ins.length, pos + ins.length);
                active.dispatchEvent(new Event('input', { bubbles: true }));
                active.focus();
            }
        });
    });

    // Chiude dropdown al click fuori
    document.addEventListener('click', e => {
        if (!acDrop.contains(e.target) && !e.target.closest('.var-editor') && e.target !== acTarget) hideAc();
    });

    // Sync prima del submit
    document.querySelector('.mail-model-form').closest('form')?.addEventListener('submit', () => {
        document.querySelectorAll('.var-editor[data-field]').forEach(syncHidden);

        const allowPromotion = hasPromotionEnabled();
        const body = document.getElementById('body_html');
        if (body) body.value = normalizePromotionMarkers(body.value, allowPromotion);

        ['object', 'heading', 'ending', 'sender'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = normalizePromotionMarkers(el.value, false);
        });
    });

    // Prima render
    updatePreview();
})();
</script>
@endverbatim
