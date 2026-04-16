@extends('layouts.base')

@section('contents')
<a onclick="history.back()" class="btn btn-outline-light my-5">
    <x-icon name="arrow-90deg-left" />
</a>

@php
    $locale = app()->getLocale() ?: 'it';
    $imageUrl = $ingredient->icon ? \Illuminate\Support\Facades\Storage::url($ingredient->icon) : null;
    $detailTone = $ingredient->option ? 'warning' : 'active';
    $detailLabel = $ingredient->option ? 'Opzione' : 'Ingrediente';
    $priceLabel = \App\Support\Currency::formatCents($ingredient->price ?? 0);
    $translations = $ingredient->translations->sortBy('lang')->values();
    $initial = mb_strtoupper(mb_substr((string) $ingredient->name, 0, 1)) ?: 'I';

    $formatDateTime = static function ($value, string $fallback = '-') use ($locale): string {
        if (!$value) {
            return $fallback;
        }

        return \Carbon\Carbon::parse($value)
            ->locale($locale)
            ->translatedFormat('H:i - l j F Y');
    };

    $statItems = [
        ['label' => 'Prezzo', 'value' => $priceLabel, 'helper' => 'costo come extra'],
        ['label' => 'Prodotti', 'value' => $products->count(), 'helper' => 'attualmente collegati'],
    ];
@endphp

