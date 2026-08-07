<div class="space-y-6">
    {{-- Top Navigation & Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('subjective-evaluations.index') }}" wire:navigate 
               class="p-2 text-gray-500 hover:text-gray-900 dark:hover:text-white bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">
                    Lembar Penilaian Subjektif Kinerja
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Pegawai: <strong class="text-gray-800 dark:text-gray-200">{{ $evaluation->user->name }}</strong> ({{ $evaluation->user->division->name ?? 'Tanpa Divisi' }}) &bull; Periode: <strong>{{ $evaluation->month }}/{{ $evaluation->year }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                🖨️ Cetak / Print
            </button>

            @if($canEditSelf && $evaluation->self_status !== 'Submitted')
                <button wire:click="saveSelfAssessment(true)" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm">
                    Kirim Self Assessment
                </button>
            @endif

            @if($canEditManager && $evaluation->manager_status !== 'Submitted')
                <button wire:click="saveManagerAssessment(true)" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm">
                    Simpan Penilaian Atasan
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

    {{-- Status Summary Card --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Status Self Assessment:</span>
            <div class="mt-1 font-semibold text-sm">
                @if($evaluation->self_status === 'Submitted')
                    <span class="text-green-600 dark:text-green-400">✓ Sudah Selesai ({{ $evaluation->self_submitted_at?->format('d M Y H:i') }})</span>
                @else
                    <span class="text-yellow-600 dark:text-yellow-400">⏳ Belum Dikirim (Draft)</span>
                @endif
            </div>
        </div>

        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Status Penilaian Atasan:</span>
            <div class="mt-1 font-semibold text-sm">
                @if($evaluation->manager_status === 'Submitted')
                    <span class="text-green-600 dark:text-green-400">✓ Sudah Dinilai oleh {{ $evaluation->evaluator->name ?? 'Atasan' }} ({{ $evaluation->manager_submitted_at?->format('d M Y H:i') }})</span>
                @else
                    <span class="text-yellow-600 dark:text-yellow-400">⏳ Belum Dinilai</span>
                @endif
            </div>
        </div>

        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Skor Akhir Subjektif:</span>
            <div class="mt-1 font-bold text-lg text-indigo-600 dark:text-indigo-400">
                {{ $evaluation->final_subjective_score ? number_format($evaluation->final_subjective_score, 2) : '-' }} / 5.00
            </div>
        </div>
    </div>

    {{-- Main Evaluation Table (Output matching exact user reference image) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden shadow-sm print:shadow-none print:border-black">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 font-bold border-b border-gray-300 dark:border-gray-600">
                        <th class="py-3 px-4 w-12 text-center border-r border-gray-300 dark:border-gray-600">No</th>
                        <th class="py-3 px-4 border-r border-gray-300 dark:border-gray-600">Item / Indikator Penilaian</th>
                        <th class="py-3 px-4 w-36 text-center border-r border-gray-300 dark:border-gray-600">Self Assessment</th>
                        <th class="py-3 px-4 w-36 text-center">Penilaian Atasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($categories as $category)
                        {{-- Category Header Row (e.g. I. Kompetensi Dasar) --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/80 font-bold text-gray-900 dark:text-white border-b border-gray-300 dark:border-gray-600">
                            <td class="py-2.5 px-4 text-center border-r border-gray-300 dark:border-gray-600 font-bold">
                                {{ $category->code }}.
                            </td>
                            <td colspan="3" class="py-2.5 px-4 text-sm font-bold">
                                {{ $category->name }}
                            </td>
                        </tr>

                        @foreach($category->criteria as $criterion)
                            {{-- Criterion Header Row (e.g. 1 Rispek) --}}
                            <tr class="bg-gray-50/50 dark:bg-gray-800/40 text-gray-900 dark:text-gray-100 font-semibold border-b border-gray-200 dark:border-gray-700">
                                <td class="py-2 px-4 text-center border-r border-gray-300 dark:border-gray-600 font-semibold">
                                    {{ $criterion->number }}
                                </td>
                                <td colspan="3" class="py-2 px-4 font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $criterion->name }}
                                </td>
                            </tr>

                            @foreach($criterion->indicators as $indicator)
                                {{-- Indicator Row (e.g. a. Menerima dan menghargai...) --}}
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-700/20 border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2.5 px-4 text-center border-r border-gray-300 dark:border-gray-600 font-medium text-gray-500">
                                        {{ $indicator->letter }}.
                                    </td>
                                    <td class="py-2.5 px-4 border-r border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ $indicator->statement }}
                                    </td>

                                    {{-- Self Assessment Score Cell --}}
                                    <td class="py-2.5 px-4 text-center border-r border-gray-300 dark:border-gray-600 font-semibold align-middle">
                                        @if($canEditSelf && $evaluation->self_status !== 'Submitted')
                                            <select wire:model.defer="selfScores.{{ $indicator->id }}" 
                                                    class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1 px-2 text-center font-bold">
                                                <option value="">- Pilihlah -</option>
                                                <option value="5">5 - Sangat Baik</option>
                                                <option value="4">4 - Baik</option>
                                                <option value="3">3 - Cukup</option>
                                                <option value="2">2 - Kurang</option>
                                                <option value="1">1 - Sangat Kurang</option>
                                            </select>
                                        @else
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $selfScores[$indicator->id] ?? '-' }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Manager Score Cell --}}
                                    <td class="py-2.5 px-4 text-center font-semibold align-middle">
                                        @if($canEditManager && $evaluation->manager_status !== 'Submitted')
                                            <select wire:model.defer="managerScores.{{ $indicator->id }}" 
                                                    class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1 px-2 text-center font-bold">
                                                <option value="">- Pilihlah -</option>
                                                <option value="5">5 - Sangat Baik</option>
                                                <option value="4">4 - Baik</option>
                                                <option value="3">3 - Cukup</option>
                                                <option value="2">2 - Kurang</option>
                                                <option value="1">1 - Sangat Kurang</option>
                                            </select>
                                        @else
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $managerScores[$indicator->id] ?? '-' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach

                    {{-- Summary Row: Rata-Rata Skor --}}
                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold border-t-2 border-gray-400 dark:border-gray-500 text-gray-900 dark:text-white">
                        <td colspan="2" class="py-3 px-4 text-right border-r border-gray-300 dark:border-gray-600">
                            RATA-RATA SKOR
                        </td>
                        <td class="py-3 px-4 text-center border-r border-gray-300 dark:border-gray-600 text-sm font-extrabold">
                            {{ $evaluation->average_self_score ? number_format($evaluation->average_self_score, 2) : '-' }}
                        </td>
                        <td class="py-3 px-4 text-center text-sm font-extrabold text-indigo-600 dark:text-indigo-400">
                            {{ $evaluation->average_manager_score ? number_format($evaluation->average_manager_score, 2) : '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Manager Notes Section --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Catatan & Ulasan Atasan</h3>
        @if($canEditManager && $evaluation->manager_status !== 'Submitted')
            <textarea wire:model.defer="notes" rows="3" placeholder="Masukkan ulasan atau masukan untuk pengembangan staf..." 
                      class="w-full text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-2.5"></textarea>
            <div class="flex justify-end mt-2">
                <button wire:click="saveManagerAssessment(false)" class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Simpan Draft Penilaian
                </button>
            </div>
        @else
            <p class="text-xs text-gray-700 dark:text-gray-300 italic">
                {{ $evaluation->notes ?: 'Tidak ada catatan khusus dari atasan.' }}
            </p>
        @endif
    </div>

    @if($canEditSelf && $evaluation->self_status !== 'Submitted')
        <div class="flex justify-end gap-3 pt-2">
            <button wire:click="saveSelfAssessment(false)" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Simpan Draft Self Assessment
            </button>
            <button wire:click="saveSelfAssessment(true)" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                Kirim Self Assessment Sekarang
            </button>
        </div>
    @endif
</div>
