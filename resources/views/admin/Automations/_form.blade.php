@php
    $selectedPromotionIds = collect(old('promotions', $automation->exists ? $automation->promotions->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $cooldownValue = old('metadata.cooldown_days', data_get($automation->metadata, 'cooldown_days'));
    $enabledFromValue = old('metadata.enabled_from', data_get($automation->metadata, 'enabled_from'));
    $enabledUntilValue = old('metadata.enabled_until', data_get($automation->metadata, 'enabled_until'));
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        Controlla i campi evidenziati prima di salvare.
    </div>
@endif

<form class="creation mt-4" action="{{ $action }}" method="POST">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-card-text"></i>
                </span>
                Dati automazione
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="name">
                    <i class="bi bi-type"></i>
                    Nome
                </label>
                <p>
                    <input value="{{ old('name', $automation->name) }}" type="text" name="name" id="name" placeholder="Nome automazione">
                </p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="status">
                    <i class="bi bi-toggle-on"></i>
                    Status
                </label>
                <p>
                    <select name="status" id="status">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $automation->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('status') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                Trigger e modello
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="trigger">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Trigger
                </label>
                <p>
                    <select name="trigger" id="trigger">
                        <option value="">Nessun trigger</option>
                        @foreach ($triggers as $value => $label)
                            <option value="{{ $value }}" @selected(old('trigger', $automation->trigger) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('trigger') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="model_id">
                    <i class="bi bi-envelope-fill"></i>
                    Modello mail
                </label>
                <p>
                    <select name="model_id" id="model_id">
                        <option value="">Nessun modello</option>
                        @foreach ($mailModels as $mailModel)
                            <option value="{{ $mailModel->id }}" @selected((string) old('model_id', $automation->model_id) === (string) $mailModel->id)>
                                {{ $mailModel->name }}
                                @if ($mailModel->object)
                                    - {{ $mailModel->object }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </p>
                @error('model_id') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-hourglass-split"></i>
                </span>
                Regole esecuzione
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="metadata_cooldown_days">
                    <i class="bi bi-hourglass-split"></i>
                    Cooldown giorni
                </label>
                <p>
                    <input value="{{ $cooldownValue }}" type="number" min="0" step="1" name="metadata[cooldown_days]" id="metadata_cooldown_days">
                </p>
                @error('metadata.cooldown_days') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="metadata_enabled_from">
                    <i class="bi bi-calendar-plus"></i>
                    Abilitata da
                </label>
                <p>
                    <input value="{{ $enabledFromValue }}" type="date" name="metadata[enabled_from]" id="metadata_enabled_from">
                </p>
                @error('metadata.enabled_from') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="metadata_enabled_until">
                    <i class="bi bi-calendar-x"></i>
                    Abilitata fino a
                </label>
                <p>
                    <input value="{{ $enabledUntilValue }}" type="date" name="metadata[enabled_until]" id="metadata_enabled_until">
                </p>
                @error('metadata.enabled_until') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div></div>
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                Promozioni collegate
            </h3>
        </div>

        <label class="label_c" for="promotions">
            <i class="bi bi-megaphone-fill"></i>
            Promozioni
        </label>
        <p>
            <select name="promotions[]" id="promotions" multiple size="8">
                @foreach ($promotions as $promotion)
                    <option value="{{ $promotion->id }}" @selected(in_array((string) $promotion->id, $selectedPromotionIds, true))>
                        {{ $promotion->name }} - {{ $promotion->slug }}
                    </option>
                @endforeach
            </select>
        </p>
        @error('promotions') <p class="error">{{ $message }}</p> @enderror
        @error('promotions.*') <p class="error">{{ $message }}</p> @enderror
    </section>

    <div class="d-flex justify-content-end mt-4 mb-5">
        <button class="my_btn_2 w-auto" type="submit">
            <i class="bi bi-check2-circle"></i>
            {{ $submitLabel }}
        </button>
    </div>
</form>
