<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Support\Facades\Auth;

class FamilyController extends Controller
{
    public function myFamily()
    {
        $user = Auth::user();

        $family = Family::with(['members', 'rt.rw.village'])
            ->where('kk_number', $user->kk_number)
            ->first();

        $members = $family ? $family->members : collect();

        return view('dashboard.families.my_family', compact('family', 'members', 'user'));
    }
}
