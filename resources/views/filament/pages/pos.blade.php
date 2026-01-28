<x-filament::page>
    <div class="space-y-4 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-6 min-h-screen"
        style="background-color: #fafafa;">


        {{-- Search Bar --}}
        <div class="relative">
            <form wire:submit.prevent="addProduct" class="flex gap-3">
                <div
                    class="flex-1 relative flex items-center bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 dark:focus-within:ring-primary-400">
                    <div class="flex items-center justify-center w-12 px-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="sku"
                        placeholder="Scan barcode atau ketik nama produk..." autocomplete="off" autofocus
                        class="flex-1 pl-2 pr-4 py-3 text-base bg-transparent border-none outline-none placeholder-gray-400 dark:placeholder-gray-500 text-gray-900 dark:text-white">
                </div>
                <button type="button" wire:click="openBarcodeModal"
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white font-medium rounded-lg transition-colors">
                    Scan Barcode
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white font-medium rounded-lg transition-colors">
                    + Tambah
                </button>
            </form>

            {{-- Suggestions Dropdown --}}
            @if (!empty($suggestions))
                <div
                    class="absolute top-full left-0 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto">
                    @foreach ($suggestions as $item)
                        <div wire:click="selectSuggestion({{ $item['id'] }})"
                            class="px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0 flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 flex gap-2 mt-0.5">
                                    <span class="font-mono">{{ $item['sku'] }}</span>
                                    <span
                                        class="{{ $item['stock'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        Stok: {{ $item['stock'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="font-semibold text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div wire:click="$set('suggestions', [])" class="fixed inset-0 z-40"></div>
            @endif
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Cart (2 columns) --}}
            <div class="lg:col-span-2">
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Cart Header --}}
                    <div
                        class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            Keranjang ({{ count($cart) }} item)
                        </h3>
                        @if (count($cart) > 0)
                            <button wire:click="clearCart" class="text-sm text-red-600 dark:text-red-400 hover:underline">
                                Kosongkan
                            </button>
                        @endif
                    </div>

                    {{-- Cart Items --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[400px] overflow-y-auto">
                        @forelse ($cart as $index => $item)
                                            <div class="px-4 py-3 flex items-center gap-4">
                                                {{-- Product Info --}}
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium text-gray-900 dark:text-white truncate">{{ $item['name'] }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        Rp {{ number_format($item['price'], 0, ',', '.') }}
                                                    </div>
                                                    {{-- DISCOUNT INFO --}}
                                                    @if ($item['discount'] > 0)
                                                        <div class="text-xs mt-0.5">
                                                            <span class="text-red-600 dark:text-red-400">Diskon: {{ $item['discount'] }}%</span>
                                                            @if ($item['tax_rate'] > 0)
                                                                <span class="ml-2 text-blue-600 dark:text-blue-400">Pajak:
                                                                    {{ $item['tax_rate'] }}%</span>
                                                            @endif
                                                        </div>
                                                    @elseif($item['tax_rate'] > 0)
                                                        <div class="text-xs mt-0.5 text-blue-600 dark:text-blue-400">
                                                            Pajak: {{ $item['tax_rate'] }}%
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Quantity --}}
                                                <div class="flex items-center gap-1">
                                                    <button wire:click="decQty({{ $index }})"
                                                        class="w-8 h-8 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold">
                                                        −
                                                    </button>
                                                    <input type="number" wire:change="updateQty({{ $index }}, $event.target.value)"
                                                        value="{{ $item['qty'] }}" min="1"
                                                        class="w-14 h-8 text-center text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                    <button wire:click="incQty({{ $index }})"
                                                        class="w-8 h-8 flex items-center justify-center rounded bg-primary-600 dark:bg-primary-500 hover:bg-primary-700 dark:hover:bg-primary-600 text-white font-bold">
                                                        +
                                                    </button>
                                                </div>

                                                {{-- Discount Button --}}
                                                <button wire:click="openDiscountModal({{ $index }})" class="ml-2 px-2 py-1 text-xs font-medium rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{ $item['discount'] > 0
                            ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-300 dark:border-red-700'
                            : 'text-gray-600 dark:text-gray-400' }}">
                                                    {{ $item['discount'] > 0 ? $item['discount'] . '%' : 'Diskon' }}
                                                </button>

                                                {{-- Line Total Details --}}
                                                <div class="w-32 text-right">
                                                    <div class="font-semibold text-gray-900 dark:text-white">
                                                        Rp {{ number_format($item['line_total'], 0, ',', '.') }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        @if ($item['discount'] > 0)
                                                            <div>-Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}</div>
                                                        @endif
                                                        @if ($item['tax_rate'] > 0)
                                                            <div>+Rp {{ number_format($item['tax_amount'], 0, ',', '.') }}</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Delete --}}
                                                <button wire:click="removeLine({{ $index }})"
                                                    class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                        fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                        @empty
                            <div class="px-4 py-12 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-16 w-16 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Keranjang kosong</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Payment Panel (1 column) --}}
            <div class="lg:col-span-1">
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Summary --}}
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Pembayaran</h3>
                    </div>

                    <div class="p-4 space-y-4">
                        {{-- Totals --}}
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                                <span class="text-gray-900 dark:text-white">Rp
                                    {{ number_format($payment['subtotal'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Diskon</span>
                                <span class="text-red-600 dark:text-red-400">-Rp
                                    {{ number_format($payment['total_discount'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Pajak</span>
                                <span class="text-gray-900 dark:text-white">+Rp
                                    {{ number_format($payment['total_tax'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="font-semibold text-gray-900 dark:text-white">Total</span>
                                <span class="text-xl font-bold text-primary-600 dark:text-primary-400">Rp
                                    {{ number_format($payment['grand_total'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Metode
                                Pembayaran</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" wire:click="$set('payment.method', 'cash')"
                                    class="py-2 px-3 text-sm font-medium rounded-lg border-2 transition-colors
                                    {{ $payment['method'] === 'cash'
    ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
    : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                    Cash
                                </button>
                                <button type="button" wire:click="$set('payment.method', 'qris')"
                                    class="py-2 px-3 text-sm font-medium rounded-lg border-2 transition-colors
                                    {{ $payment['method'] === 'qris'
    ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400'
    : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                    QRIS
                                </button>
                                <button type="button" wire:click="$set('payment.method', 'debit')"
                                    class="py-2 px-3 text-sm font-medium rounded-lg border-2 transition-colors
                                    {{ $payment['method'] === 'debit'
    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
    : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                    Debit
                                </button>
                            </div>
                        </div>

                        {{-- Cash Input --}}
                        @if ($payment['method'] === 'cash')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jumlah
                                    Bayar</label>
                                <input type="number" wire:model.live="payment.paid" placeholder="0" step="1000"
                                    class="w-full px-3 py-2 text-lg font-semibold text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">

                                <div class="grid grid-cols-3 gap-1 mt-2">
                                    @foreach ([10000, 20000, 50000, 100000, 150000, 200000] as $amount)
                                        <button type="button" wire:click="$set('payment.paid', {{ $amount }})"
                                            class="py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                            {{ number_format($amount / 1000) }}K
                                        </button>
                                    @endforeach
                                </div>
                                <button type="button" wire:click="$set('payment.paid', {{ $payment['grand_total'] }})"
                                    class="w-full mt-2 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg">
                                    Uang Pas
                                </button>
                            </div>
                        @elseif($payment['method'] === 'qris')
                            <div>
                                @if ($qrisConfirmed)
                                    <div
                                        class="py-3 text-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        ✓ QRIS Terkonfirmasi
                                    </div>
                                @else
                                    <button type="button" wire:click="openQrisModal"
                                        class="w-full py-3 text-sm font-medium text-light bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 rounded-lg">
                                        Tampilkan QR Code
                                    </button>
                                @endif
                            </div>
                        @elseif($payment['method'] === 'debit')
                            <div>
                                @if ($debitConfirmed)
                                    <div
                                        class="py-3 text-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        ✓ Debit Terkonfirmasi
                                    </div>
                                @else
                                    <button type="button" wire:click="openDebitModal"
                                        class="w-full py-3 text-sm font-medium text-light bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg">
                                        Proses Kartu Debit
                                    </button>
                                @endif
                            </div>
                        @endif

                        {{-- Change --}}
                        @if ($payment['paid'] > 0 && $payment['method'] === 'cash')
                            <div class="py-3 text-center bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="text-sm text-green-600 dark:text-green-400">Kembalian</div>
                                <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                                    Rp {{ number_format($payment['change'], 0, ',', '.') }}
                                </div>
                            </div>
                        @endif

                        {{-- Process Button --}}
                        <button wire:click="saveSale" @if (count($cart) === 0 || $payment['paid'] < $payment['grand_total']) disabled @endif
                            class="w-full py-3 text-base font-semibold text-light bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 disabled:bg-gray-300 dark:disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg transition-colors">
                            Proses Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QRIS Modal --}}
    @if ($showQrisModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scan QRIS</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total: Rp
                        {{ number_format($payment['grand_total'], 0, ',', '.') }}
                    </p>
                </div>

                {{-- QR Code --}}
                <div class="flex justify-center mb-4">
                    <div class="p-4 bg-white rounded-lg border-2 border-gray-200">
                        <svg viewBox="0 0 200 200" class="w-40 h-40">
                            <rect fill="white" width="200" height="200" />
                            <rect fill="black" x="10" y="10" width="50" height="50" />
                            <rect fill="white" x="17" y="17" width="36" height="36" />
                            <rect fill="black" x="24" y="24" width="22" height="22" />
                            <rect fill="black" x="140" y="10" width="50" height="50" />
                            <rect fill="white" x="147" y="17" width="36" height="36" />
                            <rect fill="black" x="154" y="24" width="22" height="22" />
                            <rect fill="black" x="10" y="140" width="50" height="50" />
                            <rect fill="white" x="17" y="147" width="36" height="36" />
                            <rect fill="black" x="24" y="154" width="22" height="22" />
                            <rect fill="black" x="70" y="10" width="10" height="10" />
                            <rect fill="black" x="90" y="10" width="10" height="10" />
                            <rect fill="black" x="70" y="70" width="10" height="10" />
                            <rect fill="black" x="90" y="70" width="10" height="10" />
                            <rect fill="black" x="110" y="70" width="10" height="10" />
                            <rect fill="black" x="130" y="70" width="10" height="10" />
                            <rect fill="black" x="150" y="70" width="10" height="10" />
                            <rect fill="black" x="70" y="90" width="10" height="10" />
                            <rect fill="black" x="110" y="90" width="10" height="10" />
                            <rect fill="black" x="70" y="110" width="10" height="10" />
                            <rect fill="black" x="90" y="110" width="10" height="10" />
                            <rect fill="black" x="130" y="110" width="10" height="10" />
                            <rect fill="black" x="150" y="110" width="10" height="10" />
                            <rect fill="black" x="70" y="130" width="10" height="10" />
                            <rect fill="black" x="90" y="130" width="10" height="10" />
                            <rect fill="black" x="110" y="130" width="10" height="10" />
                            <rect fill="black" x="130" y="160" width="10" height="10" />
                            <rect fill="black" x="150" y="160" width="10" height="10" />
                            <rect fill="black" x="170" y="160" width="10" height="10" />
                            <rect fill="black" x="150" y="180" width="10" height="10" />
                        </svg>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button wire:click="closeQrisModal"
                        class="flex-1 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        Batal
                    </button>
                    <button wire:click="confirmQrisPayment"
                        class="flex-1 py-2 text-sm font-medium text-light bg-purple-600 hover:bg-purple-700 rounded-lg">
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Debit Modal --}}
    @if ($showDebitModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pembayaran Debit</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total: Rp
                        {{ number_format($payment['grand_total'], 0, ',', '.') }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-center">4 Digit
                        Terakhir Kartu</label>
                    <input type="text" wire:model="debitLastFour" maxlength="4" placeholder="••••"
                        class="w-full px-4 py-3 text-2xl text-center font-mono tracking-widest border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                        autocomplete="off">
                </div>

                <div class="flex gap-2">
                    <button wire:click="closeDebitModal"
                        class="flex-1 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        Batal
                    </button>
                    <button wire:click="confirmDebitPayment"
                        class="flex-1 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Discount Modal --}}
    @if ($showDiscountModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Atur Diskon</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $cart[$editingCartIndex]['name'] ?? '' }}
                    </p>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-center gap-3">
                        <button wire:click="$set('editingDiscount', 0)"
                            class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                            0%
                        </button>
                        <button wire:click="$set('editingDiscount', 5)"
                            class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                            5%
                        </button>
                        <button wire:click="$set('editingDiscount', 10)"
                            class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                            10%
                        </button>
                        <button wire:click="$set('editingDiscount', 20)"
                            class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                            20%
                        </button>
                    </div>

                    <div class="mt-4">
                        <input type="number" wire:model="editingDiscount" min="0" max="100" step="0.5"
                            class="w-full mt-2 px-4 py-2 text-lg text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            placeholder="0">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
                            Masukkan persentase diskon
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button wire:click="closeDiscountModal"
                        class="flex-1 py-2 text-sm font-medium text-gray-200 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        Batal
                    </button>
                    <button wire:click="applyDiscount"
                        class="flex-1 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    @endif
    {{-- Barcode Modal --}}
    @if ($showBarcodeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
                <h3 class="text-lg font-semibold text-center mb-3">Scan Barcode</h3>

                {{-- QR Scanner --}}
                <div id="qris-scanner"></div>

                <div class="flex gap-2 mt-4">
                    <button wire:click="closeBarcodeModal" class="flex-1 mt-2 py-3 text-sm dark:bg-gray-700 rounded">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script>
        let qrScanner = null;

        window.addEventListener('barcode-modal-opened', () => {
            setTimeout(() => {
                if (qrScanner) return;

                const el = document.getElementById('qris-scanner');
                if (!el) return;

                qrScanner = new Html5Qrcode("qris-scanner");

                qrScanner.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: 250
                },
                    (decodedText) => {
                        Livewire.dispatch('barcode-scanned', {
                            value: decodedText
                        });

                        qrScanner.stop().then(() => qrScanner = null);
                    }
                );
            }, 50); // TUNGGU DOM
        });

        window.addEventListener('barcode-modal-closed', () => {
            if (qrScanner) {
                qrScanner.stop().then(() => qrScanner = null);
            }
        });
    </script>


</x-filament::page>