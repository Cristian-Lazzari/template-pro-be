@php
    $selectedPromotionIds = collect(old('promotions', $automation->exists ? $automation->promotions->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $cooldownValue = old('metadata.cooldown_days', data_get($automation->metadata, 'cooldown_days'));
    $enabledFromValue = old('metadata.enabled_from', data_get($automation->metadata, 'enabled_from'));
    $enabledUntilValue = old('metadata.enabled_until', data_get($automation->metadata, 'enabled_until'));
    $primaryActionLabel = $method === 'POST'
        ? __('admin.marketing.automations.create_activate')
        : __('admin.marketing.automations.save_activate');
    $cancelUrl = $automation->exists ? route('admin.automations.show', $automation) : route('admin.automations.index');
    $selectedMailModelId = (string) old('model_id', $automation->model_id);
    $selectedTrigger = old('trigger', $automation->trigger);
    $previewMailModel = collect($mailModels)->first(fn ($mailModel) => (string) $mailModel->id === $selectedMailModelId) ?: $automation->model;
    $previewPromotions = collect($promotions)
        ->filter(fn ($promotion) => in_array((string) $promotion->id, $selectedPromotionIds, true))
        ->values();
@endphp

@include('admin.Marketing.partials.form-style')

@if ($errors->any())
    <div class="alert alert-danger">
        {{ __('admin.marketing.automations.check_fields') }}
    </div>
@endif

<form class="creation marketing-form-shell mt-4" action="{{ $action }}" method="POST">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="marketing-form-grid">
        <div class="marketing-form-main">
    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-card-text"></i>
                </span>
                {{ __('admin.marketing.automations.information') }}
            </h3>
        </div>

        <div>
            <label class="label_c" for="name">
                <i class="bi bi-type"></i>
                {{ __('admin.marketing.automations.name') }}
            </label>
            <p>
                <input value="{{ old('name', $automation->name) }}" type="text" name="name" id="name" placeholder="{{ __('admin.marketing.automations.name_placeholder') }}">
            </p>
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
        <p class="menu-dashboard__copy mt-3">{{ __('admin.marketing.automations.inactive_until_processed') }}</p>
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                {{ __('admin.marketing.automations.trigger') }}
            </h3>
        </div>

        <div>
            <label class="label_c" for="trigger">
                <i class="bi bi-lightning-charge-fill"></i>
                {{ __('admin.marketing.automations.trigger') }}
            </label>
            <p>
                <select name="trigger" id="trigger">
                    <option value="">{{ __('admin.marketing.automations.no_trigger') }}</option>
                    @foreach ($triggers as $value => $label)
                        <option value="{{ $value }}" @selected(old('trigger', $automation->trigger) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </p>
            @error('trigger') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="split mt-3">
            <div>
                <label class="label_c" for="metadata_cooldown_days">
                    <i class="bi bi-hourglass-split"></i>
                    {{ __('admin.marketing.automations.cooldown_days') }}
                </label>
                <p>
                    <input value="{{ $cooldownValue }}" type="number" min="0" step="1" name="metadata[cooldown_days]" id="metadata_cooldown_days">
                </p>
                @error('metadata.cooldown_days') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="metadata_enabled_from">
                    <i class="bi bi-calendar-plus"></i>
                    {{ __('admin.marketing.automations.enabled_from') }}
                </label>
                <p>
                    <input value="{{ $enabledFromValue }}" type="date" name="metadata[enabled_from]" id="metadata_enabled_from">
                </p>
                @error('metadata.enabled_from') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="split mt-3">
            <div>
                <label class="label_c" for="metadata_enabled_until">
                    <i class="bi bi-calendar-x"></i>
                    {{ __('admin.marketing.automations.enabled_until') }}
                </label>
                <p>
                    <input value="{{ $enabledUntilValue }}" type="date" name="metadata[enabled_until]" id="metadata_enabled_until">
                </p>
                @error('metadata.enabled_until') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div></div>
        </div>
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-envelope-fill"></i>
                </span>
                {{ __('admin.marketing.automations.mail_model') }}
            </h3>
        </div>

        <label class="label_c" for="model_id">
            <i class="bi bi-envelope-fill"></i>
            {{ __('admin.marketing.automations.mail_model') }}
        </label>
        <p>
            <select name="model_id" id="model_id">
                <option value="">{{ __('admin.marketing.automations.no_model') }}</option>
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
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                {{ __('admin.marketing.automations.linked_promotions') }}
            </h3>
        </div>

        <label class="label_c" for="promotions">
            <i class="bi bi-megaphone-fill"></i>
            {{ __('admin.marketing.automations.promotions') }}
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
        </div>

        <aside class="marketing-form-sidebar">
            <section class="order-detail__section marketing-form-preview">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                        {{ __('admin.marketing.automations.summary') }}
                    </h3>
                </div>

                <div class="marketing-form-preview__panel">
                    <div class="marketing-form-preview__head">
                        <span class="marketing-form-preview__icon">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </span>
                        <div>
                            <strong>{{ old('name', $automation->name) ?: __('admin.marketing.automations.name_placeholder') }}</strong>
                        </div>
                    </div>

                    <div class="marketing-form-preview__facts">
                        @if ($automation->exists)
                            <div class="marketing-form-preview__fact">
                                <span>{{ __('admin.marketing.automations.status') }}</span>
                                <strong>{{ $statuses[$automation->status] ?? $automation->status }}</strong>
                            </div>
                        @endif
                        <div class="marketing-form-preview__fact">
                            <span>{{ __('admin.marketing.automations.trigger') }}</span>
                            <strong>{{ $triggers[$selectedTrigger] ?? __('admin.marketing.automations.to_choose') }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>{{ __('admin.marketing.automations.model') }}</span>
                            <strong>{{ $previewMailModel?->name ?? __('admin.marketing.automations.to_choose') }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>{{ __('admin.marketing.automations.promotions') }}</span>
                            <strong>{{ $previewPromotions->count() }}</strong>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <div class="marketing-form-actions">
        <a class="order-detail__contact marketing-form-action--cancel" href="{{ $cancelUrl }}">
            <i class="bi bi-x-lg"></i>
            <span>{{ __('admin.common.cancel') }}</span>
        </a>
        <button class="order-detail__contact marketing-form-action--secondary" type="submit" name="submit_action" value="draft">
            <i class="bi bi-clock-history"></i>
            <span>{{ __('admin.marketing.automations.complete_later') }}</span>
        </button>
        <button class="order-detail__contact marketing-form-action--primary" type="submit" name="submit_action" value="activate">
            <i class="bi bi-check2-circle"></i>
            <span>{{ $primaryActionLabel }}</span>
        </button>
        @error('submit_action') <p class="error">{{ $message }}</p> @enderror
    </div>
</form>
