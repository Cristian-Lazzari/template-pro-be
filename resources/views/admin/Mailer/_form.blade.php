@php
    $isEdit = $model->exists;
    $bodyHtmlValue = old('body_html', $model->body_html ?: $model->body);
    $bodyTextValue = old('body_text', $model->body_text);
    $endingValue = old('ending', $model->ending);
    $statusValue = old('status', $model->status ?: 'draft');
    $objectValue = old('object', $model->object);
    $headingValue = old('heading', $model->heading);
    $senderValue = old('sender', $model->sender);
    $previewHeading = $headingValue ?: 'Titolo della promozione';
    $previewObject = $objectValue ?: 'Oggetto della mail';
    $previewBody = trim((string) $bodyHtmlValue) !== ''
        ? $bodyHtmlValue
        : '<p>Ciao {{ customer_first_name }}, abbiamo preparato una promozione per te.</p>';
    $previewEnding = $endingValue ?: 'A presto';

    $variableGroups = [
        'Cliente' => [
            'customer_name',
            'customer_first_name',
            'customer_last_name',
            'customer_email',
            'customer_phone',
        ],
        'Promozione' => [
            'promotion_name',
            'promotion_discount',
            'promotion_discount_label',
            'promotion_type_discount',
            'promotion_type_discount_label',
            'promotion_expiring_at',
            'promotion_cta',
        ],
        'Target' => [
            'product_name',
            'menu_name',
            'category_name',
            'post_title',
        ],
        'Campagna' => [
            'campaign_name',
            'tracking_click_url',
        ],
    ];
@endphp

