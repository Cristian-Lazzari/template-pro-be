@extends('layouts.base')

@section('contents')

@php
$allergien = [
            1 => ['img' => $domain . 'glutine.png', 'name' => 'glutine'] ,
            2 => ['img' => $domain . 'pesce.png', 'name' => 'pesce'] ,
            3 => ['img' => $domain . 'crostacei.png', 'name' => 'crostacei'] ,
            4 => ['img' => $domain . 'latticini.png', 'name' => 'latticini'] ,
            5 => ['img' => $domain . '', 'name' => ''] ,
            6 => ['img' => $domain . '', 'name' => ''] ,
            7 => ['img' => $domain . '', 'name' => ''] ,
        ]; 
@endphp


<form class="top-bar-product" action="{{ route('admin.product.special') }}" method="get">
    @csrf
    <div class="bar">


       
    </div>
    <div class="categories">
        @foreach ($categories as $item)
            <a href="{{ route('') }}">{{$item->name}}</a>
        @endforeach
    </div>
</form>
<div class="object-container">
    @foreach ($products as $item)
        <div class="obj">
            <h3>{{$item->name}}</h3>     
            <div class="card">
                @if (isset($item->image))
                    <img src="{{ asset('public/storage/' . $item->image) }}" alt="">
                @endif

            </div>
            <div class="allergiens">
                @php $allergiens = json_decode($item->allergiens) @endphp
                @foreach ($allergiens as $item)
                    <img src="{{$allergien[$item]['img']}}" alt="" title="{{$allergien[$item]['name']}}">
                @endforeach
            </div>
            <div class="actions">
                <a href="{{ route('admin.product.edit') }}">Modifica</a>
                <a href="">Archivia</a>
                <a href="">Visibilità -  @if ($item->vibility) on  @else off @endif</a>
            </div>

        </div>
    @endforeach
</div>
@endsection