<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SoalResource;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianSoalList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function createUjian(Request $request)
    {
        $soalAngka = Soal::where('kategori', 'Numeric')->inRandomOrder()->limit(20)->get();
        $soalVerbal = Soal::where('kategori', 'Verbal')->inRandomOrder()->limit(20)->get();
        $soalLogika = Soal::where('kategori', 'Logika')->inRandomOrder()->limit(20)->get();

        $ujian = Ujian::create([
            'user_id' => $request->user()->id,
        ]);

        foreach ($soalAngka as $soal) {
            UjianSoalList::create([
                'ujian_id' => $ujian->id,
                'soal_id' => $soal->id,
            ]);
        }

        foreach ($soalVerbal as $soal) {
            UjianSoalList::create([
                'ujian_id' => $ujian->id,
                'soal_id' => $soal->id,
            ]);
        }
        foreach ($soalLogika as $soal) {
            UjianSoalList::create([
                'ujian_id' => $ujian->id,
                'soal_id' => $soal->id,
            ]);
        }

        return response()->json([
            'message' => 'Ujian berhasil dibuat',
            'data' => $ujian,
        ]);
    }

    public function getListSoalByKategori(Request $request)
    {
        $ujian = Ujian::where('user_id', $request->user()->id)->first();

        if (!$ujian) {
            return response()->json([
                'message' => 'ujian tidak ditemukan',
                'data' => [],
            ]);
        }

        $ujianSoalList = UjianSoalList::where('ujian_id', $ujian->id)->get();
        $soalIds = $ujianSoalList->pluck('soal_id');
        $soal = Soal::whereIn('id', $soalIds)->where('kategori', $request->kategori)->get();

        $timer = $ujian->timer_angka;
        if ($request->kategori == 'Verbal') {
            $timer = $ujian->timer_verbal;
        } else if ($request->kategori == 'Logika') {
            $timer = $ujian->timer_logika;
        }

        return response()->json([
            'message' => 'Berhasil mendapatkan soal',
            'timer' => $timer,
            'data' => SoalResource::collection($soal),
        ]);
    }

    public function jawabSoal(Request $request)
    {
        $validatedData = $request->validate([
            'soal_id' => 'required',
            'jawaban' => 'required',
        ]);

        $ujian = Ujian::where('user_id', $request->user()->id)->first();

        if (!$ujian) {
            return response()->json([
                'message' => 'ujian tidak ditemukan',
                'data' => [],
            ]);
        }

        $ujianSoalList = UjianSoalList::where('ujian_id', $ujian->id)->where('soal_id', $validatedData['soal_id'])->first();
        $soal = Soal::where('id', $validatedData['soal_id'])->first();

        if ($soal->kunci == $validatedData['jawaban']) {
            $ujianSoalList->kebenaran = true;
            $ujianSoalList->save();
        } else {
            $ujianSoalList->kebenaran = false;
            $ujianSoalList->save();
        }

        return response()->json([
            'message' => 'Berhasil simpan jawaban',
            'jawaban' => $ujianSoalList->kebenaran,
        ]);
    }

    public function hitungNilaiUjianByKategori(Request $request)
    {
        $kategori = $request->kategori;
        $ujian = Ujian::where('user_id', $request->user()->id)->first();

        if (!$ujian) {
            return response()->json([
                'message' => 'ujian tidak ditemukan',
                'data' => [],
            ]);
        }

        $ujianSoalList = UjianSoalList::where('ujian_id', $ujian->id)->get();
        $ujianSoalList = $ujianSoalList->filter(function ($value, $key) use ($kategori) {
            return $value->soal->kategori == $kategori;
        });

        $totalBenar = $ujianSoalList->where('kebenaran', true)->count();
        $totalSalah = $ujianSoalList->where('kebenaran', false)->count();
        $totalSoal = $ujianSoalList->count();
        $nilai = ($totalBenar / $totalSoal) * 100;

        $kategori_field = 'nilai_verbal';
        $status_field = 'status_verbal';
        $timer_field = 'timer_verbal';
        if ($kategori == 'Numeric') {
            $kategori_field = 'nilai_angka';
            $status_field = 'status_angka';
            $timer_field = 'timer_angka';
        } else if ($kategori == 'Logika') {
            $kategori_field = 'nilai_logika';
            $status_field = 'status_logika';
            $timer_field = 'timer_logika';
        }

        // update field hasil
        // rumus = (nilai_angka + nilai_verbal + nilai_logika) / 3
        // jika hasil >=70 maka Lulus else Tidak Lulus

        $rumus = ($ujian->nilai_angka + $ujian->nilai_verbal + $ujian->nilai_logika) / 3;
        $hasil_field = 'hasil';
        if ($rumus > 70) {
            $hasil = 'Lulus';
        } else {
            $hasil = 'Tidak Lulus';
        }

        $ujian->update([
            $kategori_field => $nilai,
            $status_field => 'done',
            $timer_field => 0,
            $hasil_field => $hasil,
        ]);

        return response()->json([
            'message' => 'Berhasil mendapatkan nilai',
            'nilai' => $nilai,
            'hasil' => $hasil,
            'benar' => $totalBenar,
            'salah' => $totalSalah,
            'soal' => $totalSoal,
        ]);
    }
}
