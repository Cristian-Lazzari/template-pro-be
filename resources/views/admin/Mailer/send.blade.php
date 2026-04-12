
@extends('layouts.base')

@section('contents')
@if (session('send_success'))
    @php
        $data = session('send_success')
    @endphp
    <div class="alert alert-primary">
        {{ $data }}
    </div>
@endif





<h1 class="my-4">{{ __('admin.Invia_Mail') }}</h1>

<form class="creation email-m"  action="{{ route('admin.mailer.send_m') }}"  method="POST"  >
    @csrf
        <section class="check_c">
            <h3 class="mb-4">{{ __('admin.Scegli_la_lista_di_destinatari') }}</h3>        
            <label class="label_c" for="type">
                <i class="bi bi-ui-checks-grid"></i>
                Liste
            </label>

            <p class="mail-list-check">
                <input type="checkbox" name="recipients[]" class="btn-check" id="1" value="1">
                <label class="btn btn-outline-light" for="1">
                    <span>{{ __('admin.Contatti_dalle_Prenotaioni') }}</span>
                    <span>{{$n_c[0]}}</span>
                </label>

                <input type="checkbox" name="recipients[]" class="btn-check" id="2" value="2">
                <label class="btn btn-outline-light" for="2">
                    <span>{{ __('admin.Contatti_daglOrdini') }}</span>
                    <span>{{$n_c[1]}}</span>
                </label>

                <input type="checkbox" name="recipients[]" class="btn-check" id="3" value="3">
                <label class="btn btn-outline-light" for="3">
                    <span>{{ __('admin.Contatti_aggiunti_manualmente') }}</span>
                    <span>{{$n_c[2]}}</span>
                </label>

                <input type="checkbox" name="recipients[]" class="btn-check" id="4" value="4">
                <label class="btn btn-outline-light" for="4">
                    <span>{{ __('admin.Ultima_lista_Contatti') }}</span>
                    <span>{{$n_c[3]}}</span>
                </label>
            </p>
            
            
            @error('recipients') <p class="error"> {{ $message }}</p> @enderror
        </section>

        <section class="check_c">
            <h3 class="mb-4">{{ __('admin.Scegli_un_modello_da_inviare') }}</h3>        
            <label class="label_c" for="type">
                <i class="bi bi-ui-checks-grid"></i>
                Modelli Mail
            </label>

            <div class="models">
                @foreach ($models as $m)
                    
                    <div class="model">
                        <input type="radio" name="models[]" class="btn-check" id="{{$m->id}}m"  value="{{$m->id}}">
                        <label class="btn btn-outline-dark m-auto" for="{{$m->id}}m">{{$m->name}}</label>

                       
            
                        <h1>{{$m->heading}}</h1>
                    
                        @if($m->img_1 !== NULL)   
                        <img src="{{ asset('public/storage/' . $m->img_1) }}" alt="">
                        @endif
                        
                        <span>€</span>     
                    </div>
                @endforeach
            </div>
             
            @error('models') <p class="error"> {{ $message }}</p> @enderror
        </section>

       



    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.invia_mail') }}</button>

</form>



@endsection
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        document.getElementById('addEmailsButton').addEventListener('click', function() {
            const emailInput = document.getElementById('emailInput');
            const emailList = document.getElementById('emailList');
            const emails = emailInput.value.split(/[ ,]+/).filter(Boolean); // Dividi per spazio o virgola e rimuovi stringhe vuote

            const existingEmails = Array.from(emailList.querySelectorAll('input[name="recipients[]"]')).map(input => input.value);

            emails.forEach(email => {
                if (validateEmail(email)) {
                    // Controlla se l'email esiste già nella lista
                    if (!existingEmails.includes(email)) {
                        const uniqueId = `email-${Math.random().toString(36).substr(2, 9)}`; // Genera un ID univoco

                        const wrapper = document.createElement('div');
                        wrapper.className = 'd-flex gap-2 wrapper';

                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.checked = true;
                        input.className = 'd-none';
                        input.id = uniqueId;
                        input.name = 'recipients[]';
                        input.value = email;

                        const label = document.createElement('label');
                        //label.className = 'btn btn-outline-light ';
                        label.setAttribute('for', uniqueId);
                        label.textContent = email;

                        const removeButton = document.createElement('button');
                        removeButton.className = 'btn btn-outline-light btn-sm';
                        removeButton.innerHTML = 'Rimuovi'; // Icona cestino
                        removeButton.addEventListener('click', () => {
                            emailList.removeChild(wrapper);
                        });

                        wrapper.appendChild(input);
                        wrapper.appendChild(label);
                        wrapper.appendChild(removeButton);
                        emailList.appendChild(wrapper);
                    } else {
                        alert(`L'email "${email}" è già nella lista!`);
                    }
                } else {
                    alert(`L'email "${email}" non è valida!`);
                }
            });

            emailInput.value = ''; // Svuota l'input
        });

        // Funzione per validare l'email
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    })
</script>
<style>
    .wrapper{
        flex: 1 !important;
        border-radius: 10px;
        background-color: rgba(0, 0, 0, 0.225);
        padding: .6rem .8rem;
        max-width: 100%;
        flex-wrap: wrap
    }
    .wrapper label{
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        background-color: rgba(255, 0, 0, 0) !important;
    }
</style>

