@extends('errors.minimal')

@section('title', __('Troppe richieste'))
@section('code', '429')
@section('message', __('Hai effettuato troppe richieste in poco tempo. Attendi qualche istante e riprova.'))
