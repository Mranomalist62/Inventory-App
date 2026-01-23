<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\StockLedgerService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Pos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'POS';
    protected static ?string $title = 'Point of Sale';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static string $view = 'filament.pages.pos';

    /** Cart line format: id, sku, name, price, qty, subtotal */
    public array $cart = [];

    /** Ringkasan & pembayaran */
    public array $payment = [
        'subtotal' => 0,
        'discount' => 0,
        'tax' => 0,
        'grand_total' => 0,
        'paid' => 0,
        'change' => 0,
        'method' => 'cash',
    ];

    public ?string $sku = '';

    /** >>> daftar rekomendasi produk saat mengetik */
    public array $suggestions = [];

    /** Modal state untuk simulasi pembayaran */
    public bool $showQrisModal = false;
    public bool $showDebitModal = false;
    public string $debitLastFour = '';
    public bool $qrisConfirmed = false;
    public bool $debitConfirmed = false;

    /** Tampilkan menu hanya untuk owner|cashier */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('owner') || $user->hasRole('cashier'));
    }

    /** >>> Hitung ulang kembalian setiap kali payment.paid berubah */
    public function updatedPaymentPaid($value): void
    {
        $paid = (int) $value;
        $grand = (int) $this->payment['grand_total'];
        $this->payment['change'] = $paid > 0 ? max(0, $paid - $grand) : 0;
    }

    /* ===================== SUGGESTIONS (SMART SEARCH) ===================== */

    /** * Implementasi Pencarian Mirip Google
     * Menggunakan Tokenizing & Scoring 
     */
    public function updatedSku($value): void
    {
        $value = trim((string) $value);

        // Kalau kosong / kurang dari 2 huruf, reset saran
        if ($value === '' || mb_strlen($value) < 2) {
            $this->suggestions = [];
            return;
        }

        // 1. CEK EXACT MATCH (Prioritas Utama: Scan Barcode/SKU)
        // Jika input persis sama dengan SKU/Barcode, langsung tampilkan 1 hasil saja.
        $exactProduct = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($value) {
                $q->where('sku', $value)->orWhere('barcode', $value);
            })->first();

        if ($exactProduct) {
            $this->suggestions = [$this->formatSuggestion($exactProduct)];
            return;
        }

        // 2. TOKENIZING & BROAD SEARCH
        // Pecah kalimat "Kopi Susu" menjadi ["Kopi", "Susu"]
        $keywords = explode(' ', $value);

        // Ambil kandidat produk dari database (Limit 50 agar ringan)
        // Kita cari yang mengandung SKU parsial ATAU salah satu kata dari nama
        $candidates = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($keywords, $value) {
                // Group 1: Cari berdasarkan SKU/Barcode (mirip)
                $query->where('sku', 'like', "%{$value}%")
                    ->orWhere('barcode', 'like', "%{$value}%");

                // Group 2: Cari berdasarkan Nama (mengandung salah satu kata)
                $query->orWhere(function ($subQuery) use ($keywords) {
                    foreach ($keywords as $word) {
                        if (strlen($word) > 1) { // abaikan kata 1 huruf
                            $subQuery->orWhere('name', 'like', "%{$word}%");
                        }
                    }
                });
            })
            ->limit(50) // Ambil pool data secukupnya untuk diproses di PHP
            ->get();

        // 3. RANKING / SCORING (Fuzzy Logic di PHP)
        $results = $candidates->map(function ($product) use ($value, $keywords) {
            $score = 0;
            $nameLower = strtolower($product->name);
            $valueLower = strtolower($value);

            // A. Full match string (nilai tertinggi)
            if (str_contains($nameLower, $valueLower)) {
                $score += 100;
            }

            // B. Kata per kata match (menangani urutan acak)
            $matchedCount = 0;
            foreach ($keywords as $word) {
                if (strlen($word) > 1 && str_contains($nameLower, strtolower($word))) {
                    $score += 10;
                    $matchedCount++;
                }
            }

            // Bonus jika semua kata ada
            if ($matchedCount === count($keywords) && count($keywords) > 1) {
                $score += 50;
            }

            // C. Barcode/SKU match partial
            if (str_contains(strtolower($product->sku), $valueLower))
                $score += 80;
            if (str_contains(strtolower($product->barcode), $valueLower))
                $score += 80;

            // Simpan score sementara di object
            $product->search_score = $score;
            return $product;
        })
            // Urutkan berdasarkan Score tertinggi
            ->sortByDesc('search_score')
            ->take(8); // Ambil 8 teratas

        // 4. Formatting Output
        $this->suggestions = $results->map(function (Product $p) {
            return $this->formatSuggestion($p);
        })->values()->toArray();
    }

    /** Helper Format Array untuk Suggestion */
    protected function formatSuggestion(Product $p): array
    {
        return [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'price' => (int) $p->sell_price,
            'stock' => (int) $p->qty_on_hand,
            // Tambahan untuk tampilan UI yang lebih cantik (opsional)
            'price_fmt' => number_format($p->sell_price, 0, ',', '.'),
        ];
    }

    /** >>> klik salah satu rekomendasi */
    public function selectSuggestion(int $productId): void
    {
        $product = Product::query()
            ->where('is_active', true)
            ->find($productId);

        if (!$product) {
            Notification::make()->title('Produk tidak ditemukan/aktif')->danger()->send();
            return;
        }

        $this->addProductToCart($product);
        $this->sku = '';
        $this->suggestions = [];
    }

    /* ===================== CART OPS ===================== */

    /** Tambah produk via scan/ketik SKU/Barcode/Nama (Enter) */
    public function addProduct(): void
    {
        $value = trim((string) $this->sku);

        if ($value === '') {
            return;
        }

        // Coba cari exact match dulu
        $product = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($value) {
                $q->where('sku', $value)
                    ->orWhere('barcode', $value)
                    ->orWhere('name', $value);
            })
            ->first();

        // Jika tidak ketemu exact, cek apakah ada di top suggestion?
        // (Opsional: Jika user tekan enter tapi belum pilih suggestion, 
        // kita bisa ambil suggestion pertama secara otomatis)
        if (!$product && !empty($this->suggestions)) {
            $firstId = $this->suggestions[0]['id'];
            $product = Product::find($firstId);
        }

        if (!$product || !$product->is_active) {
            Notification::make()->title('Produk tidak ditemukan/aktif')->danger()->send();
            return;
        }

        $this->addProductToCart($product);

        $this->sku = '';
        $this->suggestions = [];   // >>> tutup dropdown setelah berhasil
    }

    /** >>> helper dipakai addProduct() & selectSuggestion() */
    protected function addProductToCart(Product $product): void
    {
        // Jika sudah ada di cart → +1
        foreach ($this->cart as &$line) {
            if ($line['id'] === $product->id) {
                $line['qty'] += 1;
                $line['subtotal'] = $line['qty'] * $line['price'];
                $this->recalculate();
                return;
            }
        }
        unset($line);

        // Tambah line baru
        $this->cart[] = [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => (int) $product->sell_price,
            'tax' => (int) $product->tax_rate,
            'qty' => 1,
            'subtotal' => (int) $product->sell_price,
        ];

        $this->recalculate();
    }

    public function incQty(int $index): void
    {
        if (!isset($this->cart[$index]))
            return;
        $this->cart[$index]['qty'] += 1;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['qty'] * $this->cart[$index]['price'];
        $this->recalculate();
    }

    public function decQty(int $index): void
    {
        if (!isset($this->cart[$index]))
            return;
        $this->cart[$index]['qty'] = max(1, (int) $this->cart[$index]['qty'] - 1);
        $this->cart[$index]['subtotal'] = $this->cart[$index]['qty'] * $this->cart[$index]['price'];
        $this->recalculate();
    }

    public function updateQty(int $index, $qty): void
    {
        if (!isset($this->cart[$index]))
            return;
        $q = max(1, (int) $qty);
        $this->cart[$index]['qty'] = $q;
        $this->cart[$index]['subtotal'] = $q * $this->cart[$index]['price'];
        $this->recalculate();
    }

    public function removeLine(int $index): void
    {
        if (isset($this->cart[$index])) {
            array_splice($this->cart, $index, 1);
            $this->recalculate();
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->payment = [
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 0,
            'paid' => 0,
            'change' => 0,
            'method' => 'cash',
        ];
        $this->sku = '';
        $this->suggestions = [];
    }

    /* ===================== PERHITUNGAN ===================== */

    public function recalculate(): void
    {
        $subtotal = collect($this->cart)->sum(fn($i) => (int) $i['subtotal']);
        $discount = 0;
        $subtotal = $subtotal - ($subtotal * ($discount/100));
        $tax = $subtotal * ($this->cart[0]['tax'] / 100);

        $grand = max(0, $subtotal + $tax);

        $this->payment['subtotal'] = $subtotal;
        $this->payment['tax'] = $tax;
        $this->payment['discount'] = $discount;
        $this->payment['grand_total'] = $grand;

        $paid = (int) $this->payment['paid'];
        $this->payment['change'] = $paid > 0 ? max(0, $paid - $grand) : 0;
    }

    /* ===================== SIMPAN TRANSAKSI ===================== */

    public function saveSale(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Cart kosong!')->danger()->send();
            return;
        }

        if ((int) $this->payment['paid'] < (int) $this->payment['grand_total']) {
            Notification::make()->title('Uang kurang dari total')->danger()->send();
            return;
        }

        try {
            $sale = DB::transaction(function () {
                $sale = Sale::create([
                    'code' => makeRunningNumber('pos'), // Pastikan helper ini ada
                    'date' => now(),
                    'cashier_id' => Auth::id(),
                    'customer_id' => null,
                    'subtotal' => (int) $this->payment['subtotal'],
                    'tax' => (int) $this->payment['tax'],
                    'discount' => (int) $this->payment['discount'],
                    'rounding' => 0,
                    'grand_total' => (int) $this->payment['grand_total'],
                    'paid' => (int) $this->payment['paid'],
                    'change' => (int) $this->payment['change'],
                    'payment_method' => (string) ($this->payment['method'] ?? 'cash'),
                    'status' => 'paid',
                ]);

                foreach ($this->cart as $item) {
                    $qty = (int) $item['qty'];
                    $price = (int) $item['price'];
                    $tax = (int) $item['tax'];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => (int) $item['id'],
                        'qty' => $qty,
                        'unit_price' => $price,
                        'discount' => 0,
                        'tax' => $tax,
                        'line_total' => $qty * $price,
                    ]);

                    // stok keluar + catat ledger
                    StockLedgerService::moveOut((int) $item['id'], $qty, 'sale', $sale->id);
                }

                return $sale;
            });

            $code = $sale->code;
            $change = $this->payment['change'];
            $this->clearCart();

            Notification::make()
                ->title('Transaksi berhasil')
                ->body("Kode: {$code} | Kembali: " . number_format($change, 0, ',', '.'))
                ->success()
                ->send();

            // Redirect ke print receipt (sesuaikan route name anda)
            redirect()->route('receipt.print', $sale);

        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Gagal menyimpan transaksi')->danger()->send();
        }
    }

    /* ===================== MODAL SIMULASI PEMBAYARAN ===================== */

    /** Buka modal QRIS */
    public function openQrisModal(): void
    {
        $this->qrisConfirmed = false;
        $this->showQrisModal = true;
    }

    /** Tutup modal QRIS */
    public function closeQrisModal(): void
    {
        $this->showQrisModal = false;
    }

    /** Konfirmasi pembayaran QRIS berhasil */
    public function confirmQrisPayment(): void
    {
        $this->qrisConfirmed = true;
        $this->payment['paid'] = $this->payment['grand_total'];
        $this->payment['change'] = 0;
        $this->showQrisModal = false;

        Notification::make()
            ->title('Pembayaran QRIS dikonfirmasi')
            ->success()
            ->send();
    }

    /** Buka modal Debit */
    public function openDebitModal(): void
    {
        $this->debitConfirmed = false;
        $this->debitLastFour = '';
        $this->showDebitModal = true;
    }

    /** Tutup modal Debit */
    public function closeDebitModal(): void
    {
        $this->showDebitModal = false;
    }

    /** Konfirmasi pembayaran Debit berhasil */
    public function confirmDebitPayment(): void
    {
        if (strlen($this->debitLastFour) !== 4 || !ctype_digit($this->debitLastFour)) {
            Notification::make()
                ->title('Masukkan 4 digit terakhir kartu')
                ->danger()
                ->send();
            return;
        }

        $this->debitConfirmed = true;
        $this->payment['paid'] = $this->payment['grand_total'];
        $this->payment['change'] = 0;
        $this->showDebitModal = false;

        Notification::make()
            ->title('Pembayaran Debit dikonfirmasi')
            ->body('Kartu berakhiran: ****' . $this->debitLastFour)
            ->success()
            ->send();
    }

    /** Reset konfirmasi saat ganti metode pembayaran */
    public function updatedPaymentMethod($value): void
    {
        $this->qrisConfirmed = false;
        $this->debitConfirmed = false;
        $this->payment['paid'] = 0;
        $this->payment['change'] = 0;
    }
}