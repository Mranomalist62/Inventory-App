<?php

if (! function_exists('makeRunningNumber')) {
    function makeRunningNumber(string $prefix): string {
        $ym = now()->format('Ym');
        $seq = \App\Services\CounterService::next("$prefix-$ym");
        return sprintf('%s-%s-%04d', strtoupper($prefix), $ym, $seq); 
        // Contoh hasil: POS-202511-0001
    }
}

if (! function_exists('idr')) {
    function idr(int|float $value): string {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
