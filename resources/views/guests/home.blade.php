@extends('layouts.guest')

@section('contents')
    <div class="container my-3">
        <a class="my_btn_2" href="{{route('login')}}">{{ __('admin.Accedi') }}</a>
    </div>
@endsection