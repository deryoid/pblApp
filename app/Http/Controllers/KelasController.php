<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $data = Kelas::latest()->get();
            return view('admin.kelas.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.kelas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas' => 'required|string|max:150|unique:kelas,kelas',
        ], [
            'kelas.unique' => 'Nama kelas sudah ada, tidak boleh duplikat.',
        ]);

        Kelas::create($validated);
        Alert::toast('Kelas berhasil ditambahkan.', 'success');
        return redirect()->route('kelas.index');
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
         $kelas = Kelas::where('uuid', $uuid)->firstOrFail();
        return view('admin.kelas.edit', compact('kelas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $kelas = Kelas::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            // pakai sometimes agar bisa update sebagian field jika diperlukan
            'kelas' => [
                'sometimes','required','string','max:150',
                // Jika PK Anda 'id':
                Rule::unique('kelas','kelas')->ignore($kelas->id),
                // Jika Anda ingin ignore berdasarkan 'uuid' (jika tabel unique di uuid), gunakan ini dan hapus baris di atas:
                // Rule::unique('kelas','kelas')->ignore($kelas->uuid, 'uuid'),
            ],
        ], [
            'kelas.unique' => 'Nama kelas sudah ada, tidak boleh duplikat.',
        ]);
        $kelas->update($validated);
        Alert::toast('Kelas berhasil diubah.', 'success');
        return redirect()->route('kelas.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $kelas = Kelas::where('uuid', $uuid)->firstOrFail();
        $kelas->delete();
        
        Alert::toast('Kelas berhasil dihapus.', 'success');
        return redirect()->route('kelas.index');
    }
}
