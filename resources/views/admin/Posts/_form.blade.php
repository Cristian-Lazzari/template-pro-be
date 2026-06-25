@php
    $post        = $post ?? new \App\Models\Post();
    $isEdit      = $post->exists;

    $titleValue      = old('title',       $post->title ?? '');
    $pathValue       = old('path',        $post->path  ?? 1);
    $descValue       = old('description', $post->description ?? '');
    $hashtagValue    = old('hashtag',     $post->hashtag ?? '');
    $linkValue       = old('link',        $post->link ?? '');
    $linkLabelValue  = old('link_label',  $post->link_label ?? '');
    $promoValue      = old('promo',       $post->promo ?? false);

    $coverUrl = null;
    if ($isEdit && $post->image) {
        $img = ltrim($post->image, '/');
        $coverUrl = (str_starts_with($img, 'public/storage/') || str_starts_with($img, 'storage/'))
            ? asset($img)
            : asset('public/storage/' . $img);
    }
@endphp

@include('admin.Marketing.partials.form-style')

<style>
    .post-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 2.1fr) minmax(270px, 1fr);
        gap: 18px;
        align-items: start;
        width: 100%;
        min-width: 0;
    }

    .post-form-main,
    .post-form-sidebar {
        display: grid;
        gap: 16px;
        min-width: 0;
    }

    .post-form-sidebar {
        position: sticky;
        top: 24px;
    }

    /* Campi form */
    .post-form-shell input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]),
    .post-form-shell select {
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

    .post-form-shell textarea {
        color: var(--c3);
        background: rgba(216, 221, 232, 0.06);
        border: 1px solid rgba(216, 221, 232, 0.16);
        border-radius: 12px;
        padding: 12px 14px;
        resize: vertical;
        min-height: 160px;
    }

    .post-form-shell input::placeholder,
    .post-form-shell textarea::placeholder {
        color: rgba(216, 221, 232, 0.42);
    }

    .post-form-shell input:not([type="file"]):focus,
    .post-form-shell select:focus,
    .post-form-shell textarea:focus,
    .post-form-shell input:not([type="file"]):hover,
    .post-form-shell select:hover,
    .post-form-shell textarea:hover {
        opacity: 1;
        border-color: rgba(14, 183, 146, 0.55);
        background: rgba(216, 221, 232, 0.08);
        outline: none;
    }

    /* ── Upload card copertina ─────────────────────────────────── */
    .post-upload-card {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        border-radius: 16px;
        overflow: hidden;
        cursor: pointer;
        transition: border-color .2s, background .2s;
    }

    .post-upload-card--cover {
        aspect-ratio: 16/9;
        border: 2px dashed rgba(216, 221, 232, 0.2);
        background: rgba(216, 221, 232, 0.03);
    }

    .post-upload-card--cover:hover {
        border-color: rgba(14, 183, 146, 0.5);
        background: rgba(14, 183, 146, 0.04);
    }

    .post-upload-card--cover:focus-visible {
        outline: 2px solid rgba(14, 183, 146, 0.6);
        outline-offset: 3px;
    }

    .post-upload-card__placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-align: center;
        padding: 24px;
        pointer-events: none;
        user-select: none;
    }

    .post-upload-card__ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 18px;
        border: 1px solid rgba(14, 183, 146, 0.22);
        background: rgba(14, 183, 146, 0.09);
        color: rgba(142, 246, 219, 0.82);
        font-size: 24px;
        margin-bottom: 4px;
        transition: background .2s, border-color .2s;
    }

    .post-upload-card--cover:hover .post-upload-card__ico {
        background: rgba(14, 183, 146, 0.15);
        border-color: rgba(14, 183, 146, 0.38);
    }

    .post-upload-card__placeholder strong {
        color: var(--c3);
        font-size: var(--fs-300);
        font-weight: 900;
        line-height: 1.2;
    }

    .post-upload-card__placeholder small {
        color: rgba(216, 221, 232, 0.48);
        font-size: var(--fs-200);
        font-weight: 700;
    }

    .post-upload-card__hint {
        display: block;
        color: rgba(216, 221, 232, 0.28);
        font-size: var(--fs-100);
        font-weight: 700;
        margin-top: 2px;
    }

    .post-upload-card__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: opacity .2s;
    }

    .post-upload-card--cover:hover .post-upload-card__img {
        opacity: .88;
    }

    .post-upload-card__remove {
        position: absolute;
        top: 10px;
        right: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: rgba(9, 3, 51, 0.72);
        color: rgba(255, 255, 255, 0.92);
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        transition: background .15s;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 2;
    }

    .post-upload-card__remove:hover {
        background: rgba(206, 59, 59, 0.88);
    }

    /* ── Gallery grid & cards ──────────────────────────────────── */
    .post-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
        gap: 10px;
    }

    #gallery-new-previews {
        display: contents;
    }

    .post-gallery-card {
        position: relative;
        aspect-ratio: 1;
        border-radius: 12px;
        overflow: hidden;
    }

    .post-gallery-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Foto esistenti */
    .post-gallery-card--existing {
        border: 1px solid rgba(216, 221, 232, 0.12);
        cursor: default;
    }

    .post-gallery-card__del {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        justify-content: flex-end;
        padding: 7px;
        opacity: 0;
        background: linear-gradient(to top, rgba(9, 3, 51, 0.65) 0%, transparent 55%);
        transition: opacity .18s;
        cursor: pointer;
    }

    .post-gallery-card--existing:hover .post-gallery-card__del {
        opacity: 1;
    }

    .post-gallery-card__del-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(9, 3, 51, 0.75);
        color: rgba(255, 255, 255, 0.88);
        font-size: 13px;
        transition: background .15s;
    }

    .post-gallery-card--existing:hover .post-gallery-card__del-icon {
        background: rgba(200, 48, 48, 0.88);
        color: #fff;
    }

    /* Stato marcato per eliminazione */
    .post-gallery-card--existing.is-marked {
        outline: 2.5px solid rgba(210, 50, 50, 0.75);
        outline-offset: -2px;
    }

    .post-gallery-card--existing.is-marked::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(160, 20, 20, 0.32);
        pointer-events: none;
        z-index: 1;
    }

    .post-gallery-card--existing.is-marked .post-gallery-card__del {
        opacity: 1;
        z-index: 2;
    }

    .post-gallery-card--existing.is-marked .post-gallery-card__del-icon {
        background: rgba(200, 48, 48, 0.92);
        color: #fff;
    }

    /* Foto nuove (preview JS) */
    .post-gallery-card--new {
        border: 1.5px solid rgba(14, 183, 146, 0.28);
    }

    .post-gallery-card--new::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(14, 183, 146, 0.06);
        pointer-events: none;
    }

    .post-gallery-card--new:hover .post-gallery-card__del {
        opacity: 1;
        z-index: 2;
    }

    .post-gallery-card--new:hover .post-gallery-card__del-icon {
        background: rgba(200, 48, 48, 0.88);
        color: #fff;
    }

    /* Card aggiungi */
    .post-gallery-card--add {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border: 2px dashed rgba(216, 221, 232, 0.18);
        background: rgba(216, 221, 232, 0.03);
        color: rgba(216, 221, 232, 0.4);
        cursor: pointer;
        transition: border-color .18s, background .18s, color .18s;
        user-select: none;
    }

    .post-gallery-card--add:hover,
    .post-gallery-card--add:focus-visible {
        border-color: rgba(14, 183, 146, 0.45);
        background: rgba(14, 183, 146, 0.05);
        color: rgba(142, 246, 219, 0.82);
        outline: none;
    }

    .post-gallery-add__ico {
        font-size: 22px;
        line-height: 1;
    }

    .post-gallery-card--add small {
        font-size: var(--fs-100);
        font-weight: 800;
    }

    /* Info espandibile (es. campo link) */
    .form-info-expand {
        margin-top: 6px;
        font-size: var(--fs-100, 12px);
    }

    .form-info-expand summary {
        cursor: pointer;
        color: rgba(216, 221, 232, 0.5);
        user-select: none;
        list-style: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .form-info-expand summary::-webkit-details-marker { display: none; }

    .form-info-expand summary::before {
        content: '▸';
        display: inline-block;
        transition: transform .15s;
        font-size: 10px;
    }

    .form-info-expand[open] summary::before {
        transform: rotate(90deg);
    }

    .form-info-expand__body {
        margin-top: 8px;
        padding: 10px 12px;
        border-radius: 8px;
        background: rgba(216, 221, 232, 0.04);
        border: 1px solid rgba(216, 221, 232, 0.1);
        color: rgba(216, 221, 232, 0.6);
        line-height: 1.55;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-info-expand__body p { margin: 0; }

    .form-info-expand__body strong { color: rgba(216, 221, 232, 0.85); }

    .form-info-expand__body code {
        font-family: monospace;
        font-size: 0.92em;
        padding: 1px 5px;
        border-radius: 4px;
        background: rgba(14, 183, 146, 0.12);
        color: rgba(142, 246, 219, 0.9);
    }

    /* Sidebar preview */
    .post-preview-cover-img {
        width: 100%;
        aspect-ratio: 16/9;
        border-radius: 12px;
        object-fit: cover;
        display: block;
    }

    .post-preview-cover-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        aspect-ratio: 16/9;
        border-radius: 12px;
        border: 1px dashed rgba(216, 221, 232, 0.18);
        color: rgba(216, 221, 232, 0.22);
        font-size: 28px;
    }

    .post-preview-cover-img[hidden],
    .post-preview-cover-placeholder[hidden] { display: none !important; }

    .post-preview-title {
        margin: 10px 0 6px;
        color: var(--c3);
        font-weight: 900;
        font-size: var(--fs-400);
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .post-preview-title.is-placeholder {
        color: rgba(216, 221, 232, 0.3);
        font-weight: 600;
        font-style: italic;
    }

    .post-path-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: var(--fs-100);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid rgba(14, 183, 146, 0.3);
        background: rgba(14, 183, 146, 0.1);
        color: rgba(142, 246, 219, 0.92);
    }

    /* Promo star toggle */
    .post-promo-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1.5px solid rgba(216, 221, 232, 0.14);
        background: rgba(216, 221, 232, 0.04);
        cursor: pointer;
        user-select: none;
        transition: border-color .15s, background .15s;
    }

    .post-promo-wrap:hover {
        border-color: rgba(216, 221, 232, 0.26);
        background: rgba(216, 221, 232, 0.07);
    }

    .post-promo-wrap.is-active {
        border-color: rgba(251, 191, 36, 0.38);
        background: rgba(251, 191, 36, 0.06);
    }

    .post-promo-wrap .post-promo-text {
        display: grid;
        gap: 2px;
        flex: 1;
    }

    .post-promo-wrap .post-promo-text strong {
        color: var(--c3);
        font-size: var(--fs-300);
        font-weight: 900;
    }

    .post-promo-wrap .post-promo-text small {
        color: rgba(216, 221, 232, 0.5);
        font-size: var(--fs-100);
        font-weight: 700;
    }

    .post-promo-star {
        flex-shrink: 0;
        color: rgba(216, 221, 232, 0.28);
        transition: color .15s;
        font-size: 22px;
        line-height: 1;
    }

    .post-promo-wrap.is-active .post-promo-star {
        color: rgba(251, 191, 36, 0.9);
    }

    @media (max-width: 1100px) {
        .post-form-grid { grid-template-columns: 1fr; }
        .post-form-sidebar { position: static; }
    }

    @media (max-width: 600px) {
        .post-gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        }
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        {{ __('admin.marketing.mailer.check_fields') }}
    </div>
