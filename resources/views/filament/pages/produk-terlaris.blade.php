<x-filament::page>
    <div class="space-y-4">
        <div class="flex gap-3">
            <x-filament::input.wrapper>
                <x-filament::input type="date" wire:model.defer="startDate" />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper>
                <x-filament::input type="date" wire:model.defer="endDate" />
            </x-filament::input.wrapper>
            <x-filament::button wire:click="generate" color="primary">Tampilkan</x-filament::button>
            <x-filament::button wire:click="exportPdf" color="success" icon="heroicon-o-document-arrow-down">Export
                PDF</x-filament::button>
        </div>

        <x-filament::card>
            <div class="text-lg font-semibold mb-3">Top 20 Produk Terlaris</div>
            <table class="w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">Produk</th>
                        <th class="p-2 text-center">Jumlah Terjual</th>
                        <th class="p-2 text-right">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $row)
                        <tr class="border-t">
                            <td class="p-2">{{ $row->product->name ?? '-' }}</td>
                            <td class="p-2 text-center">{{ $row->total_qty }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center p-3 text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::card>
    </div>
</x-filament::page>