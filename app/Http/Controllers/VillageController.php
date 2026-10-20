<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VillageController extends Controller
{
    public function index(Request $request)
    {
        $params = array_merge(['tab' => 'desa'], $request->all());
        if ($request->filled('search')) {
            $params['search_village'] = $request->search;
            unset($params['search']);
        }

        return redirect()->route('areas.index', $params);
    }

    public function create()
    {
        return view('dashboard.villages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:villages,code',
        ]);

        $village = Village::create($validated);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE',
            'entity_name' => 'Village',
            'entity_id' => $village->id,
            'details' => ['name' => $village->name],
        ]);

        return redirect()->route('villages.index')->with('success', 'Data desa berhasil ditambahkan.');
    }

    public function edit(Village $village)
    {
        return view('dashboard.villages.edit', compact('village'));
    }

    public function update(Request $request, Village $village)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:villages,code,'.$village->id,
        ]);

        $village->update($validated);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE',
            'entity_name' => 'Village',
            'entity_id' => $village->id,
            'details' => ['name' => $village->name],
        ]);

        return redirect()->route('villages.index')->with('success', 'Data desa berhasil diperbarui.');
    }

    public function destroy(Village $village)
    {
        $name = $village->name;
        $village->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE',
            'entity_name' => 'Village',
            'entity_id' => null,
            'details' => ['name' => $name],
        ]);

        return redirect()->route('villages.index')->with('success', 'Data desa berhasil dihapus.');
    }
}
