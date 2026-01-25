<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'barcode', 'name', 'category_id', 'unit',
        'cost_price', 'sell_price', 'discount', 'tax_rate',
        'min_stock', 'qty_on_hand', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tax_rate'  => 'decimal:2',
        'discount'  => 'float',
        'cost_price' => 'integer',
        'sell_price' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // Generate SKU if not provided
            if (empty($product->sku)) {
                $product->sku = self::generateSKU();
            }

            // Generate EAN-13 barcode if not provided
            if (empty($product->barcode)) {
                $product->barcode = self::generateEAN13();
            }
        });

        static::updating(function ($product) {
            // Generate SKU if it's being set to empty/null
            if (empty($product->sku)) {
                $product->sku = self::generateSKU();
            }
        });
    }

    private static function generateSKU(): string
    {
        $prefix = 'PRD';
        $year = date('y');
        $month = date('m');

        do {
            $random = strtoupper(Str::random(4));
            $sku = "{$prefix}{$year}{$month}{$random}";
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     *
     * EAN-13 format: 12 digits + 1 check digit
     *
     * @return string
     */
    public static function generateEAN13(): string
    {
        $prefix = '89'; // Indonesia country code for EAN
        $company = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $product = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

        $first12 = $prefix . $company . $product;
        $first12 = substr($first12, 0, 12);

        $checkDigit = self::calculateEAN13CheckDigit($first12);

        return $first12 . $checkDigit;
    }

    /**
     * Calculate EAN-13 check digit
     */
    private static function calculateEAN13CheckDigit(string $first12Digits): string
    {
        $digits = str_split($first12Digits);
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$digits[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return (string)$checkDigit;
    }

    /**
     * Calculate the selling price after discount
     */
    public function getDiscountedPriceAttribute(): int
    {
        if ($this->discount > 0) {
            $discountAmount = $this->sell_price * ($this->discount / 100);
            return (int) round($this->sell_price - $discountAmount);
        }

        return $this->sell_price;
    }

    /**
     * Get discount amount in Rupiah
     */
    public function getDiscountAmountAttribute(): int
    {
        return (int) round($this->sell_price * ($this->discount / 100));
    }

    /**
     * Get formatted Rupiah price
     */
    public function getFormattedSellPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->sell_price, 0, ',', '.');
    }

    /**
     * Get formatted discounted price
     */
    public function getFormattedDiscountedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->discounted_price, 0, ',', '.');
    }

    /**
     * Get formatted cost price
     */
    public function getFormattedCostPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->cost_price, 0, ',', '.');
    }

    /**
     * Validate if barcode is a valid EAN-13
     */
    public static function isValidEAN13(string $barcode): bool
    {
        if (strlen($barcode) !== 13 || !ctype_digit($barcode)) {
            return false;
        }

        $first12 = substr($barcode, 0, 12);
        $expectedCheckDigit = self::calculateEAN13CheckDigit($first12);
        $actualCheckDigit = substr($barcode, -1);

        return $expectedCheckDigit === $actualCheckDigit;
    }

    /**
     * Validate discount is between 0-100
     */
    public function setDiscountAttribute($value)
    {
        // Ensure discount is between 0 and 100
        $value = max(0, min(100, (float) $value));
        $this->attributes['discount'] = $value;
    }

    /**
     * Ensure prices are stored as integers (no decimals)
     */
    public function setCostPriceAttribute($value)
    {
        $this->attributes['cost_price'] = (int) round($value);
    }

    public function setSellPriceAttribute($value)
    {
        $this->attributes['sell_price'] = (int) round($value);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