@endif

<form class="creation marketing-form-shell post-form-shell mt-4"
      action="{{ $action }}"
      enctype="multipart/form-data"
      method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="post-form-grid">

        {{-- ── Colonna principale ───────────────────────────────────── --}}
        <div class="post-form-main">

            {{-- Sezione: informazioni base --}}
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="card-text" /></span>
                        {{ __('admin.marketing.mailer.model_info') }}
                    </h3>
                </div>

                <div>
                    <label class="label_c" for="title">
                        <x-icon name="type" />
                        {{ __('admin.Titolo') }}
                    </label>
                    <p>
                        <input type="text"
                               id="title"
                               name="title"
                               value="{{ $titleValue }}"
                               placeholder="{{ __('admin.posts.title_placeholder') }}">
                    </p>
                    @error('title') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="split">
                    <div>
                        <label class="label_c" for="path">
                            <x-icon name="view-list" />
                            {{ __('admin.Pagina_di_destinazione') }}
                        </label>
                        <p>
                            <select name="path" id="path">
                                <option value="1" @selected($pathValue == 1)>{{ __('admin.News') }}</option>
                                <option value="2" @selected($pathValue == 2)>{{ __('admin.Storia') }}</option>
                            </select>
                        </p>
                        @error('path') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="link">
                            <x-icon name="link-45deg" />
                            Link
                        </label>
                        <p>
                            <input type="text"
                                   id="link"
                                   name="link"
                                   value="{{ $linkValue }}"
                                   placeholder="{{ __('admin.posts.link_placeholder') }}">
                        </p>
                        <details class="form-info-expand">
                            <summary>Come funziona il link?</summary>
                            <div class="form-info-expand__body">
                                <p>Per un <strong>sito internet</strong> inserisci l'indirizzo completo, ad esempio: <code>https://www.esempio.it</code></p>
                                <p>Per aprire direttamente il <strong>telefono</strong> con un numero preimpostato, scrivi <code>tel:</code> prima del numero, ad esempio: <code>tel:+39012345678</code></p>
                            </div>
                        </details>
                        @error('link') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="link_label">
                            <x-icon name="tag" />
                            {{ __('admin.posts.link_label_label') }}
                        </label>
                        <p>
                            <input type="text"
                                   id="link_label"
                                   name="link_label"
                                   value="{{ $linkLabelValue }}"
                                   maxlength="60"
                                   placeholder="{{ __('admin.posts.link_label_placeholder') }}">
                        </p>
                        @error('link_label') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Sezione: contenuto --}}
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="body-text" /></span>
                        {{ __('admin.Descrizione') }}
                    </h3>
                </div>

                <div>
                    <label class="label_c" for="description">
                        <x-icon name="body-text" />
                        {{ __('admin.Descrizione') }}
                        <button class="my_btn_4" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#desc-info"
                                aria-expanded="false"
                                aria-controls="desc-info">
                            <x-icon name="info-circle-fill" />
                        </button>
                    </label>
                    <div class="collapse" id="desc-info">
                        <p class="menu-dashboard__copy mb-2">{{ __('admin.post_info') }}</p>
                    </div>
                    <p>
                        <textarea name="description"
                                  id="description"
                                  rows="10"
                                  placeholder="{{ __('admin.Descrizione') }}...">{{ $descValue }}</textarea>
                    </p>
                    @error('description') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label_c" for="hashtag">
                        <x-icon name="hash" />
                        {{ __('admin.Hashtag_') }}
                    </label>
                    <p>
                        <textarea name="hashtag"
                                  id="hashtag"
                                  rows="3"
                                  placeholder="#esempio #ristorante">{{ $hashtagValue }}</textarea>
                    </p>
                </div>
            </section>

            {{-- Sezione: immagini --}}
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="images" /></span>
                        Immagini
                    </h3>
                </div>

                {{-- Copertina --}}
                <div>
                    <p class="label_c">
                        <x-icon name="image-fill" />
                        {{ __('admin.Immagine') }}
                    </p>

                    <div class="post-upload-card post-upload-card--cover"
                         id="cover-card"
                         role="button"
                         tabindex="0"
                         aria-label="Carica immagine copertina">

                        <div class="post-upload-card__placeholder"
                             id="cover-placeholder"
                             @if ($coverUrl) hidden @endif>
                            <span class="post-upload-card__ico"><x-icon name="image" /></span>
                            <strong>Copertina post</strong>
                            <small>Clicca o trascina qui</small>
                            <span class="post-upload-card__hint">PNG · JPG · WEBP &nbsp;·&nbsp; max 1 MB</span>
                        </div>

                        <img class="post-upload-card__img"
                             id="cover-img"
                             @if ($coverUrl) src="{{ $coverUrl }}" @endif
                             alt="Copertina"
                             @unless ($coverUrl) hidden @endunless>

                        <button class="post-upload-card__remove"
                                id="cover-remove"
                                type="button"
                                @unless ($coverUrl) hidden @endunless
                                aria-label="Rimuovi copertina">
                            <x-icon name="x-circle-fill" />
                        </button>

                        <input type="file"
                               id="file-input"
                               name="image"
                               accept="image/*"
                               hidden>
                    </div>
                    @error('image') <p class="error mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Galleria --}}
                <div>
                    <p class="label_c">
                        <x-icon name="images" />
                        {{ __('admin.posts.gallery_label') }}
                    </p>

                    <div class="post-gallery-grid" id="gallery-grid">

                        {{-- Foto esistenti in modifica --}}
                        @if ($isEdit && $post->images->count())
                            @foreach ($post->images as $img)
                                @php
                                    $imgPath = ltrim($img->image, '/');
                                    $imgUrl  = (str_starts_with($imgPath, 'public/storage/') || str_starts_with($imgPath, 'storage/'))
                                        ? asset($imgPath)
                                        : asset('public/storage/' . $imgPath);
                                @endphp
                                <div class="post-gallery-card post-gallery-card--existing"
                                     data-id="{{ $img->id }}">
                                    <img src="{{ $imgUrl }}" alt="">
                                    <label class="post-gallery-card__del"
                                           title="{{ __('admin.posts.gallery_delete') }}">
                                        <input type="checkbox"
                                               name="delete_images[]"
                                               value="{{ $img->id }}"
                                               class="post-gallery-del-cb"
                                               hidden>
                                        <span class="post-gallery-card__del-icon">
                                            <x-icon name="trash3-fill" />
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        @endif

                        {{-- Anteprime nuove foto (iniettate via JS) --}}
                        <div id="gallery-new-previews"></div>

                        {{-- Card aggiungi --}}
                        <div class="post-gallery-card post-gallery-card--add"
                             id="gallery-add-card"
                             role="button"
                             tabindex="0"
                             aria-label="Aggiungi foto galleria">
                            <span class="post-gallery-add__ico"><x-icon name="plus-lg" /></span>
                            <small>Aggiungi</small>
                        </div>

                    </div>

                    <p class="menu-dashboard__copy mt-1">{{ __('admin.posts.gallery_hint') }}</p>
                    @error('images.*') <p class="error">{{ $message }}</p> @enderror
                </div>
            </section>

        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────── --}}
        <aside class="post-form-sidebar">

            {{-- Post in evidenza (solo in modifica) --}}
            @if ($isEdit)
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon"><x-icon name="star-fill" /></span>
                            {{ __('admin.Post_in_evidenza') }}
                        </h3>
                    </div>

                    <label class="post-promo-wrap {{ $promoValue ? 'is-active' : '' }}"
                           id="promo-toggle-label"
                           for="promo-input">
                        <span class="post-promo-star">
                            <i class="bi bi-star-fill"></i>
                        </span>
                        <span class="post-promo-text">
                            <strong>{{ __('admin.Post_in_evidenza') }}</strong>
                            <small>Evidenziato nella pagina del sito</small>
                        </span>
                        <input type="checkbox"
                               id="promo-input"
                               name="promo"
                               style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;"
                               @if ($promoValue) checked @endif>
                    </label>
                </section>
            @endif

            {{-- Anteprima live --}}
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="eye-fill" /></span>
                        Anteprima
                    </h3>
                </div>

                <img class="post-preview-cover-img"
                     id="preview-cover"
                     @if ($coverUrl) src="{{ $coverUrl }}" @endif
                     alt="Anteprima copertina"
                     @unless ($coverUrl) hidden @endunless>

                <div class="post-preview-cover-placeholder"
                     id="preview-cover-placeholder"
                     @if ($coverUrl) hidden @endif>
                    <x-icon name="image" />
                </div>

                <p class="post-preview-title {{ $titleValue ? '' : 'is-placeholder' }}"
                   id="preview-title">
                    {{ $titleValue ?: 'Titolo del post...' }}
                </p>

                <span class="post-path-badge" id="preview-path-badge">
                    {{ $pathValue == 2 ? __('admin.Storia') : __('admin.News') }}
                </span>
            </section>

            {{-- Suggerimenti --}}
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon"><x-icon name="lightbulb-fill" /></span>
                        Suggerimenti
                    </h3>
                </div>
                <div class="marketing-form-preview__note">
                    <p>Campi segnati con * sono facoltativi. La descrizione supporta testo multiriga e verrà pubblicata nella pagina del sito.</p>
                </div>
            </section>

        </aside>
    </div>

    {{-- Barra azioni --}}
    <section class="order-detail__section mt-2">
        <div class="marketing-form-actions">
            <button class="order-detail__contact marketing-form-action--primary" type="submit">
                <x-icon name="check2-circle" />
                <span>{{ $isEdit ? __('admin.Modifica_Post') : __('admin.Crea_Post') }}</span>
            </button>
            <a class="order-detail__contact marketing-form-action--cancel"
               href="{{ route('admin.posts.index') }}">
                <x-icon name="arrow-left-circle-fill" />
                <span>{{ __('admin.Annulla') }}</span>
            </a>
        </div>
        <p class="menu-dashboard__copy mt-2">* {{ __('admin.Campi_facoltativi') }}</p>
    </section>

