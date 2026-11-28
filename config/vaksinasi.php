<?php

return [
    'biaya' => env('VAKSINASI_BIAYA') !== null && env('VAKSINASI_BIAYA') !== ''
        ? (int) env('VAKSINASI_BIAYA')
        : null,
    'rekening_demo' => [
        'bank' => 'Bank Simulasi',
        'nomor' => '8241 6037 9152 4086',
        'atas_nama' => 'VaksinSehat',
    ],
];
