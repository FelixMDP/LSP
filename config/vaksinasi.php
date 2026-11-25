<?php

return [
    'biaya' => env('VAKSINASI_BIAYA') !== null && env('VAKSINASI_BIAYA') !== ''
        ? (int) env('VAKSINASI_BIAYA')
        : null,
];
