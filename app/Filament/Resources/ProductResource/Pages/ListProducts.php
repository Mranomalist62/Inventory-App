<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_barcode')
                ->label('Cetak Semua Barcode')
                ->color('success')
                ->action(function () {
                    session()->forget('selected_product_ids');
                    return redirect()->route('barcode.print');
                }),
            Actions\CreateAction::make(),
        ];
    }
}
