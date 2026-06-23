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

    /* Campi form – stile consistente col mail model */
    .post-form-shell input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
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

    .post-form-shell input[type="file"] {
        min-height: 44px;
        height: auto;
        padding: 10px;
        cursor: pointer;
        background: rgba(216, 221, 232, 0.06);
        border: 1px solid rgba(216, 221, 232, 0.16);
        border-radius: 12px;
        color: var(--c3);
    }

    .post-form-shell input::placeholder,
    .post-form-shell textarea::placeholder {
        color: rgba(216, 221, 232, 0.42);
    }

    .post-form-shell input:focus,
    .post-form-shell select:focus,
    .post-form-shell textarea:focus,
    .post-form-shell input:hover,
    .post-form-shell select:hover,
    .post-form-shell textarea:hover {
        opacity: 1;
        border-color: rgba(14, 183, 146, 0.55);
        background: rgba(216, 221, 232, 0.08);
        outline: none;
    }

    /* Anteprima copertina */
    .post-cover-preview {
        width: 100%;
        aspect-ratio: 16/9;
        border-radius: 12px;
        object-fit: cover;
        display: block;
    }

    .post-cover-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        aspect-ratio: 16/9;
        border-radius: 12px;
        border: 1px dashed rgba(216, 221, 232, 0.2);
        color: rgba(216, 221, 232, 0.25);
        font-size: 30px;
    }

    .post-cover-placeholder[hidden],
    .post-cover-preview[hidden] { display: none !important; }

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

    /* Galleria foto in edit */
    .post-gallery-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 4px;
    }

    .post-gallery-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .post-gallery-item img {
        width: 84px;
        height: 84px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid rgba(216, 221, 232, 0.14);
        display: block;
    }

    .post-gallery-item label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: var(--fs-100);
        color: rgba(216, 221, 232, 0.66);
        cursor: pointer;
    }

    .post-gallery-item input[type="checkbox"]:checked + span {
        color: rgba(255, 120, 120, 0.9);
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
        color: rgba(216, 221, 232, 0.3);
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

        {{-- ── Colonna principale: campi form ──────────────────────── --}}
        <div class="post-form-main">

            {{-- Sezione: informazioni base --}}
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="card-text" />
                        </span>
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
                            {{ __('admin.Link_IG_') }}
                        </label>
                        <p>
                            <input type="text"
                                   id="link"
                                   name="link"
                                   value="{{ $linkValue }}"
                                   placeholder="{{ __('admin.posts.link_placeholder') }}">
                        </p>
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
                        <span class="order-detail__section-icon">
                            <x-icon name="body-text" />
                        </span>
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

            {{-- Sezione: media --}}
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="image-fill" />
                        </span>
                        {{ __('admin.Immagine') }}
                    </h3>
                </div>

                <div>
                    <label class="label_c" for="file-input">
                        <x-icon name="file-earmark-image" />
                        {{ __('admin.Immagine') }}
                    </label>
                    <p>
                        <input type="file"
                               id="file-input"
                               name="image"
                               accept="image/*"
                               data-cover-input>
                    </p>
                    @error('image') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label_c" for="images-input">
                        <x-icon name="images" />
                        {{ __('admin.posts.gallery_label') }}
                    </label>

                    @if ($isEdit && $post->images->count())
                        <div class="post-gallery-grid mb-2">
                            @foreach ($post->images as $img)
                                <div class="post-gallery-item">
                                    <img src="{{ asset('public/storage/' . $img->image) }}"
                                         alt="">
                                    <label>
                                        <input type="checkbox"
                                               name="delete_images[]"
                                               value="{{ $img->id }}">
                                        <span>{{ __('admin.posts.gallery_delete') }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <p>
                        <input type="file"
                               id="images-input"
                               name="images[]"
                               accept="image/*"
                               multiple>
                    </p>
                    <small class="menu-dashboard__copy">{{ __('admin.posts.gallery_hint') }}</small>
                    @error('images.*') <p class="error">{{ $message }}</p> @enderror
                </div>
            </section>

        </div>

        {{-- ── Colonna destra: sidebar ──────────────────────────────── --}}
        <aside class="post-form-sidebar">

            {{-- Post in evidenza (solo in modifica) --}}
            @if ($isEdit)
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="star-fill" />
                            </span>
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
                        <span class="order-detail__section-icon">
                            <x-icon name="eye-fill" />
                        </span>
                        Anteprima
                    </h3>
                </div>

                <img class="post-cover-preview"
                     id="preview-cover"
                     @if ($coverUrl) src="{{ $coverUrl }}" @endif
                     alt="Anteprima copertina"
                     @unless ($coverUrl) hidden @endunless>

                <div class="post-cover-placeholder"
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
                        <span class="order-detail__section-icon">
                            <x-icon name="lightbulb-fill" />
                        </span>
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
    const titleInput   = document.getElementById('title');
    const pathSelect   = document.getElementById('path');
    const coverInput   = document.querySelector('[data-cover-input]');
    const previewCover = document.getElementById('preview-cover');
    const previewPlaceholder = document.getElementById('preview-cover-placeholder');
    const previewTitle = document.getElementById('preview-title');
    const previewBadge = document.getElementById('preview-path-badge');
    const promoLabel   = document.getElementById('promo-toggle-label');
    const promoInput   = document.getElementById('promo-input');

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

    if (coverInput && previewCover && previewPlaceholder) {
        let objectUrl = null;

        coverInput.addEventListener('change', function () {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
            const file = this.files && this.files[0];
            if (file) {
                objectUrl = URL.createObjectURL(file);
                previewCover.src = objectUrl;
                previewCover.hidden = false;
                previewPlaceholder.hidden = true;
            } else {
                const existing = {!! json_encode($coverUrl) !!};
                if (existing) {
                    previewCover.src = existing;
                    previewCover.hidden = false;
                    previewPlaceholder.hidden = true;
                } else {
                    previewCover.removeAttribute('src');
                    previewCover.hidden = true;
                    previewPlaceholder.hidden = false;
                }
            }
        });

        window.addEventListener('beforeunload', function () {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
        });
    }

    if (promoLabel && promoInput) {
        promoInput.addEventListener('change', function () {
            promoLabel.classList.toggle('is-active', this.checked);
        });
    }
})();
</script>
