<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Village;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        return redirect()->route('developments.index');
    }

    public function create()
    {
        return redirect()->route('developments.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|digits:4',
            'type' => 'required|in:PENDAPATAN,BELANJA,PEMBIAYAAN',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'amount_budget' => 'required|numeric|min:0',
            'amount_realized' => 'required|numeric|min:0',
        ]);

        $data['village_id'] = auth()->user()->village_id ?? Village::first()->id ?? null;
        if (! $data['village_id']) {
            return back()->with('error', 'Silakan set up data Desa terlebih dahulu.');
        }

        Budget::create($data);

        return redirect()->route('developments.index')->with('success', 'Anggaran APBDes berhasil ditambahkan.');
    }

    public function edit(Budget $budget)
    {
        return redirect()->route('developments.index');
    }

    public function update(Request $request, Budget $budget)
    {
        $data = $request->validate([
            'year' => 'required|digits:4',
            'type' => 'required|in:PENDAPATAN,BELANJA,PEMBIAYAAN',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'amount_budget' => 'required|numeric|min:0',
            'amount_realized' => 'required|numeric|min:0',
        ]);

        $budget->update($data);

        return redirect()->route('developments.index')->with('success', 'Anggaran APBDes berhasil diupdate.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();

        return redirect()->route('developments.index')->with('success', 'Anggaran APBDes berhasil dihapus.');
    }
}
