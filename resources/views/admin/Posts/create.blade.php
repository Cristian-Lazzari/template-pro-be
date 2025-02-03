@extends('layouts.base')

@section('contents')
    
@if (session('ingredient_success'))
    @php
        $data = session('ingredient_success')
    @endphp
    <div class="alert alert-success">
        "{{ $data['name_ing'] }}" è stato correttamente creato!
    </div>
@endif
    


 
<a class="my_btn_5 ml-auto" href="{{ route('admin.posts.index') }}">Torna ai Post</a>

<h1>Crea nuovo Post</h1>
<form class="creation"  action="{{ route('admin.posts.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type" viewBox="0 0 16 16">
                        <path d="m2.244 13.081.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081zm2.7-7.923L6.34 9.314H3.51l1.4-4.156zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525"/>
                      </svg>
                    Titolo</label>
                <p><input value="{{ old('title') }}" type="text" name="title" id="title" placeholder=" inserisci il titolo"></p>
                @error('title') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="order">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                        <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                    </svg>
                    Precedenza *1</label>
                <p><input value="{{ old('order') }}" type="number" name="order" id="order" placeholder=" inserisci la precedenza "></p>
                @error('order') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">    
            <div>
                <label class="label_c" for="file-input">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-image" viewBox="0 0 16 16">
                        <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                        <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                    </svg>
                    Immagine principale</label>
                <p><input type="file" id="file-input" name="img_1" ></p>
                @error('img_1') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="file-input1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-image" viewBox="0 0 16 16">
                        <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                        <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                    </svg>
                    Immagine secondaria</label>
                <p><input type="file" id="file-input1" name="img_2" ></p>
                @error('img_2') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            <div>
                <label class="label_c" for="place">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe-americas" viewBox="0 0 16 16">
                        <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0M2.04 4.326c.325 1.329 2.532 2.54 3.717 3.19.48.263.793.434.743.484q-.121.12-.242.234c-.416.396-.787.749-.758 1.266.035.634.618.824 1.214 1.017.577.188 1.168.38 1.286.983.082.417-.075.988-.22 1.52-.215.782-.406 1.48.22 1.48 1.5-.5 3.798-3.186 4-5 .138-1.243-2-2-3.5-2.5-.478-.16-.755.081-.99.284-.172.15-.322.279-.51.216-.445-.148-2.5-2-1.5-2.5.78-.39.952-.171 1.227.182.078.099.163.208.273.318.609.304.662-.132.723-.633.039-.322.081-.671.277-.867.434-.434 1.265-.791 2.028-1.12.712-.306 1.365-.587 1.579-.88A7 7 0 1 1 2.04 4.327Z"/>
                      </svg>  
                    Dove si è svolto?</label>
                <p><input value="{{ old('place') }}" type="text" name="place" id="place" placeholder=" inserisci un luogo "></p>
                @error('place') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="date">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-date" viewBox="0 0 16 16">
                        <path d="M6.445 11.688V6.354h-.633A13 13 0 0 0 4.5 7.16v.695c.375-.257.969-.62 1.258-.777h.012v4.61zm1.188-1.305c.047.64.594 1.406 1.703 1.406 1.258 0 2-1.066 2-2.871 0-1.934-.781-2.668-1.953-2.668-.926 0-1.797.672-1.797 1.809 0 1.16.824 1.77 1.676 1.77.746 0 1.23-.376 1.383-.79h.027c-.004 1.316-.461 2.164-1.305 2.164-.664 0-1.008-.45-1.05-.82zm2.953-2.317c0 .696-.559 1.18-1.184 1.18-.601 0-1.144-.383-1.144-1.2 0-.823.582-1.21 1.168-1.21.633 0 1.16.398 1.16 1.23"/>
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                      </svg>
                    Periodo indicativo</label>
                <p><input value="{{ old('date') }}" type="date" name="date" id="date" ></p>
                @error('date') <p class="error">{{ $message }}</p> @enderror
            </div>

        </div>


 
        <div class="check_c">
            <label class="label_c" for="path">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                    <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2m0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2m0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14"/>
                </svg>
                Come categorizzi l'esperienza? *3</label>
            <p>
                @foreach($categories as $category)
                    <input type="checkbox" class="btn-check" id="category{{ $category->id }}" name="categorys[]" 
                    value="{{ $category->id }}"
                    @if(in_array($category->id, old('categorys', [])))
                        checked
                    @endif>

                    <label class="btn btn-outline-light shadow-sm" for="category{{ $category->id }}">{{ $category->name }}</label>
                    @error('categories') 
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror      
                @endforeach
            </p>
        </div>
        <div class="desc">
            <label class="label_c" >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                    <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                    <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                  </svg>
                Allega i link dei video YT *3</label>
            <p>
            <div class="input-group mb-3">
                <input type="text" id="youtubeLink" class="form-control" placeholder="Incolla il link YouTube" aria-label="YouTube Link">
                <button class="btn btn-light" id="addv" type="button" onclick="addVideo()">Aggiungi</button>
            </div>
    
            <ul id="videoList" class="list-group mb-3">
                <!-- I video verranno aggiunti qui -->
            </ul>
    
        </div>       
        <p class="desc"> 
            <label class="label_c" for="description">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-body-text" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M0 .5A.5.5 0 0 1 .5 0h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 0 .5m0 2A.5.5 0 0 1 .5 2h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m9 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-9 2A.5.5 0 0 1 .5 4h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m5 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-12 2A.5.5 0 0 1 .5 6h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-8 2A.5.5 0 0 1 .5 8h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-7 2a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                </svg>
                Descrizione *2</label>   
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description') }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </p>
        <div class="">

               

            
            </div>
            <p>*1 il post con la precedenza più alta verra visualizzato per primo</p>
            <p>*2 per andare a capo riportare i caratteri: <strong>/**/</strong> , per mettere in grassetto del testo invece basta racchiudere la porzione di testo che si vuole mettere in grassetto tra 3 asterischi in questo modo: <strong>***</strong> parola da mettere in grassetto <strong>***</strong> .  </p>
            <p>*3 campi facoltativi</p>
    </section>
    
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Crea Post</button>
    
