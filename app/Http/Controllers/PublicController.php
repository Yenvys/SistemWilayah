<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Budget;
use App\Models\Development;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\Village;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Get the primary village for use in all portal pages.
     */
    private function getVillage(): ?Village
    {
        return Village::first();
    }

    /**
     * Portal landing page — shows village overview with real data.
     */
    public function index()
    {
        $village = $this->getVillage();

        $totalResidents = Resident::count();
        $totalFamilies = Family::count();
        $totalRw = Rw::count();
        $totalRt = Rt::count();

        $announcements = Announcement::latest()->take(5)->get();

        $ongoingDevelopments = Development::where('status', 'ONGOING')->count();
        $completedDevelopments = Development::where('status', 'COMPLETED')->count();

        return view('public.index', compact(
            'village',
            'totalResidents', 'totalFamilies', 'totalRw', 'totalRt',
            'announcements',
            'ongoingDevelopments', 'completedDevelopments'
        ));
    }

    public function apbdes(Request $request)
    {
        $village = $this->getVillage();
        $year = $request->input('year', date('Y'));
        $budgets = Budget::where('year', $year)->get();

        $pendapatan = $budgets->where('type', 'PENDAPATAN');
        $belanja = $budgets->where('type', 'BELANJA');
        $pembiayaan = $budgets->where('type', 'PEMBIAYAAN');

        $totalPendapatan = $pendapatan->sum('amount_budget');
        $totalBelanja = $belanja->sum('amount_budget');
        $totalPembiayaan = $pembiayaan->sum('amount_budget');

        $years = Budget::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($years->isEmpty()) {
            $years = collect([date('Y')]);
        }

        return view('public.apbdes', compact(
            'village', 'year', 'budgets', 'pendapatan', 'belanja', 'pembiayaan',
            'totalPendapatan', 'totalBelanja', 'totalPembiayaan', 'years'
        ));
    }

    public function pembangunan()
    {
        $village = $this->getVillage();
        $developments = Development::latest('start_date')->get();

        return view('public.pembangunan', compact('village', 'developments'));
    }

    public function statistik()
    {
        $village = $this->getVillage();

        $totalResidents = Resident::count();
        $maleCount = Resident::where('gender', 'LAKI_LAKI')->count();
        $femaleCount = Resident::where('gender', 'PEREMPUAN')->count();

        // Agregasi Pendidikan
        $educationStats = Resident::select('education', \DB::raw('count(*) as total'))
            ->groupBy('education')
            ->get();

        // Agregasi Pekerjaan (Top 10)
        $occupationStats = Resident::select('occupation', \DB::raw('count(*) as total'))
            ->groupBy('occupation')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Agregasi Usia (Generasi)
        $ageStats = [
            'Balita (0-5)' => 0,
            'Anak-anak (6-12)' => 0,
            'Remaja (13-17)' => 0,
            'Dewasa (18-59)' => 0,
            'Lansia (60+)' => 0,
        ];

        $residents = Resident::select('birth_date')->get();
        foreach ($residents as $resident) {
            if (! $resident->birth_date) {
                continue;
            }

            $age = $resident->birth_date->age;
            if ($age <= 5) {
                $ageStats['Balita (0-5)']++;
            } elseif ($age <= 12) {
                $ageStats['Anak-anak (6-12)']++;
            } elseif ($age <= 17) {
                $ageStats['Remaja (13-17)']++;
            } elseif ($age <= 59) {
                $ageStats['Dewasa (18-59)']++;
            } else {
                $ageStats['Lansia (60+)']++;
            }
        }

        return view('public.statistik', compact(
            'village', 'totalResidents', 'maleCount', 'femaleCount',
            'educationStats', 'occupationStats', 'ageStats'
        ));
    }
}
