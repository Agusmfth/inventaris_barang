@extends('errors.layout')
@section('code','403')
@section('title','Akses tidak diizinkan')
@section('message','Anda tidak memiliki izin untuk membuka halaman atau menjalankan tindakan tersebut.')
@section('actions')<a href="{{ url('/') }}">Kembali ke Dashboard</a><a class="secondary" href="{{ url()->previous() }}">Kembali</a>@endsection