</form>
<script>
   // document.addEventListener('DOMContentLoaded', async function() {
        // Array per memorizzare i link aggiunti

    let videoLinks = []; // Array per salvare i link

    const input = document.getElementById('youtubeLink');

    function addVideo() {
        const input = document.getElementById('youtubeLink');
        const url = input.value.trim();

        // Regex per validare link di YouTube
        const youtubeRegex = /^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
        const match = url.match(youtubeRegex);

        if (!match) {
            showAlert('Inserisci un link valido da YouTube', 'danger', 5000)
            return;
        }

        const videoId = match[4];

        // Controllo se il link è già stato aggiunto
        if (videoLinks.includes(videoId)) {
            showAlert('Video gia inserito!', 'warning', 5000)
            return;
        }

        // Aggiungi il video alla lista
        videoLinks.push(videoId);

        // Recupera il titolo del video da YouTube
        fetch(`https://noembed.com/embed?url=https://www.youtube.com/watch?v=${videoId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.title) {
                    showAlert('Errore nel recupero del video.', 'danger', 5000)
                    return;
                }

            // Creazione dell'elemento nella lista
            const listItem = document.createElement('li');
            listItem.className = "list-group-item d-flex justify-content-between align-items-center";

            // Input nascosto da inviare nel form
            const hiddenInput = document.createElement('input');
            hiddenInput.type = "hidden";
            hiddenInput.name = "videos[]";
            hiddenInput.value = `https://www.youtube.com/watch?v=${videoId}`;

            // Label con titolo del video
            const label = document.createElement('span');
            label.textContent = data.title;

            // Bottone di rimozione
            const removeButton = document.createElement('button');
            removeButton.className = "btn btn-danger btn-sm";
            removeButton.textContent = "Rimuovi";
            removeButton.onclick = function() {
                videoLinks = videoLinks.filter(id => id !== videoId);
                listItem.remove();
            };

            // Aggiunta degli elementi al DOM
            listItem.appendChild(label);
            listItem.appendChild(hiddenInput);
            listItem.appendChild(removeButton);
            document.getElementById('videoList').appendChild(listItem);

            // Resetta il campo input
            input.value = '';
        })
        .catch(error => {
            showAlert('Errore nel recupero del titolo.', 'danger', 5000) 
        });
    }
    function showAlert(message, type, timeout) {
        // Controlla se esiste già un contenitore per gli alert
        let alertContainer = document.getElementById('alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container';
            alertContainer.style.position = 'fixed';
            alertContainer.style.top = '0';
            alertContainer.style.left = '50%';
            alertContainer.style.transform = 'translateX(-50%)';
            alertContainer.style.width = 'auto';
            alertContainer.style.maxWidth = '90%';
            alertContainer.style.zIndex = '5550'; // Sopra a tutto
            document.body.appendChild(alertContainer);
        }

        // Creazione dell'alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show shadow-lg my-5`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Aggiunge l'alert al contenitore
        alertContainer.appendChild(alertDiv);

        // Rimuove l'alert dopo il timeout (default 5s)
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, timeout);
    }

</script>



@endsection