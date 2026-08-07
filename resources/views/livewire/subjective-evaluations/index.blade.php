<div class="space-y-6">
    {{-- Header & Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">
                Penilaian Subjektif Kinerja
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Evaluasi kompetensi dasar (Self Assessment & Penilaian Atasan) bulanan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->isDirector() || auth()->user()->isManager())
                <a href="{{ route('subjective-evaluations.indicators') }}" wire:navigate 
                   class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Master Indikator
                </a>

                <button wire:click="generateSessions" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Buka Sesi Bulan Ini
                </button>
            @endif
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg dark:bg-gray-800 dark:text-green-400 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg dark:bg-gray-800 dark:text-red-400 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters Card --}}
    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Bulan:</label>
            <select wire:model.live="selectedMonth" 
                    class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1.5 px-2.5">
                @foreach($months as $num => $name)
                    <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
            <select wire:model.live="selectedYear" 
                    class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1.5 px-2.5">
                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="flex-1 min-w-[200px]">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama staff..." 
                   class="w-full text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1.5 px-3">
        </div>
    </div>

    {{-- Evaluations Table Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 uppercase font-semibold text-[11px] tracking-wider border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="py-3 px-4">Pegawai (Staff)</th>
                        <th class="py-3 px-4">Penilai (Atasan)</th>
                        <th class="py-3 px-4 text-center">Status Self Assessment</th>
                        <th class="py-3 px-4 text-center">Status Penilaian Atasan</th>
                        <th class="py-3 px-4 text-center">Rata-Rata Self</th>
                        <th class="py-3 px-4 text-center">Rata-Rata Atasan</th>
                        <th class="py-3 px-4 text-center">Skor Akhir Subjektif</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($evaluations as $eval)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $eval->user->name }}</div>
                                <div class="text-[11px] text-gray-500">{{ $eval->user->division->name ?? 'Tanpa Divisi' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                {{ $eval->evaluator->name ?? 'Belum Ditentukan' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($eval->self_status === 'Submitted')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Sudah Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        Draft / Belum
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($eval->manager_status === 'Submitted')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Sudah Dinilai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        Draft / Belum
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-medium">
                                {{ $eval->average_self_score ? number_format($eval->average_self_score, 2) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-medium">
                                {{ $eval->average_manager_score ? number_format($eval->average_manager_score, 2) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $eval->final_subjective_score ? number_format($eval->final_subjective_score, 2) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('subjective-evaluations.show', $eval->id) }}" wire:navigate 
                                   class="inline-flex items-center px-3 py-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    @if(auth()->id() === $eval->user_id && $eval->self_status !== 'Submitted')
                                        Isi Self Assessment &rarr;
                                    @elseif((auth()->user()->isDirector() || auth()->id() === $eval->evaluator_id) && $eval->manager_status !== 'Submitted')
                                        Isi Penilaian Atasan &rarr;
                                    @else
                                        Lihat Lembar Evaluasi &rarr;
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada sesi penilaian subjektif untuk periode {{ $selectedMonth }}/{{ $selectedYear }}.<br>
                                @if(auth()->user()->isDirector() || auth()->user()->isManager())
                                    Klik tombol <strong>"Buka Sesi Bulan Ini"</strong> di atas untuk memulai.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($evaluations->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $evaluations->links() }}
            </div>
        @endif
    </div>
</div>
