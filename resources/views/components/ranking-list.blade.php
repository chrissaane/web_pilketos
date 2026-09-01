<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
    <h4 class="font-bold text-slate-800 mb-3">Ranking Kandidat</h4>
    <div class="space-y-3">
        @if(isset($candidates) && count($candidates))
            @foreach($candidates as $cand)
                @php
                    $rank = $loop->iteration;
                    $medalBg = $rank==1 ? 'bg-yellow-400 text-slate-900' : ($rank==2 ? 'bg-slate-200 text-slate-900' : ($rank==3 ? 'bg-amber-200 text-slate-900' : 'bg-slate-100 text-slate-700'));
                @endphp
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $medalBg }} font-semibold">{{ $rank }}</div>
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <div class="font-medium text-slate-700">{{ $cand->name }}</div>
                            <div class="text-sm text-slate-500">{{ $cand->votes ?? 0 }} suara</div>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full mt-2 overflow-hidden">
                            <div class="h-2 bg-emerald-500 rounded-full transition-all" style="width:{{ $cand->percent ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="w-12 text-right text-sm font-semibold text-slate-600">{{ $cand->percent ?? 0 }}%</div>
                </div>
            @endforeach
        @else
            <div class="text-sm text-slate-500">Tidak ada data kandidat.</div>
        @endif
    </div>
</div>
