
@extends('layouts.base')

@section('contents')



<a class="my_btn_5 ml-auto" href="{{route('admin.mailer.index')}}"> Indietro </a>
<h2 class="my-4">Modifica il modello "{{$model->name}}"</h2>


<form class="creation"  action="{{ route('admin.mailer.update_model') }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    <input type="hidden" name="id" value="{{$model->id}}">

    <section class="base">

        <div class="split"> 
            <div>
                <label class="label_c" for="sender">
                <i class="bi bi-type" style="font-size: 16px"></i>
                Nome del modello *</label>   
                <input value="{{ old('name', $model->name) }}" type="text" name="name" id="name" class="w-100" placeholder="Insersci nome di questo template">
                    @error('name') <p class="error">{{ $message }}</p> @enderror

            </div>
                
            <div>
                <label class="label_c" for="sender">
                <i class="bi bi-type" style="font-size: 16px"></i>
                Mittente *</label>   
                <input value="{{ old('sender', $model->sender) }}" type="text" name="sender" id="sender" class="w-100" placeholder="es: Con affetto il proprietario Marco Rossi">
                    @error('sender') <p class="error">{{ $message }}</p> @enderror

            </div>
        </div>
        <p class="desc"> 
            <label class="label_c" for="object">
            <i class="bi bi-type" style="font-size: 16px"></i>
            Oggetto mail *</label>   
            <input value="{{ old('object', $model->object) }}" type="text" name="object" id="object" class="w-100" placeholder=" Inserisci l'oggetto della mail">
                @error('object') <p class="error">{{ $message }}</p> @enderror
        </p>
        <p class="desc"> 
            <label class="label_c" for="heading">
            <i class="bi bi-type" style="font-size: 16px"></i>
            Heading *</label>   
            <input value="{{ old('heading', $model->heading) }}" type="text" name="heading" id="heading" class="w-100" placeholder=" Inserisci il titolo">
                @error('heading') <p class="error">{{ $message }}</p> @enderror
        </p>
        <div>
            <label class="label_c" for="file-input">
                <i class="bi bi-file-earmark-image" style="font-size: 16px"></i>
                {{__('admin.Immagine_1')}}</label>
            <p><input type="file" id="file-input" name="img_1" ></p>
            @error('img_1') <p class="error">{{ $message }}</p> @enderror
        </div>
        <p class="desc"> 
            <label class="label_c" for="body">
            <i class="bi bi-body-text" style="font-size: 16px"></i> 
            {{__('admin.Corpo')}} *1</label>   
            <textarea name="body" id="body" cols="30" rows="10" > {{ old('body', $model->body) }} </textarea>
            @error('body') <p class="error">{{ $message }}</p> @enderror
            <p>{{ __('admin.Per_andare_a_capo_e_creare_pi_paragafi_inserire__tra_un_paragrafo_e_laltro') }}</p>
        </p>
        
        <div>
            <label class="label_c" for="file-input1">
                <i class="bi bi-file-earmark-image" style="font-size: 16px"></i>
                {{__('admin.Immagine_2')}}</label>
            <p><input type="file" id="file-input1" name="img_2" ></p>
            @error('img_2') <p class="error">{{ $message }}</p> @enderror
        </div>
        
        <p class="desc"> 
            <label class="label_c" for="ending">
            <i class="bi bi-body-text" style="font-size: 16px"></i> 
            Conclusione *1</label>   
            <textarea name="ending" id="ending" cols="30" rows="7" > {{ old('ending', $model->ending) }} </textarea>
            @error('ending') <p class="error">{{ $message }}</p> @enderror
        </p>
        <p>*1 Per andare a capo inserire \n e creare più paragafi inserire /*/ tra un paragrafo e l'altro </p>
    </section>

    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Modifica_modello') }}</button>

</form>



@endsection

