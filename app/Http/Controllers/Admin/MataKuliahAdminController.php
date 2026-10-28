<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class MataKuliahAdminController extends Controller
{
    /**
     * Tampilkan daftar mata kuliah berdasarkan semester dan filter lainnya.
     */
    public function index(Request $request): View
    {
        $selectedSemester = $request->query('semester');

        $query = MataKuliah::with('dosenPengampu')
            ->orderBy('semester')
            ->orderBy('kode_matkul');

        if (! empty($selectedSemester)) {
            $query->where('semester', (int) $selectedSemester);
        }

        $data = $query->get();
        $dosenList = User::whereIn('role', ['dosen', 'evaluator', 'admin'])
            ->orderBy('nama_user')
            ->get();

        return view('admin.matakuliah.index', compact('data', 'selectedSemester', 'dosenList'));
    }

    /**
     * Tampilkan formulir pembuatan mata kuliah baru.
     */
    public function create(): View
    {
        $dosenList = User::whereIn('role', ['dosen', 'evaluator', 'admin'])
            ->orderBy('nama_user')
            ->get();

        return view('admin.matakuliah.create', compact('dosenList'));
    }

    /**
     * Simpan mata kuliah baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_matkul' => ['required', 'string', 'max:50', 'unique:mata_kuliah,kode_matkul'],
            'nama_matkul' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'integer', 'min:1', 'max:8'],
            'sks' => ['required', 'integer', 'min:1', 'max:8'],
            'dosen_id' => ['nullable', 'exists:users,id'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode_matkul.unique' => 'Kode mata kuliah sudah ada, gunakan kode yang lain.',
            'semester.min' => 'Semester minimal 1.',
            'semester.max' => 'Semester maksimal 8.',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        MataKuliah::create($validated);

        Alert::toast('Mata kuliah berhasil ditambahkan.', 'success');

        return redirect()->route('matakuliah.index', ['semester' => $validated['semester']]);
    }

    /**
     * Tampilkan formulir edit mata kuliah.
     */
    public function edit(MataKuliah $matakuliah): View
    {
        $dosenList = User::whereIn('role', ['dosen', 'evaluator', 'admin'])
            ->orderBy('nama_user')
            ->get();

        return view('admin.matakuliah.edit', compact('matakuliah', 'dosenList'));
    }

    /**
     * Perbarui data mata kuliah di database.
     */
    public function update(Request $request, MataKuliah $matakuliah): RedirectResponse
    {
        $validated = $request->validate([
            'kode_matkul' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mata_kuliah', 'kode_matkul')->ignore($matakuliah->id),
            ],
            'nama_matkul' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'integer', 'min:1', 'max:8'],
            'sks' => ['required', 'integer', 'min:1', 'max:8'],
            'dosen_id' => ['nullable', 'exists:users,id'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode_matkul.unique' => 'Kode mata kuliah sudah ada, gunakan kode yang lain.',
            'semester.min' => 'Semester minimal 1.',
            'semester.max' => 'Semester maksimal 8.',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $matakuliah->update($validated);

        Alert::toast('Mata kuliah berhasil diperbarui.', 'success');

        return redirect()->route('matakuliah.index', ['semester' => $validated['semester']]);
    }

    /**
     * Hapus mata kuliah.
     */
    public function destroy(MataKuliah $matakuliah): RedirectResponse
    {
        $matakuliah->delete();

        Alert::toast('Mata kuliah berhasil dihapus.', 'success');

        return redirect()->route('matakuliah.index');
    }
}
