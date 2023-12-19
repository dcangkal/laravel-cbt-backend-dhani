<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSoalRequest;
use App\Imports\SoalImport;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        // $soals = \App\Models\Soal::paginate(10);
        $soals = DB::table('soals')
            ->when($request->input('pertanyaan'), function ($query, $name) {
                return $query->where('pertanyaan', 'like', '%' . $name . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(20);
        return view('pages.soals.index', compact('soals'));
    }

    public function create()
    {
        return view('pages.soals.create');
    }

    public function store(StoreSoalRequest $request)
    {
        $data = $request->all();
        \App\Models\Soal::create($data);
        return redirect()->route('soal.index')->with('success', 'soal berhasil dibuat');
    }

    public function edit($id)
    {
        $soal = \App\Models\Soal::findOrFail($id);
        return view('pages.soals.edit', compact('soal'));
    }

    public function update(StoreSoalRequest $request, Soal $soal)
    {
        $data = $request->validated();
        $soal->update($data);
        return redirect()->route('soal.index')->with('success', 'soal berhasil diubah');
    }

    public function destroy(Soal $soal)
    {
        $soal->delete();
        return redirect()->route('soal.index')->with('success', 'soal berhasil dihapus');
    }

    public function import(Request $request)
    {
        $file = $request->file('file');
        Excel::import(new SoalImport(), $file);
        return redirect()->back()->with('success', 'Soal berhasil diimpor.');

        // validasi
        // $this->validate($request, [
        //     'file' => 'required|mimes:csv,xls,xlsx'
        // ]);

        // menangkap file excel
        // $file = $request->file('file');

        // membuat nama file unik
        // $nama_file = rand() . $file->getClientOriginalName();

        // upload ke folder file_siswa di dalam folder public
        // $file->move('file_soal', $nama_file);

        // import data
        // Excel::import(new SoalImport, public_path('/file_soal/' . $nama_file));

        // notifikasi dengan session
        // Session::flash('sukses','Data Siswa Berhasil Diimport!');

        // alihkan halaman kembali
        // return redirect()->back()->with('success', 'Soal berhasil diimpor.');
    }
}
