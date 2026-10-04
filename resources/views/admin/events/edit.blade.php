@extends('layouts.admin')

@section('title', 'Edit Event - Admin EventTix')

@section('content')
<div class="max-w-7xl mx-auto" x-data="eventForm()">
    <div class="mb-6">
        <a href="{{ route('admin.events.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500 inline-flex items-center gap-1 font-medium">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Kembali ke Daftar Event
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Event</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $event->title }}</p>
    </div>

    @include('admin.events._form')
</div>
@endsection
