@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="dash-c">
    @php
        $pack = ['', 'Essetians', 'Cene & Pranzi', 'Delivery & Asporto', 'Premium' ]
    @endphp
    <p> 
        <a class="my_btn_5 m-2" href="https://future-plus.it/#pacchetti">Pacchetto: {{$pack[config('configurazione.pack')]}}</a>
        <a class="my_btn_3 m-2" href="{{route('admin.statistics')}}">  
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard2-data" viewBox="0 0 16 16">
                <path d="M9.5 0a.5.5 0 0 1 .5.5.5.5 0 0 0 .5.5.5.5 0 0 1 .5.5V2a.5.5 0 0 1-.5.5h-5A.5.5 0 0 1 5 2v-.5a.5.5 0 0 1 .5-.5.5.5 0 0 0 .5-.5.5.5 0 0 1 .5-.5z"/>
                <path d="M3 2.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 0 0-1h-.5A1.5 1.5 0 0 0 2 2.5v12A1.5 1.5 0 0 0 3.5 16h9a1.5 1.5 0 0 0 1.5-1.5v-12A1.5 1.5 0 0 0 12.5 1H12a.5.5 0 0 0 0 1h.5a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                <path d="M10 7a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zm-6 4a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm4-3a1 1 0 0 0-1 1v3a1 1 0 1 0 2 0V9a1 1 0 0 0-1-1"/>
            </svg> <span>Statistiche</span>
        </a>
        <a class="my_btn_6 m-2" href="{{route('admin.mailer.index')}}">  
            <span>Email Marketing</span>
        </a>
    </p>

    
    <div class="top-c">
   

        <div class="prod post">  
            <div class="top-p">
                <a class="title" href="{{ route('admin.posts.index') }}"> <h2>Post</h2></a>
                <a href="{{ route('admin.posts.index') }}" class=" plus icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                      </svg>
                </a>
                <a href="{{ route('admin.posts.create') }}" class="plus">
                    <div class="line"></div>
                    <div class="line l2"></div>
                </a>
                
            </div>
            <div class="stat-p">
                <div class="stat">
                    <h2>{{$post[1]}}</h2>
                    <span>totali</span>
                </div>
                <div class="stat">
                    <h2>{{$post[2]}}</h2>
                    <span>pronti</span>
                </div>
                <div class="stat">
                    <h2>{{$post[3]}}</h2>
                    <span>postati</span>
                </div>
                <div class="stat">
                    <h2>{{$post[4]}}</h2>
                    <span>archiviati</span>
                </div>
            </div>   
        </div>
    </div>
</div>

@endsection

