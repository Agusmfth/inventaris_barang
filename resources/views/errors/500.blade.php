@extends('errors.layout')
@section('code','500')
@section('title','Terjadi kendala pada sistem')
@section('message','Permintaan belum dapat diproses. Silakan coba beberapa saat lagi atau hubungi administrator.')
@section('actions')<a href="{{ url('/') }}">Kembali ke Dashboard</a><a class="secondary" href="{{ request()->fullUrl() }}">Coba Lagi</a>@endsection