</form>

<script>
(function () {
    let coverObjectUrl = null;
    const galleryObjectUrls = [];

    window.addEventListener('beforeunload', function () {
        if (coverObjectUrl) URL.revokeObjectURL(coverObjectUrl);
        galleryObjectUrls.forEach(function (u) { URL.revokeObjectURL(u); });
    });

    // ── Copertina ─────────────────────────────────────────────────
    const coverCard        = document.getElementById('cover-card');
    const coverInput       = document.getElementById('file-input');
    const coverImg         = document.getElementById('cover-img');
    const coverPlaceholder = document.getElementById('cover-placeholder');
    const coverRemove      = document.getElementById('cover-remove');
    const sidePreview      = document.getElementById('preview-cover');
    const sidePlaceholder  = document.getElementById('preview-cover-placeholder');
    const existingCover    = {!! json_encode($coverUrl) !!};

    function showCover(src) {
        coverImg.src = src;
        coverImg.hidden = false;
        coverPlaceholder.hidden = true;
        coverRemove.hidden = false;
        if (sidePreview)     { sidePreview.src = src; sidePreview.hidden = false; }
        if (sidePlaceholder) { sidePlaceholder.hidden = true; }
    }

    function clearCover() {
        if (existingCover) {
            showCover(existingCover);
        } else {
            coverImg.removeAttribute('src');
            coverImg.hidden = true;
            coverPlaceholder.hidden = false;
            coverRemove.hidden = true;
            if (sidePreview)     { sidePreview.removeAttribute('src'); sidePreview.hidden = true; }
            if (sidePlaceholder) { sidePlaceholder.hidden = false; }
        }
    }

    function resetCoverInput() {
        const clone = coverInput.cloneNode(true);
        coverInput.parentNode.replaceChild(clone, coverInput);
        clone.addEventListener('change', onCoverChange);
    }

    function onCoverChange() {
        const file = this.files && this.files[0];
        if (!file) return;
        if (coverObjectUrl) URL.revokeObjectURL(coverObjectUrl);
        coverObjectUrl = URL.createObjectURL(file);
        showCover(coverObjectUrl);
    }

    if (coverCard && coverInput) {
        coverInput.addEventListener('change', onCoverChange);

        coverCard.addEventListener('click', function (e) {
            if (!e.target.closest('#cover-remove')) {
                document.getElementById('file-input')?.click();
            }
        });
        coverCard.addEventListener('keydown', function (e) {
            if ((e.key === 'Enter' || e.key === ' ') && !e.target.closest('#cover-remove')) {
                e.preventDefault();
                document.getElementById('file-input')?.click();
            }
        });

        if (coverRemove) {
            coverRemove.addEventListener('click', function (e) {
                e.stopPropagation();
                if (coverObjectUrl) { URL.revokeObjectURL(coverObjectUrl); coverObjectUrl = null; }
                resetCoverInput();
                clearCover();
            });
        }
    }

    // Drag & drop sulla cover card
    if (coverCard) {
        coverCard.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.style.borderColor = 'rgba(14,183,146,.6)';
        });
        coverCard.addEventListener('dragleave', function () {
            this.style.borderColor = '';
        });
        coverCard.addEventListener('drop', function (e) {
            e.preventDefault();
            this.style.borderColor = '';
            const file = e.dataTransfer?.files?.[0];
            if (!file || !file.type.startsWith('image/')) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            const inp = document.getElementById('file-input');
            if (inp) { inp.files = dt.files; inp.dispatchEvent(new Event('change')); }
        });
    }

    // ── Galleria ─────────────────────────────────────────────────
    const galleryAddCard     = document.getElementById('gallery-add-card');
    const galleryNewPreviews = document.getElementById('gallery-new-previews');

    var trashSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>';

    function addNewGalleryItem(file) {
        var objectUrl = URL.createObjectURL(file);
        galleryObjectUrls.push(objectUrl);

        var card = document.createElement('div');
        card.className = 'post-gallery-card post-gallery-card--new';

        // Input nascosto con il file: viene inviato con il form
        var formInput = document.createElement('input');
        formInput.type = 'file';
        formInput.name = 'images[]';
        formInput.hidden = true;
        var dt = new DataTransfer();
        dt.items.add(file);
        formInput.files = dt.files;
        card.appendChild(formInput);

        var img = document.createElement('img');
        img.src = objectUrl;
        img.alt = file.name;
        card.appendChild(img);

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'post-gallery-card__del';
        delBtn.setAttribute('aria-label', 'Rimuovi foto');
        delBtn.innerHTML = '<span class="post-gallery-card__del-icon">' + trashSvg + '</span>';
        card.appendChild(delBtn);

        delBtn.addEventListener('click', function () {
            var idx = galleryObjectUrls.indexOf(objectUrl);
            if (idx !== -1) { URL.revokeObjectURL(objectUrl); galleryObjectUrls.splice(idx, 1); }
            card.remove();
        });

        galleryNewPreviews.appendChild(card);
    }

    if (galleryAddCard) {
        galleryAddCard.addEventListener('click', function () {
            var picker = document.createElement('input');
            picker.type = 'file';
            picker.accept = 'image/*';
            picker.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (file) { addNewGalleryItem(file); }
            });
            picker.click();
        });
        galleryAddCard.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); galleryAddCard.click(); }
        });
    }

    // Esistenti: toggle marcatura eliminazione
    document.querySelectorAll('.post-gallery-del-cb').forEach(function (cb) {
        const card = cb.closest('.post-gallery-card--existing');
        cb.addEventListener('change', function () {
            card?.classList.toggle('is-marked', this.checked);
        });
        // click sull'icona cestino spunta/deseleziona il checkbox
        const delLabel = cb.closest('.post-gallery-card__del');
        if (delLabel) {
            delLabel.addEventListener('click', function (e) {
                e.preventDefault();
                cb.checked = !cb.checked;
                cb.dispatchEvent(new Event('change'));
            });
        }
    });

    // ── Sidebar: titolo + path ────────────────────────────────────
    const titleInput   = document.getElementById('title');
    const pathSelect   = document.getElementById('path');
    const previewTitle = document.getElementById('preview-title');
    const previewBadge = document.getElementById('preview-path-badge');

    const pathLabels = {
        '1': '{{ __('admin.News') }}',
        '2': '{{ __('admin.Storia') }}',
    };

    if (titleInput && previewTitle) {
        titleInput.addEventListener('input', function () {
            const val = this.value.trim();
            if (val) {
                previewTitle.textContent = val;
                previewTitle.classList.remove('is-placeholder');
            } else {
                previewTitle.textContent = 'Titolo del post...';
                previewTitle.classList.add('is-placeholder');
            }
        });
    }

    if (pathSelect && previewBadge) {
        pathSelect.addEventListener('change', function () {
            previewBadge.textContent = pathLabels[this.value] || this.value;
        });
    }

    // ── Promo toggle ─────────────────────────────────────────────
    const promoLabel = document.getElementById('promo-toggle-label');
    const promoInput = document.getElementById('promo-input');
    if (promoLabel && promoInput) {
        promoInput.addEventListener('change', function () {
            promoLabel.classList.toggle('is-active', this.checked);
        });
    }
})();
</script>
