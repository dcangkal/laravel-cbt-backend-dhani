<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSoalRequest;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->paginate(5);
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
}
