<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NominalPresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presets = [
            ['urutan' => 1, 'label' => '5.000',   'nominal' => 5000],
            ['urutan' => 2, 'label' => '10.000',  'nominal' => 10000],
            ['urutan' => 3, 'label' => '20.000',  'nominal' => 20000],
            ['urutan' => 4, 'label' => '50.000',  'nominal' => 50000],
            ['urutan' => 5, 'label' => '100.000', 'nominal' => 100000],
        ];

        foreach ($presets as $preset) {
            \App\Models\NominalPreset::firstOrCreate(
                ['nominal' => $preset['nominal']],
                $preset + ['is_active' => true],
            );
        }
    }
}
