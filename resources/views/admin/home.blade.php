@extends('_template_adm.master')

@section('title', ucwords(lang('home', $translations)))

@section('content')
    <div class="row">
        {{-- display response message --}}
        @include('_template_adm.message')
        
        <h2 class="text-center">{{ strtoupper(lang('welcome, #name', $translations, ['#name' => Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->fullname])) }}</h2>
    </div>
@endsection