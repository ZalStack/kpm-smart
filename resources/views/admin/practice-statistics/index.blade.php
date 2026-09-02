{{-- admin/practice-statistics/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Statistik Pengerjaan Soal - Admin')
@section('header-title', 'Statistik Pengerjaan Soal')
@section('header-sub', 'Monitor aktivitas pengerjaan soal oleh pengguna')

@section('content')
<livewire:practice-statistics />
@endsection