<style>
    .mail-model-form {
        display: grid;
        gap: 18px;
    }

    .mail-model-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
        gap: 18px;
        align-items: start;
    }

    .mail-model-variable-list {
        display: grid;
        gap: 14px;
    }

    .mail-model-variable-group {
        display: grid;
        gap: 8px;
    }

    .mail-model-variable-group strong {
        color: var(--c3);
        font-size: var(--fs-300);
    }

    .mail-model-variable-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .mail-model-variable-chip {
        display: inline-flex;
        max-width: 100%;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.14);
        background: rgba(216, 221, 232, 0.06);
        color: rgba(216, 221, 232, 0.88);
        font-size: var(--fs-200);
        overflow-wrap: anywhere;
    }

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
    .mail-model-preview p {
        overflow-wrap: anywhere;
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

    @media (max-width: 860px) {
        .mail-model-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger">
        Controlla i campi evidenziati prima di salvare.
    </div>
@endif

<form class="creation mail-model-form mt-4" action="{{ $action }}" enctype="multipart/form-data" method="POST">
    @csrf
    @if ($isEdit)
        <input type="hidden" name="id" value="{{ $model->id }}">
    @endif
    <input type="hidden" name="type" value="marketing">
    <input type="hidden" name="channel" value="email">

    <div class="mail-model-grid">
        <div class="mail-model-form">
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="card-text" />
                        </span>
                        Informazioni modello
                    </h3>
                </div>

                <div class="split">
                    <div>
                        <label class="label_c" for="name">
                            <x-icon name="type" />
                            Nome modello
                        </label>
                        <p>
                            <input value="{{ old('name', $model->name) }}" type="text" name="name" id="name" placeholder="Es. Promo clienti inattivi">
                        </p>
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="status">
                            <x-icon name="toggle-on" />
                            Stato
                        </label>
                        <p>
                            <select name="status" id="status">
                                <option value="draft" @selected($statusValue === 'draft')>Bozza</option>
                                <option value="active" @selected($statusValue === 'active')>Attivo</option>
                                <option value="archived" @selected($statusValue === 'archived')>Archiviato</option>
                            </select>
                        </p>
                        @error('status') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="split">
                    <div>
                        <label class="label_c" for="object">
                            <x-icon name="envelope-fill" />
                            Oggetto email
                        </label>
                        <p>
                            <input value="{{ $objectValue }}" type="text" name="object" id="object" placeholder="Es. Una promozione pensata per te">
                        </p>
                        @error('object') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="sender">
                            <x-icon name="person-lines-fill" />
                            Mittente
                        </label>
                        <p>
                            <input value="{{ $senderValue }}" type="text" name="sender" id="sender" placeholder="Es. Il team del ristorante">
                        </p>
                        @error('sender') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="file-earmark-richtext-fill" />
                        </span>
                        Contenuto email
                    </h3>
                </div>

                <div>
                    <label class="label_c" for="heading">
                        <x-icon name="type-h1" />
                        Titolo principale
                    </label>
                    <p>
                        <input value="{{ $headingValue }}" type="text" name="heading" id="heading" placeholder="Es. Ti aspetta una sorpresa">
                    </p>
                    @error('heading') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label_c" for="body_html">
                        <x-icon name="body-text" />
                        Corpo HTML
                    </label>
                    <p>
                        <textarea name="body_html" id="body_html" rows="12" placeholder="Scrivi il contenuto. Puoi usare @{{ customer_name }} e gli altri placeholder.">{{ $bodyHtmlValue }}</textarea>
                    </p>
                    @error('body_html') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label_c" for="body_text">
                        <x-icon name="text-left" />
                        Versione testo
                    </label>
                    <p>
                        <textarea name="body_text" id="body_text" rows="6" placeholder="Versione solo testo opzionale.">{{ $bodyTextValue }}</textarea>
                    </p>
                    @error('body_text') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label_c" for="ending">
                        <x-icon name="chat-quote-fill" />
                        Chiusura
                    </label>
                    <p>
                        <textarea name="ending" id="ending" rows="4" placeholder="Es. A presto, il tuo ristorante.">{{ $endingValue }}</textarea>
                    </p>
                    @error('ending') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="split">
                    <div>
                        <label class="label_c" for="img_1">
                            <x-icon name="image-fill" />
                            Immagine superiore
                        </label>
                        <p><input type="file" id="img_1" name="img_1"></p>
                        @if ($model->img_1)
                            <p class="menu-dashboard__copy">Immagine presente: {{ basename($model->img_1) }}</p>
                        @endif
                        @error('img_1') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label_c" for="img_2">
                            <x-icon name="image-fill" />
                            Immagine inferiore
                        </label>
                        <p><input type="file" id="img_2" name="img_2"></p>
                        @if ($model->img_2)
                            <p class="menu-dashboard__copy">Immagine presente: {{ basename($model->img_2) }}</p>
                        @endif
                        @error('img_2') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        </div>

        <aside class="mail-model-form">
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="braces" />
                        </span>
                        Variabili disponibili
                    </h3>
                </div>

                <p class="menu-dashboard__copy">
                    Le variabili verranno sostituite automaticamente quando il modello viene usato in una campagna o automazione.
                </p>

                <div class="mail-model-variable-list">
                    @foreach ($variableGroups as $group => $items)
                        <div class="mail-model-variable-group">
                            <strong>{{ $group }}</strong>
                            <div class="mail-model-variable-chips">
                                @foreach ($items as $variable)
                                    <code class="mail-model-variable-chip">&#123;&#123; {{ $variable }} &#125;&#125;</code>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="eye-fill" />
                        </span>
                        Anteprima struttura
                    </h3>
                </div>

                <div class="mail-model-preview">
                    <div class="mail-model-preview__inner">
                        <span class="mail-model-preview__logo">R</span>
                        <p class="mail-model-preview__subject">{{ $previewObject }}</p>
                        <h4>{{ $previewHeading }}</h4>
                        <div class="mail-model-preview__body">
                            {!! $previewBody !!}
                        </div>
                        <a class="mail-model-preview__cta" href="#">Scopri la promozione</a>
                        <p>{{ $previewEnding }}</p>
                        <div class="mail-model-preview__footer">
                            Footer e tracking saranno aggiunti nella mail reale.
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <section class="order-detail__section">
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <button class="order-detail__contact" type="submit">
                <x-icon name="check2-circle" />
                <span>{{ $isEdit ? 'Salva modello' : 'Crea modello' }}</span>
            </button>
            <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.index') }}">
                <x-icon name="arrow-left-circle-fill" />
                <span>Annulla</span>
            </a>
        </div>
        <p class="menu-dashboard__copy mt-3">
            Il modello resta collegabile a campagne e automazioni. L'invio email non parte da questa pagina.
        </p>
    </section>
</form>