<style>
    .ingredient-detail-page {
        width: 100%;
        display: grid;
        gap: 22px;
    }

    .ingredient-detail__grid,
    .ingredient-detail__stats,
    .ingredient-detail__chip-list,
    .ingredient-detail__translation-list,
    .ingredient-detail__product-list {
        display: grid;
        gap: 18px;
    }

    .ingredient-detail__grid {
        grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr);
    }

    .ingredient-detail__media-card,
    .ingredient-detail__stat-card,
    .ingredient-detail__translation-item,
    .ingredient-detail__product-item {
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
    }

    .ingredient-detail__media-card {
        display: grid;
        gap: 18px;
    }

    .ingredient-detail__media {
        min-height: 260px;
        overflow: hidden;
        border-radius: 18px;
        background:
            radial-gradient(circle at top, rgba(255, 211, 122, 0.24), transparent 58%),
            rgba(255, 255, 255, 0.03);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ingredient-detail__media img {
        width: 100%;
        max-height: 320px;
        object-fit: cover;
        display: block;
    }

    .ingredient-detail__placeholder {
        width: 92px;
        height: 92px;
        border-radius: 28px;
        background: rgba(255, 211, 122, 0.16);
        color: #ffd37a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: var(--fs-700);
        font-weight: 700;
    }

    .ingredient-detail__stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ingredient-detail__stat-card,
    .ingredient-detail__translation-item,
    .ingredient-detail__product-item {
        padding: 16px 18px;
    }

    .ingredient-detail__stat-card span,
    .ingredient-detail__translation-item span,
    .ingredient-detail__product-meta span {
        color: rgba(216, 221, 232, 0.7);
        font-size: var(--fs-200);
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .ingredient-detail__stat-card strong,
    .ingredient-detail__translation-item strong,
    .ingredient-detail__product-copy strong {
        display: block;
        margin-top: 8px;
        color: var(--c3);
    }

    .ingredient-detail__stat-card small,
    .ingredient-detail__product-copy p {
        margin: 8px 0 0;
        color: rgba(216, 221, 232, 0.82);
        line-height: 1.6;
    }

    .ingredient-detail__chip-list {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }

    .ingredient-detail__chip {
        min-width: 0;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--c3);
    }

    .ingredient-detail__chip img {
        width: 34px;
        height: 34px;
        object-fit: contain;
        flex: 0 0 auto;
    }

    .ingredient-detail__translation-list,
    .ingredient-detail__product-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ingredient-detail__product-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .ingredient-detail__product-copy {
        min-width: 0;
    }

    .ingredient-detail__product-copy p {
        margin-top: 6px;
    }

    .ingredient-detail__empty {
        margin: 0;
        color: rgba(216, 221, 232, 0.82);
        line-height: 1.7;
    }

    @media (max-width: 992px) {
        .ingredient-detail__grid,
        .ingredient-detail__translation-list,
        .ingredient-detail__product-list {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .ingredient-detail__stats,
        .ingredient-detail__chip-list {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ingredient-detail-page">
    <article class="order-detail order-detail--{{ $detailTone }}">
        <header class="order-detail__header">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--{{ $detailTone }}">
                    @if ($ingredient->option)
                        <x-icon name="bookmark-star-fill" />
                    @else
                        <x-icon name="box-seam" />
                    @endif
                </span>

                <strong>{{ $detailLabel }}</strong>
            </div>

            <div class="order-detail__contacts">
                <a href="{{ route('admin.ingredients.index') }}" class="order-detail__contact">
                    <x-icon name="grid-1x2-fill" />
                    <span>{{ __('admin.Vedi_tutti') }}</span>
                </a>

                <a href="{{ route('admin.ingredients.edit', $ingredient) }}" class="order-detail__contact">
                    <x-icon name="pencil-square" />
                    <span>{{ __('admin.Modifica') }}</span>
                </a>
            </div>
        </header>

        <div class="order-detail__body">
            <section class="order-detail__summary">
                <div class="order-detail__meta">
                    <p class="order-detail__code">#I{{ $ingredient->id }}</p>
                    <p class="order-detail__time">{{ $detailLabel }}</p>
                    <p class="order-detail__date">{{ $priceLabel }}</p>
                </div>

                <div class="order-detail__customer">{{ $ingredient->name }}</div>
            </section>

            <div class="ingredient-detail__grid">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="image" />
                            </span>
                            Panoramica
                        </h3>
                    </div>

                    <div class="ingredient-detail__media">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $ingredient->name }}">
                        @else
                            <span class="ingredient-detail__placeholder">{{ $initial }}</span>
                        @endif
                    </div>

                    <div class="ingredient-detail__stats">
                        @foreach ($statItems as $item)
                            <article class="ingredient-detail__stat-card">
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ $item['value'] }}</strong>
                                <small>{{ $item['helper'] }}</small>
                            </article>
                        @endforeach
                    </div>

                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="translate" />
                            </span>
                            Traduzioni
                        </h3>
                    </div>

                    @if ($translations->isNotEmpty())
                        <div class="ingredient-detail__translation-list">
                            @foreach ($translations as $translation)
                                <article class="ingredient-detail__translation-item">
                                    <span>{{ strtoupper($translation->lang) }}</span>
                                    <strong>{{ $translation->name ?: '-' }}</strong>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="ingredient-detail__empty">Non ci sono traduzioni salvate per questo ingrediente.</p>
                    @endif
                </section>
            </div>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="exclamation-triangle-fill" />
                        </span>
                        Allergeni
                    </h3>
                </div>

                @if ($ingredient->allergens->isNotEmpty())
                    <div class="ingredient-detail__chip-list">
                        @foreach ($ingredient->allergens as $allergen)
                            <div class="ingredient-detail__chip">
                                @if ($allergen->img)
                                    <img src="{{ $allergen->img }}" alt="{{ $allergen->name }}">
                                @endif
                                <span>{{ $allergen->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="ingredient-detail__empty">Nessun allergene associato.</p>
                @endif
            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="grid-1x2-fill" />
                        </span>
                        Categorie abbinate
                    </h3>
                </div>

                @if ($categories->isNotEmpty())
                    <div class="ingredient-detail__chip-list">
                        @foreach ($categories as $category)
                            <div class="ingredient-detail__chip">
                                <x-icon name="tag-fill" />
                                <span>{{ $category->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="ingredient-detail__empty">Questo ingrediente non e ancora disponibile per nessuna categoria.</p>
                @endif
            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="card-checklist" />
                        </span>
                        Prodotti collegati
                    </h3>
                </div>

                @if ($products->isNotEmpty())
                    <div class="ingredient-detail__product-list">
                        @foreach ($products as $product)
                            <article class="ingredient-detail__product-item">
                                <div class="ingredient-detail__product-copy">
                                    <strong>{{ $product->name }}</strong>
                                    <p>{{ optional($product->category)->name ?: 'Senza categoria' }}</p>
                                </div>

                                <div class="ingredient-detail__product-meta">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="order-detail__contact">
                                        <x-icon name="arrow-up-right-circle-fill" />
                                        <span>{{ __('admin.Modifica') }}</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="ingredient-detail__empty">Nessun prodotto collegato a questo ingrediente per ora.</p>
                @endif
            </section>

            <footer class="order-detail__footer">
                <div class="order-detail__footer-row">
                    <span>Creato il</span>
                    <strong>{{ $formatDateTime($ingredient->created_at) }}</strong>
                </div>

                <div class="order-detail__footer-row">
                    <span>Ultimo aggiornamento</span>
                    <strong>{{ $formatDateTime($ingredient->updated_at) }}</strong>
                </div>
            </footer>
        </div>
    </article>
</div>
@endsection
