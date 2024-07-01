@extends('layouts.base')



@section('contents')

<h1>Ordinazioni d'asporto / domicilio</h1>

<form class="top-bar-product" action="{{ route('admin.orders.filter') }}" method="post">
    @csrf   
    <div class="bar">
        <div class="s-name">
            <label for="name" class="fw-semibold">Nome Cliente</label>
            <div>
                <input type="text" class="" id="name" name="name"
                    @if (isset($filters))
                        value="{{  $filters['name'] }}"  
                    @endif > 
                
            </div>
        </div>
        <div class="s-name">
            <label for="date" class="fw-semibold">Data</label>
            <div>
                <input type="date" class="" id="name" name="date"
                    @if (isset($filters))
                        value="{{  $filters['name'] }}"  
                    @endif > 
                <button class="search bg-primary d-none d-sm-block" type="sumbit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </div>
        </div>


        <div>
            <label for="status" class="form-label fw-semibold">Status</label>
            <select class="" id="status" name="status" >
                <option @if (isset($filters) && $filters['status'] == '2') selected @endif value="2">In Elaborazione</option>
                <option @if (isset($filters) && $filters['status'] == '1') selected @endif value="1">Confermate</option>
                <option @if (isset($filters) && $filters['status'] == '0') selected @endif value="0">Annullate</option>
                <option @if (isset($filters) && $filters['status'] == '1') selected @endif value="3">Tutte</option>
            </select>
        </div>
        <div>
            <label for="order" class="form-label fw-semibold">Ordina</label>
            <select class="" id="order" name="order" >
                <option @if (isset($filters) && $filters['order'] == '0') selected @endif value="0">Data di prenotazione</option>
                <option @if (isset($filters) && $filters['order'] == '1') selected @endif value="1">Data di creazione</option>
            </select>
        </div>
        
       
        <div class="buttons_">
         <button type="submit" class=" my_btn">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/>
             </svg>  FILTRA
         </button>
         <a class="my_btn_1 search" href="{{ route('admin.orders.index')}}">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                 <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
             </svg>  RIPRISTINA
         </a>   
        </div>
    </div>
    
</form> 



    {{-- Legenda  --}}
    <div class="py-3">
        <?php 
        $statuses = ['In Elaborazione', 'Confermato', 'Annullato'];
        ?>
        @foreach ($statuses as $status)
            @if ($status == 'In Elaborazione')
                <span class="text-warning">
                    <span>In Elaborazione</span>
            @elseif ($status == 'Confermato')
                <span class="text-success">
                    <span>Confermato</span>
            @else
                <span class="text-danger">
                    <span>Annullato</span>
            @endif
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-fill me-2" viewBox="0 0 16 16">
                    <circle cx="7" cy="7" r="7"/>
                </svg>
            </span>
        @endforeach
    </div>

        
    @foreach ($orders as $order)
        <?php
        $data_ora = DateTime::createFromFormat('d/m/Y H:i', $order->date_slot);
        $ora_formattata = $data_ora->format('H:i');
        $data_formattata = $data_ora->format('d/m');

        if ($order->status == 0) {
            $status_bg_color = 'bg-warning';
        } else if ($order->status == 1) {
            $status_bg_color = 'bg-success';
        } else {
            $status_bg_color = 'bg-danger';
        }
        ?>
        
        <div class="or-res">
            <section class="top">
                <p>{{$order->day}}/{{$order->month}}/{{$order->year}}</p>
                <h3>{{$order->surname}} {{$order->name}}</h3>
                <div class="actions">
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="my_btn_1 u">Dettagli</a>
                    <div class="my_btn_1 u">Contatta</div>
                </div>
            </section>
            <section>
                <h1 class="p">{{$order->time}}</h1>
                <p>Totale ordine: {{$order->tot_price}}</p>
                <div class="actions">
                    <div class="my_btn_1 u">Conferma</div>
                    <div class="my_btn_1 u">Annulla</div>
                </div>
            </section>
        </div>
    @endforeach
        
      

@endsection