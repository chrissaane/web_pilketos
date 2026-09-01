<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
    <h4 class="font-bold text-slate-800 mb-3">Activity Log</h4>
    <ul class="space-y-3">
        @if(isset($activities) && count($activities))
            @foreach($activities as $act)
                @php
                    $status = strtolower($act->status ?? 'verified');
                    $badge = $status=='verified' ? 'bg-emerald-100 text-emerald-700' : ($status=='failed' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700');
                @endphp
                <li class="flex items-start gap-3 hover:bg-slate-50 p-2 rounded-lg transition">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                        <i class="fa-solid fa-{{ $act->icon ?? 'circle' }}"></i>
                    </div>
                    <div class="flex-1 text-sm">
                        <div class="flex justify-between">
                            <div class="text-slate-700">{{ $act->message }}</div>
                            <div class="text-xs text-slate-400">{{ $act->created_at ?? '' }}</div>
                        </div>
                        <div class="text-xs mt-1"><span class="px-2 py-0.5 rounded-full {{ $badge }}">{{ $act->status ?? 'Verified' }}</span></div>
                    </div>
                </li>
            @endforeach
        @else
            <li class="text-sm text-slate-500">Belum ada aktivitas.</li>
        @endif
    </ul>
</div>
