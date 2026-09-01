@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold">Jadwal Voting</h2>
        <a href="#" class="px-4 py-2 bg-amber-500 text-white rounded-lg">Tambah Jadwal</a>
    </div>
    <div class="bg-white rounded-lg p-4 border">
        @if(isset($schedules) && count($schedules))
            <ul class="space-y-2">
                @foreach($schedules as $s)
                    <li class="flex justify-between">{{ $s->kelas ?? 'Kelas' }} <span class="text-slate-500">{{ $s->time ?? '' }}</span></li>
                @endforeach
            </ul>
        @else
            Belum ada jadwal (stub)
        @endif
    </div>
</div>
@endsection
