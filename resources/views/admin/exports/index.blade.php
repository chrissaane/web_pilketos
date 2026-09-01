@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <h2 class="text-lg font-bold">Export Data</h2>
    <div class="bg-white rounded-lg p-4 border">
        <form action="{{ route('admin.export.run') }}" method="POST">
            @csrf
            <button class="px-4 py-2 bg-slate-800 text-white rounded-lg">Export</button>
        </form>
    </div>
</div>
@endsection
