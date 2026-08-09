@props(['current'])
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>@if($current !== 'Dashboard')<li class="breadcrumb-item active" aria-current="page">{{ $current }}</li>@endif</ol></nav>
