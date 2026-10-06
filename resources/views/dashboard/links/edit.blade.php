@extends('layouts.dashboard')
@section('page_title', 'Edit link')
@section('back', route('dashboard.links.index'))

@section('content')
<div class="max-w-2xl space-y-4">
    <form method="POST" action="{{ route('dashboard.links.update', $link) }}" class="card card-pad" novalidate>
        @csrf @method('PUT')
        @include('dashboard.links._fields')
        <div class="mt-6 flex justify-end"><button class="btn btn-primary">Save link</button></div>
    </form>
    <form method="POST" action="{{ route('dashboard.links.destroy', $link) }}" data-confirm="Delete this link?" class="flex justify-end">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm">{{ icon('trash-2', 'size-4') }} Delete link</button>
    </form>
</div>
@endsection
