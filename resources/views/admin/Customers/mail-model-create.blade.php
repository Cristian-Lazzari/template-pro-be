@extends('layouts.base')

@section('contents')

@include('admin.Marketing.partials.breadcrumbs', [
    'items' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Marketing', 'url' => route('admin.marketing')],
        ['label' => 'Modelli mail', 'url' => route('admin.customers.mail_models.index')],
        ['label' => 'Crea'],
    ],
])

<div class="menu-dashboard__hero-actions dashboard-home__hero-actions my-4">
    <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.index') }}">
        <x-icon name="arrow-left-circle-fill" />
        <span>Indietro</span>
    </a>
    @include('admin.Marketing.partials.area-links', ['current' => 'models'])
</div>

<h2 class="my-4">{{ __('admin.Crea_il_tuo_modello') }}</h2>

<form class="creation" action="{{ route('admin.customers.mail_models.store') }}" enctype="multipart/form-data" method="POST">
    @csrf

    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="sender">
                    <i class="bi bi-type"></i>{{ __('admin.Nome_del_modello_') }}
                </label>
                <input value="{{ old('name') }}" type="text" name="name" id="name" class="w-100" placeholder="Insersci nome di questo template">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label_c" for="sender">
                    <i class="bi bi-type"></i>{{ __('admin.Mittente_') }}
                </label>
                <input value="{{ old('sender') }}" type="text" name="sender" id="sender" class="w-100" placeholder="es: Con affetto il proprietario Marco Rossi">
                @error('sender') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="desc">
            <label class="label_c" for="object">
                <i class="bi bi-type"></i>{{ __('admin.Oggetto_mail_') }}
            </label>
            <input value="{{ old('object') }}" type="text" name="object" id="object" class="w-100" placeholder=" Inserisci l'oggetto della mail">
            @error('object') <p class="error">{{ $message }}</p> @enderror
        </p>

        <p class="desc">
            <label class="label_c" for="heading">
                <i class="bi bi-type"></i>{{ __('admin.Heading_') }}
            </label>
            <input value="{{ old('heading') }}" type="text" name="heading" id="heading" class="w-100" placeholder=" Inserisci il titolo">
            @error('heading') <p class="error">{{ $message }}</p> @enderror
        </p>

        <div>
            <label class="label_c" for="file-input">
                <i class="bi bi-file-earmark-image"></i>
                {{ __('admin.Immagine_1') }}
            </label>
            <p><input type="file" id="file-input" name="img_1"></p>
            @error('img_1') <p class="error">{{ $message }}</p> @enderror
        </div>

        <p class="desc">
            <label class="label_c" for="body">
                <i class="bi bi-body-text"></i>
                {{ __('admin.Corpo') }} *1
            </label>
            <textarea name="body" id="body" cols="30" rows="10"> {{ old('body') }} </textarea>
            @error('body') <p class="error">{{ $message }}</p> @enderror
        </p>

        <div>
            <label class="label_c" for="file-input1">
                <i class="bi bi-file-earmark-image"></i>
                {{ __('admin.Immagine_2') }}
            </label>
            <p><input type="file" id="file-input1" name="img_2"></p>
            @error('img_2') <p class="error">{{ $message }}</p> @enderror
        </div>

        <p class="desc">
            <label class="label_c" for="ending">
                <i class="bi bi-body-text"></i>{{ __('admin.Conclusione_1') }}
            </label>
            <textarea name="ending" id="ending" cols="30" rows="7"> {{ old('ending') }} </textarea>
            @error('ending') <p class="error">{{ $message }}</p> @enderror
        </p>

        <p>{{ __('admin.1_Per_andare_a_capo_inserire_n_e_creare_pi_paragafi_inserire__tra_un_paragrafo_e_laltro') }}</p>
    </section>

    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Crea_modello') }}</button>
</form>

@endsection
