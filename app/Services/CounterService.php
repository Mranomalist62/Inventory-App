<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CounterService
{
    public static function next(string $key): int
    {
        return DB::transaction(function () use ($key) {
            $row = DB::table('counters')->where('key', $key)->lockForUpdate()->first();
            if (!$row) {
                DB::table('counters')->insert([
                    'key' => $key,
                    'value' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return 1;
            }
            DB::table('counters')->where('key', $key)->update([
                'value' => $row->value + 1,
                'updated_at' => now(),
            ]);
            return $row->value + 1;
        });
    }
}
