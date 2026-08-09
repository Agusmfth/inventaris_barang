@extends('errors.layout')
@section('code','419')
@section('title','Sesi telah berakhir')
@section('message','Demi keamanan, sesi Anda telah berakhir. Silakan muat ulang halaman dan coba kembali.')
@section('actions')<a href="{{ url('/login') }}">Masuk Kembali</a><a class="secondary" href="{{ request()->fullUrl() }}">Muat Ulang</a>@endsection
