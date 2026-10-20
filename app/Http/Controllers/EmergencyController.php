<?php

namespace App\Http\Controllers;

use App\Models\Emergency;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'location_lat' => 'nullable|string',
            'location_lng' => 'nullable|string',
        ]);

        Emergency::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'location_lat' => $request->location_lat,
            'location_lng' => $request->location_lng,
            'status' => 'ACTIVE',
        ]);

        return back()->with('success', 'Panggilan Darurat (SOS) telah dikirim ke perangkat Desa!');
    }

    public function resolve(Emergency $emergency)
    {
        $emergency->update(['status' => 'RESOLVED']);

        return back()->with('success', 'Status darurat berhasil diselesaikan.');
    }
}
