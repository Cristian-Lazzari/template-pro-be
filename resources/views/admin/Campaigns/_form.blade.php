@php
    $scheduledValue = old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i'));
    $selectedPromotionIds = collect(old('promotions', $campaign->exists ? $campaign->promotions->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $primaryActionLabel = $method === 'POST' ? 'Crea e programma' : 'Salva e programma';
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        Controlla i campi evidenziati prima di salvare.
    </div>
@endif

@if (in_array($campaign->status, ['completed', 'sent'], true))
    <div class="alert alert-warning">
        Questa campagna risulta completata: puoi solo archiviarla.
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
                Dati campagna
            </h3>
        </div>

        <div>
            <label class="label_c" for="name">
                <i class="bi bi-type"></i>
                Nome
            </label>
            <p>
                <input value="{{ old('name', $campaign->name) }}" type="text" name="name" id="name" placeholder="Nome campagna">
            </p>
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                Audience e modello
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="segment">
                    <i class="bi bi-people-fill"></i>
                    Segmento
                </label>
                <p>
                    <select name="segment" id="segment">
                        <option value="">Nessuno</option>
                        @foreach ($segments as $value => $label)
                            <option value="{{ $value }}" @selected(old('segment', $campaign->segment) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('segment') <p class="error">{{ $message }}</p> @enderror
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
                            <option value="{{ $mailModel->id }}" @selected((string) old('model_id', $campaign->model_id) === (string) $mailModel->id)>
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

        <div class="split">
            <div>
                <label class="label_c" for="scheduled_at">
                    <i class="bi bi-calendar-plus"></i>
                    Programmazione
                </label>
                <p>
                    <input value="{{ $scheduledValue }}" type="datetime-local" name="scheduled_at" id="scheduled_at">
                </p>
                @error('scheduled_at') <p class="error">{{ $message }}</p> @enderror
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

    <section class="order-detail__section mt-4">
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <button class="order-detail__contact" type="submit" name="submit_action" value="activate">
                <i class="bi bi-check2-circle"></i>
                <span>{{ $primaryActionLabel }}</span>
            </button>
            <button class="order-detail__contact" type="submit" name="submit_action" value="draft">
                <i class="bi bi-clock-history"></i>
                <span>Completa più tardi</span>
            </button>
        </div>
        <p class="menu-dashboard__copy mt-3">
            Crea e programma prepara subito i destinatari. Le email partiranno automaticamente all’orario programmato tramite scheduler.
        </p>
        @error('submit_action') <p class="error">{{ $message }}</p> @enderror
    </section>
</form>
