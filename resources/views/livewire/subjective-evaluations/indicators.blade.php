<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('subjective-evaluations.index') }}" wire:navigate 
               class="p-2 text-gray-500 hover:text-gray-900 dark:hover:text-white bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">
                    Master Indikator Penilaian Subjektif
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Kelola kategori, kriteria, dan item indikator penilaian kinerja dasar (RAFA).
                </p>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg dark:bg-gray-800 dark:text-green-400 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        @foreach($categories as $category)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ $category->code }}. {{ $category->name }}
                    </h2>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($category->criteria as $criterion)
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">
                                    {{ $criterion->number }}. {{ $criterion->name }}
                                </h3>
                                <button wire:click="openCreateModal({{ $criterion->id }})" 
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                    + Tambah Indikator
                                </button>
                            </div>

                            <ul class="space-y-2 pl-4">
                                @forelse($criterion->indicators as $indicator)
                                    <li class="flex items-start justify-between text-xs text-gray-700 dark:text-gray-300 p-2 rounded-lg bg-gray-50/50 dark:bg-gray-700/20">
                                        <div class="flex items-start gap-2">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $indicator->letter }}.</span>
                                            <span>{{ $indicator->statement }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="openEditModal({{ $indicator->id }})" class="text-gray-500 hover:text-indigo-600">
                                                Edit
                                            </button>
                                            <button wire:click="deleteIndicator({{ $indicator->id }})" 
                                                    wire:confirm="Yakin ingin menghapus indikator ini?" 
                                                    class="text-gray-500 hover:text-red-600">
                                                Hapus
                                            </button>
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-xs text-gray-400 italic">Belum ada indikator untuk kriteria ini.</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Indicator Modal --}}
    @if($showIndicatorModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 space-y-4 shadow-xl border border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                    {{ $editingIndicatorId ? 'Edit Indikator' : 'Tambah Indikator Baru' }}
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Huruf Poin (e.g. a, b, c):</label>
                        <input type="text" wire:model.defer="letter" class="w-full text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-2">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Pernyataan Indikator:</label>
                        <textarea wire:model.defer="statement" rows="3" class="w-full text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-2"></textarea>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Urutan Sort Order:</label>
                        <input type="number" wire:model.defer="sortOrder" class="w-full text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-2">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="closeModal" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md">Batal</button>
                    <button wire:click="saveIndicator" class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-md">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
