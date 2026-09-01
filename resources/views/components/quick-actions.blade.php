<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <a href="{{ route('admin.cards.index') }}" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow hover:scale-105 transition">
        <i class="fa-solid fa-image"></i>
        <span>Tambah Card</span>
    </a>
    <a href="{{ route('admin.candidates.index') }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl shadow hover:scale-105 transition">
        <i class="fa-solid fa-user-plus"></i>
        <span>Tambah Kandidat</span>
    </a>
    <a href="{{ route('admin.schedules.index') }}" class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl shadow hover:scale-105 transition">
        <i class="fa-solid fa-calendar-plus"></i>
        <span>Tambah Jadwal</span>
    </a>
    <a href="{{ route('admin.import.index') }}" class="flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-xl shadow hover:scale-105 transition">
        <i class="fa-solid fa-file-import"></i>
        <span>Import Excel</span>
    </a>
    <a href="{{ route('admin.export.index') }}" class="flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-xl shadow hover:scale-105 transition">
        <i class="fa-solid fa-file-export"></i>
        <span>Export Data</span>
    </a>
</div>
