@props(['title'=>'Title','value'=>'0','icon'=>'fa-solid fa-circle','color'=>'bg-slate-100','percent'=>null,'trend'=>'up'])

<div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
    <div class="w-12 h-12 flex items-center justify-center rounded-lg text-white {{ $color }} text-lg">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="flex-1">
        <div class="flex items-center justify-between">
            <div class="text-xs text-slate-400">{{ $title }}</div>
            @if($percent)
                <div class="text-xs inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-{{ $trend=='up' ? 'emerald' : 'rose' }}-50 text-{{ $trend=='up' ? 'emerald' : 'rose' }}-600">
                    <i class="fa-solid {{ $trend=='up' ? 'fa-arrow-up' : 'fa-arrow-down' }} text-xs"></i>
                    <span class="font-medium">{{ $percent }}</span>
                </div>
            @endif
        </div>
        <div class="text-xl font-bold text-slate-800">{{ $value }}</div>
        <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
            @php
                $p = is_numeric($percent) ? intval($percent) : null;
            @endphp
            <div class="h-2 rounded-full {{ $color }}" style="width:{{ $p ?? 30 }}%"></div>
        </div>
    </div>
</div>
