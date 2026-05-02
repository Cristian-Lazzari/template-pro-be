@php
    $reusableValue = old('metadata.reusable', data_get($promotion->metadata, 'reusable', false));
    $scheduleValue = old('schedule_at', $promotion->schedule_at?->format('Y-m-d\TH:i'));
    $expiringValue = old('expiring_at', $promotion->expiring_at?->format('Y-m-d\TH:i'));
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
                Dati principali
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="name">
                    <i class="bi bi-type"></i>
                    Nome
                </label>
                <p>
                    <input value="{{ old('name', $promotion->name) }}" type="text" name="name" id="name" placeholder="Nome promozione">
                </p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="slug">
                    <i class="bi bi-link-45deg"></i>
                    Slug
                </label>
                <p>
                    <input value="{{ old('slug', $promotion->slug) }}" type="text" name="slug" id="slug" placeholder="Generato dal nome se vuoto">
                </p>
                @error('slug') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="status">
                    <i class="bi bi-toggle-on"></i>
                    Status
                </label>
                <p>
                    <select name="status" id="status">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $promotion->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('status') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="case_use">
                    <i class="bi bi-bullseye"></i>
                    Caso d'uso
                </label>
                <p>
                    <select name="case_use" id="case_use">
                        <option value="">Nessuno</option>
                        @foreach ($caseUses as $value => $label)
                            <option value="{{ $value }}" @selected(old('case_use', $promotion->case_use) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('case_use') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-percent"></i>
                </span>
                Regola promozionale
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="type_discount">
                    <i class="bi bi-percent"></i>
                    Tipo sconto
                </label>
                <p>
                    <select name="type_discount" id="type_discount">
                        <option value="">Nessuno</option>
                        @foreach ($discountTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('type_discount', $promotion->type_discount) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('type_discount') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="discount">
                    <i class="bi bi-cash-coin"></i>
                    Sconto
                </label>
                <p>
                    <input value="{{ old('discount', $promotion->discount) }}" type="number" step="0.01" min="0" name="discount" id="discount">
                </p>
                @error('discount') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="minimum_pretest">
                    <i class="bi bi-bag-check"></i>
                    Minimo
                </label>
                <p>
                    <input value="{{ old('minimum_pretest', $promotion->minimum_pretest) }}" type="number" step="0.01" min="0" name="minimum_pretest" id="minimum_pretest">
                </p>
                @error('minimum_pretest') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="cta">
                    <i class="bi bi-cursor-fill"></i>
                    CTA
                </label>
                <p>
                    <input value="{{ old('cta', $promotion->cta) }}" type="text" name="cta" id="cta" placeholder="Testo call to action">
                </p>
                @error('cta') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-calendar2-week-fill"></i>
                </span>
                Validita e metadata
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="schedule_at">
                    <i class="bi bi-calendar-plus"></i>
                    Programmazione
                </label>
                <p>
                    <input value="{{ $scheduleValue }}" type="datetime-local" name="schedule_at" id="schedule_at">
                </p>
                @error('schedule_at') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="expiring_at">
                    <i class="bi bi-calendar-x"></i>
                    Scadenza
                </label>
                <p>
                    <input value="{{ $expiringValue }}" type="datetime-local" name="expiring_at" id="expiring_at">
                </p>
                @error('expiring_at') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="split">
            <div>
                <div class="check_c">
                    <input type="hidden" name="permanent" value="0">
                    <label for="permanent">
                        <input type="checkbox" name="permanent" id="permanent" value="1" @checked(old('permanent', $promotion->permanent))>
                        Permanente
                    </label>
                </div>
                @error('permanent') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <div class="check_c">
                    <input type="hidden" name="metadata[reusable]" value="0">
                    <label for="metadata_reusable">
                        <input type="checkbox" name="metadata[reusable]" id="metadata_reusable" value="1" @checked(filter_var($reusableValue, FILTER_VALIDATE_BOOLEAN))>
                        Riusabile
                    </label>
                </div>
                @error('metadata.reusable') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="d-flex justify-content-end mt-4 mb-5">
        <button class="my_btn_2 w-auto" type="submit">
            <i class="bi bi-check2-circle"></i>
            {{ $submitLabel }}
        </button>
    </div>
</form>
