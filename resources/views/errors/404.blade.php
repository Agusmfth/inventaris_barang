@extends('errors.layout')
@section('code','404')
@section('title','Halaman tidak ditemukan')
@section('message','Halaman yang Anda cari tidak tersedia, telah dipindahkan, atau alamatnya tidak tepat.')
@section('actions')<a href="{{ url('/') }}">Kembali ke Dashboard</a>@endsection
