<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Soal;

class SoalImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Soal([
            'pertanyaan' => $row['pertanyaan'],
            'kategori' => $row['kategori'],
            'jawaban_a' => $row['jawaban_a'],
            'jawaban_b' => $row['jawaban_b'],
            'jawaban_c' => $row['jawaban_c'],
            'jawaban_d' => $row['jawaban_d'],
            'kunci' => $row['kunci'],
        ]);
    }
}
