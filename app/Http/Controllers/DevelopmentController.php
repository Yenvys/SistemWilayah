<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Complaint;
use App\Models\Development;
use App\Models\Emergency;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DevelopmentController extends Controller
{
    public function index(Request $request)
    {
        // 1. Data Pembangunan (Box Kanan)
        $developments = Development::with('village')->latest()->paginate(5, ['*'], 'dev_page');
        $devStats = [
            'total' => Development::count(),
            'planned' => Development::where('status', 'PLANNED')->count(),
            'ongoing' => Development::where('status', 'ONGOING')->count(),
            'completed' => Development::where('status', 'COMPLETED')->count(),
            'total_budget' => Development::sum('budget_allocated'),
        ];

        // 2. Data APBDes (Box Kiri)
        $budgets = Budget::with('village')->latest()->paginate(5, ['*'], 'budget_page');
        $budgetStats = [
            'pendapatan_budget' => Budget::where('type', 'PENDAPATAN')->sum('amount_budget'),
            'pendapatan_realized' => Budget::where('type', 'PENDAPATAN')->sum('amount_realized'),
            'belanja_budget' => Budget::where('type', 'BELANJA')->sum('amount_budget'),
            'belanja_realized' => Budget::where('type', 'BELANJA')->sum('amount_realized'),
            'pembiayaan_budget' => Budget::where('type', 'PEMBIAYAAN')->sum('amount_budget'),
            'pembiayaan_realized' => Budget::where('type', 'PEMBIAYAAN')->sum('amount_realized'),
        ];

        // 3. Data Spasial & Peringatan Dini (Box Bawah)
        $complaints = Complaint::whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get();

        $emergencies = Emergency::whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get();

        $developmentLocations = Development::whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->get();

        $unresolvedEmergencies = $emergencies->where('status', 'ACTIVE')->count();
        $recentComplaints = $complaints->count();

        return view('dashboard.developments.index', compact(
            'developments',
            'devStats',
            'budgets',
            'budgetStats',
            'complaints',
            'emergencies',
            'developmentLocations',
            'unresolvedEmergencies',
            'recentComplaints'
        ));
    }

    public function create()
    {
        return redirect()->route('developments.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget_allocated' => 'required|numeric|min:0',
            'source_of_fund' => 'nullable|string|max:100',
            'status' => 'required|in:PLANNED,ONGOING,COMPLETED',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $data['village_id'] = auth()->user()->village_id ?? Village::first()->id ?? null;
        if (! $data['village_id']) {
            return back()->with('error', 'Silakan set up data Desa terlebih dahulu.');
        }

        Development::create($data);

        return redirect()->route('developments.index')->with('success', 'Data pembangunan berhasil ditambahkan.');
    }

    public function edit(Development $development)
    {
        return redirect()->route('developments.index');
    }

    public function update(Request $request, Development $development)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget_allocated' => 'required|numeric|min:0',
            'source_of_fund' => 'nullable|string|max:100',
            'status' => 'required|in:PLANNED,ONGOING,COMPLETED',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $development->update($data);

        return redirect()->route('developments.index')->with('success', 'Data pembangunan berhasil diupdate.');
    }

    public function destroy(Development $development)
    {
        $development->delete();

        return redirect()->route('developments.index')->with('success', 'Data pembangunan berhasil dihapus.');
    }
}
