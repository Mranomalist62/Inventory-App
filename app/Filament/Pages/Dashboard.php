<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\Pos; // Pastikan import halaman POS

class Dashboard extends BaseDashboard
{
    // Hapus 'return' karena fungsi ini bertipe void
    public function mount(): void
    {
        $user = Auth::user();

        // Cek apakah user ada & punya role cashier
        if ($user && $user->hasRole('cashier')) {
            // Langsung redirect tanpa return value
            $this->redirect(Pos::getUrl());
            return; // Hentikan eksekusi script di sini
        }

        // Jalankan mount bawaan untuk user selain cashier (Owner, Admin, dll)
        // Cek dulu apakah parent punya method mount (biasanya tidak ada di BaseDashboard, tapi aman untuk dikosongkan jika tidak perlu)
        // parent::mount(); 
    }
}