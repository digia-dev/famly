<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PlannedTransaction;
use App\Models\KategoriNamaTabungan;
use App\Models\KategoriJenisTabungan;
use App\Models\Tabungan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AgendaController extends Controller
{
    /**
     * Display the family agenda.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        // Capture Month/Year/Type from filter, fallback to current
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $viewType = $request->get('type', 'week'); // 'week' or 'month'
        
        $currentDate = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Calculate the range for data fetching based on view type
        $fetchStart = $startOfMonth;
        $fetchEnd = $endOfMonth;

        if ($viewType === 'month') {
            $fetchStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
            $fetchEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);
        }

        // Fetch all planned transactions for the family within the calculated range
        $plannedTransactions = PlannedTransaction::with(['user', 'kategoriNama', 'kategoriJenis'])
            ->where('family_id', $familyId)
            ->where(function($q) use ($fetchStart, $fetchEnd, $familyId) {
                $q->whereBetween('jatuh_tempo', [$fetchStart, $fetchEnd])
                  ->orWhere(function($sub) use ($familyId) {
                      $sub->where('family_id', $familyId)
                          ->where('status', 'pending')
                          ->where('jatuh_tempo', '<', now());
                  });
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END ASC")
            ->orderBy('jatuh_tempo', 'asc')
            ->get();

        // Categorize for counting
        $reminders = $plannedTransactions->filter(fn($i) => $this->isReminder($i));
        $tasks = $plannedTransactions->reject(fn($i) => $this->isReminder($i));

        // Calendar Generation
        if ($viewType === 'month') {
            // Full Month Grid (42 days typical for a full calendar)
            $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
            $endOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);
            
            $calendarDays = [];
            $tempDate = $startOfGrid->copy();
            while ($tempDate <= $endOfGrid) {
                $calendarDays[] = [
                    'name' => $this->getDayName($tempDate),
                    'date' => $tempDate->format('d'),
                    'full_date' => $tempDate->format('Y-m-d'),
                    'is_today' => $tempDate->isToday(),
                    'is_current_month' => $tempDate->month == $month,
                    'has_agenda' => $plannedTransactions->contains(fn($t) => $t->jatuh_tempo->isSameDay($tempDate)),
                ];
                $tempDate->addDay();
            }
        } else {
            // Weekly Bar
            $targetDate = ($month == now()->month && $year == now()->year) ? now() : $startOfMonth;
            $startOfWeek = $targetDate->copy()->startOfWeek(Carbon::MONDAY);
            
            $calendarDays = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $startOfWeek->copy()->addDays($i);
                $calendarDays[] = [
                    'name' => $this->getDayName($day),
                    'date' => $day->format('d'),
                    'full_date' => $day->format('Y-m-d'),
                    'is_today' => $day->isToday(),
                    'is_current_month' => true,
                    'has_agenda' => $plannedTransactions->contains(fn($t) => $t->jatuh_tempo->isSameDay($day)),
                ];
            }
        }

        return view('agenda.index', compact('reminders', 'tasks', 'calendarDays', 'month', 'year', 'viewType'));
    }

    private function isReminder($item)
    {
        $name = strtolower($item->kategoriNama->nama ?? '');
        $keterangan = strtolower($item->keterangan ?? '');
        $jenis = strtolower($item->kategoriJenis->nama ?? '');
        
        return str_contains($keterangan, '[pengingat]') ||
               str_contains($name, 'listrik') || str_contains($name, 'air') || 
               str_contains($name, 'internet') || str_contains($name, 'tagihan') || 
               str_contains($name, 'pajak') || str_contains($name, 'asuransi') ||
               str_contains($keterangan, 'listrik') || str_contains($keterangan, 'tagihan') ||
               $jenis == 'tagihan';
    }

    public function create()
    {
        $namaKategori = KategoriNamaTabungan::all();
        $jenisKategori = KategoriJenisTabungan::all();
        return view('agenda.create', compact('namaKategori', 'jenisKategori'));
    }

    /**
     * Store a new agenda item.
     */
    public function store(Request $request)
    {
        // 1. Handle Simple Agenda Popup (High-Density UI)
        if ($request->has('nama_manual')) {
            $request->validate([
                'nama_manual' => 'required|string|max:255',
                'jatuh_tempo' => 'required|date',
                'details' => 'nullable|array'
            ]);

            $defaultName = KategoriNamaTabungan::first()->id ?? 1;
            $defaultJenis = KategoriJenisTabungan::first()->id ?? 1;
            
            $jatuhTempo = $request->jatuh_tempo;
            if ($request->type_selection === 'reminder' && $request->has('waktu')) {
                $jatuhTempo .= ' ' . $request->waktu;
            }

            $detailsStr = ($request->type_selection === 'reminder') 
                ? "[Pengingat] " . $request->nama_manual 
                : $request->nama_manual;

            $timelineData = [];
            if ($request->type_selection === 'task' && $request->has('details')) {
                $timelineData = collect($request->details)
                    ->filter()
                    ->map(fn($item) => ['text' => $item, 'done' => false])
                    ->values()
                    ->toArray();
                
                if (count($timelineData) > 0) {
                    $detailsStr .= "\nTimeline:\n- " . implode("\n- ", array_filter($request->details));
                }
            }

            PlannedTransaction::create([
                'nama' => $defaultName,
                'jenis' => $defaultJenis,
                'nominal' => 0,
                'keterangan' => $detailsStr,
                'jatuh_tempo' => $jatuhTempo,
                'is_priority' => $request->input('is_priority') == '1',
                'timeline_data' => count($timelineData) > 0 ? $timelineData : null,
                'user_id' => auth()->id(),
                'family_id' => auth()->user()->family_id,
                'status' => 'pending'
            ]);

            return redirect()->route('agenda.index')->with('success', 'Agenda berhasil disimpan.');
        }

        // 2. Handle Legacy Full Form
        $validated = $request->validate([
            'nama' => 'required|exists:kategori_nama_tabungans,id',
            'jenis' => 'required|exists:kategori_jenis_tabungans,id',
            'nominal' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
            'jatuh_tempo' => 'required|date',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['family_id'] = auth()->user()->family_id;
        
        PlannedTransaction::create($validated);

        return redirect()->route('agenda.index')->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function toggleTimeline(Request $request, $id)
    {
        $agenda = PlannedTransaction::findOrFail($id);
        $index = $request->input('index');
        $data = $agenda->timeline_data;
        
        if (isset($data[$index])) {
            $data[$index]['done'] = !$data[$index]['done'];
            
            // Auto completion logic: if ALL are done, task becomes done
            $allDone = collect($data)->every(fn($i) => $i['done']);
            $updateData = ['timeline_data' => $data];
            if ($allDone) {
                $updateData['status'] = 'done';
            }
            
            $agenda->update($updateData);
        }
        
        return response()->json(['success' => true, 'task_done' => $agenda->status === 'done']);
    }

    public function edit($id)
    {
        $agenda = PlannedTransaction::findOrFail($id);
        $namaKategori = KategoriNamaTabungan::all();
        $jenisKategori = KategoriJenisTabungan::all();
        return view('agenda.edit', ['plannedTransaction' => $agenda, 'namaKategori' => $namaKategori, 'jenisKategori' => $jenisKategori]);
    }

    public function update(Request $request, $id)
    {
        $agenda = PlannedTransaction::findOrFail($id);
        
        // Handle inline update from Edit Mode (only keterangan)
        if ($request->has('keterangan') && !$request->has('jatuh_tempo')) {
            $agenda->update(['keterangan' => $request->keterangan]);
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'nama' => 'required|exists:kategori_nama_tabungans,id',
            'jenis' => 'required|exists:kategori_jenis_tabungans,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'jatuh_tempo' => 'required|date',
        ]);

        $agenda->update($validated);

        return redirect()->route('agenda.index')->with('success', 'Agenda berhasil diupdate.');
    }

    public function destroy($id)
    {
        $agenda = PlannedTransaction::findOrFail($id);
        $agenda->delete();
        return redirect()->route('agenda.index')->with('success', 'Agenda berhasil dihapus.');
    }

    public function complete(Request $request, $id)
    {
        $agenda = PlannedTransaction::findOrFail($id);
        $request->validate(['tanggal_peristiwa' => 'required|date']);

        Tabungan::create([
            'nama' => $agenda->nama,
            'jenis' => $agenda->jenis,
            'nominal' => $agenda->nominal,
            'keterangan' => $agenda->keterangan . " (Realisasi dari agenda)",
            'user_id' => auth()->id(),
            'family_id' => auth()->user()->family_id,
            'created_at' => $request->tanggal_peristiwa,
            'updated_at' => $request->tanggal_peristiwa,
            'status' => 'verified'
        ]);

        $agenda->update([
            'status' => 'done',
            'user_id' => auth()->id(),
            'tanggal_peristiwa' => $request->tanggal_peristiwa,
        ]);

        return redirect()->route('agenda.index')->with('success', 'Agenda berhasil diselesaikan!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json(['success' => false]);
        
        PlannedTransaction::whereIn('id', $ids)->delete();
        
        return response()->json(['success' => true]);
    }

    private function getDayName($date)
    {
        $names = [
            'Monday' => 'Sen',
            'Tuesday' => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday' => 'Kam',
            'Friday' => 'Jum',
            'Saturday' => 'Sab',
            'Sunday' => 'Min',
        ];

        return $names[$date->format('l')] ?? $date->format('D');
    }
}
