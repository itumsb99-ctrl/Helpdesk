<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        return view('units.index', [
            'units' => Unit::orderBy('name')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:units,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Unit::create($data + ['is_active' => true]);

        return redirect()->route('units.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('units', 'name')->ignore($unit->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $unit->update($data);

        return redirect()->route('units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    public function toggle(Unit $unit)
    {
        $unit->update(['is_active' => !$unit->is_active]);

        return back()->with('success', 'Status unit berhasil diubah.');
    }
}
