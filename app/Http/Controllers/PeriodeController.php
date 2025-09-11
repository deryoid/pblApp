<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periode;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class PeriodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $data = Periode::orderByRaw("CASE WHEN status_periode = 'Aktif' THEN 0 ELSE 1 END")
                   ->latest() // urutkan by created_at DESC
                   ->get();
            return view('admin.periode.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.periode.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'periode'        => 'required|string|max:150|unique:periode,periode', // ganti 'periode' jika nama tabel Anda jamak
        'status_periode' => ['required', Rule::in(['Aktif','Tidak Aktif'])],
        ], [
        'periode.unique' => 'Periode sudah ada, tidak boleh duplikat.',
        ]);

        // Default ke "Aktif" jika tidak dikirim dari form
        $validated['status_periode'] = $validated['status_periode'] ?? 'Aktif';

        Periode::create($validated);
        Alert::toast('Periode berhasil ditambahkan.', 'success');
        return redirect()->route('periode.index');
    }
   

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        $periode = Periode::where('uuid', $uuid)->firstOrFail();
        return view('admin.periode.edit', compact('periode'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $periode = Periode::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
        'periode' => [
            'sometimes','required','string','max:150',
            Rule::unique('periode','periode')->ignore($periode->id ?? null), // sesuaikan nama tabel
        ],
            'status_periode' => ['sometimes','required', Rule::in(['Aktif','Tidak Aktif'])],
        ], [
            'periode.unique' => 'Periode sudah ada, tidak boleh duplikat.',
        ]);

        $periode->update($validated);
        Alert::toast('Periode berhasil diubah.', 'success');
        return redirect()->route('periode.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $periode = Periode::where('uuid', $uuid)->firstOrFail();
        $periode->delete();
        
        Alert::toast('Periode berhasil dihapus.', 'success');
        return redirect()->route('periode.index');
    }
}
