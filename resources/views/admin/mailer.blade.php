
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

<h1>Mailer</h1>
<h2>Crea la tua mail</h2>


<form class="creation"  action="{{ route('admin.mailer.send_mail') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="base">
        <section class="check_c">
            <h3 class="mb-4">Gestione Email</h3>        
            <label class="label_c" for="type">@
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
                </svg>
                Lista destinatari
            </label>
            <div id="emailList" class="p">
                @foreach ($last_mail_list as $c)
                <div class="wrapper d-flex gap-2">
                    <input type="checkbox" name="recipients[]" class="d-none" id="{{$c}}" value="{{$c}}">
                    <label class="" for="{{$c}}">{{$c}}</label>
                </div>
                @endforeach
                <!-- Email verranno aggiunte qui -->
            </div>
            
            <div class="split ">
                <input type="text" id="emailInput" class="" placeholder="esempio@email.com, altro@email.com">
                <div id="addEmailsButton" class="my_btn_1 w-auto">Aggiungi alla lista</div>
            </div>
            @error('recipients') <p class="error"> {{ $message }}</p> @enderror
        </section>
       

        <div class="split"> 
            <div>
                <label class="label_c" for="object">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type" viewBox="0 0 16 16">
                    <path d="m2.244 13.081.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081zm2.7-7.923L6.34 9.314H3.51l1.4-4.156zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525"/>
                </svg>
                Oggetto mail</label>   
                <input value="{{ old('object') }}" type="text" name="object" id="object" class="w-100" placeholder=" inserisci il titolo">
                    @error('object') <p class="error">{{ $message }}</p> @enderror
            </div>
                
            <div>
                <label class="label_c" for="sender">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type" viewBox="0 0 16 16">
                    <path d="m2.244 13.081.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081zm2.7-7.923L6.34 9.314H3.51l1.4-4.156zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525"/>
                </svg>
                Mittente</label>   
                <input value="{{ old('sender') }}" type="text" name="sender" id="sender" class="w-100" placeholder=" inserisci il titolo">
                    @error('sender') <p class="error">{{ $message }}</p> @enderror

            </div>
        </div>
        <p class="desc"> 
            <label class="label_c" for="heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type" viewBox="0 0 16 16">
                <path d="m2.244 13.081.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081zm2.7-7.923L6.34 9.314H3.51l1.4-4.156zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525"/>
            </svg>
            Heading</label>   
            <input value="{{ old('heading') }}" type="text" name="heading" id="heading" class="w-100" placeholder=" inserisci il titolo">
                @error('heading') <p class="error">{{ $message }}</p> @enderror
        </p>
        <div>
            <label class="label_c" for="file-input">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-image" viewBox="0 0 16 16">
                    <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                    <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                </svg>
                Immagine</label>
            <p><input type="file" id="file-input" name="img_1" ></p>
            @error('img_1') <p class="error">{{ $message }}</p> @enderror
        </div>
        <p class="desc"> 
            <label class="label_c" for="body">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-body-text" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M0 .5A.5.5 0 0 1 .5 0h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 0 .5m0 2A.5.5 0 0 1 .5 2h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m9 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-9 2A.5.5 0 0 1 .5 4h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m5 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-12 2A.5.5 0 0 1 .5 6h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-8 2A.5.5 0 0 1 .5 8h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-7 2a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
            </svg> 
            Corpo</label>   
            <textarea name="body" id="body" cols="30" rows="10" > {{ old('body') }} </textarea>
            @error('body') <p class="error">{{ $message }}</p> @enderror
        </p>
        
        <div>
            <label class="label_c" for="file-input1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-image" viewBox="0 0 16 16">
                    <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                    <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                </svg>
                Immagine</label>
            <p><input type="file" id="file-input1" name="img_2" ></p>
            @error('img_2') <p class="error">{{ $message }}</p> @enderror
        </div>
        
        <p class="desc"> 
            <label class="label_c" for="ending">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-body-text" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M0 .5A.5.5 0 0 1 .5 0h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 0 .5m0 2A.5.5 0 0 1 .5 2h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m9 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-9 2A.5.5 0 0 1 .5 4h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m5 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-12 2A.5.5 0 0 1 .5 6h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-8 2A.5.5 0 0 1 .5 8h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-7 2a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
            </svg> 
            Conclusione</label>   
            <textarea name="ending" id="ending" cols="30" rows="7" > {{ old('ending') }} </textarea>
            @error('ending') <p class="error">{{ $message }}</p> @enderror
        </p>
       
    </section>

    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">invia mail</button>

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

