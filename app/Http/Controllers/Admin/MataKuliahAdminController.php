<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MataKuliahTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\User;
use App\Services\MataKuliahImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
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
            'dosen_ids' => ['nullable', 'array'],
            'dosen_ids.*' => ['exists:users,id'],
            'dosen_id' => ['nullable', 'exists:users,id'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode_matkul.unique' => 'Kode mata kuliah sudah ada, gunakan kode yang lain.',
            'semester.min' => 'Semester minimal 1.',
            'semester.max' => 'Semester maksimal 8.',
        ]);

        $dosenIds = $request->input('dosen_ids', []);
        if (empty($dosenIds) && $request->filled('dosen_id')) {
            $dosenIds = [(int) $request->input('dosen_id')];
        }

        $validated['dosen_id'] = ! empty($dosenIds) ? $dosenIds[0] : null;
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $mataKuliah = MataKuliah::create($validated);
        $mataKuliah->dosenPengampu()->sync($dosenIds);

        Alert::toast('Mata kuliah berhasil ditambahkan.', 'success');

        return redirect()->route('matakuliah.index', ['semester' => $validated['semester']]);
    }

    /**
     * Tampilkan formulir edit mata kuliah.
     */
    public function edit(MataKuliah $matakuliah): View
    {
        $matakuliah->load('dosenPengampu');
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
            'dosen_ids' => ['nullable', 'array'],
            'dosen_ids.*' => ['exists:users,id'],
            'dosen_id' => ['nullable', 'exists:users,id'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode_matkul.unique' => 'Kode mata kuliah sudah ada, gunakan kode yang lain.',
            'semester.min' => 'Semester minimal 1.',
            'semester.max' => 'Semester maksimal 8.',
        ]);

        $dosenIds = $request->input('dosen_ids', []);
        if (empty($dosenIds) && $request->filled('dosen_id')) {
            $dosenIds = [(int) $request->input('dosen_id')];
        }

        $validated['dosen_id'] = ! empty($dosenIds) ? $dosenIds[0] : null;
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $matakuliah->update($validated);
        $matakuliah->dosenPengampu()->sync($dosenIds);

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

    /**
     * Tampilkan formulir impor data mata kuliah.
     */
    public function importForm(): View
    {
        return view('admin.matakuliah.import');
    }

    /**
     * Proses unggah berkas impor data mata kuliah.
     */
    public function import(Request $request, MataKuliahImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => [
                'required', 'file',
                'mimes:xlsx,xls,csv,txt',
                'max:5120',
            ],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $ext = $file->getClientOriginalExtension();

        try {
            $result = $importService->importFile($path, $ext);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $stats = $result['stats'];
        $errors = $result['errors'];

        session()->flash('import_stats', $stats);

        if (! empty($errors)) {
            session()->flash('import_errors', $errors);
            Alert::toast("Import selesai: dibuat: {$stats['created_matkul']}, diperbarui: {$stats['updated_matkul']}, dosen disinkronkan: {$stats['synced_dosen']} (ada error)", 'warning');
        } else {
            Alert::toast("Import selesai: dibuat: {$stats['created_matkul']}, diperbarui: {$stats['updated_matkul']}, dosen disinkronkan: {$stats['synced_dosen']}", 'success');
        }

        return redirect()->route('matakuliah.import.form');
    }

    /**
     * Unduh template impor Excel (.xlsx) untuk Mata Kuliah.
     */
    public function templateExcel()
    {
        return Excel::download(new MataKuliahTemplateExport, 'template-mata-kuliah.xlsx');
    }

    /**
     * Unduh template impor CSV untuk Mata Kuliah.
     */
    public function templateCsv()
    {
        return Excel::download(new MataKuliahTemplateExport, 'template-mata-kuliah.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
