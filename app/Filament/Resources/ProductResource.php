<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $slug = 'products';

    // ===========================
    // FORM (Create / Edit)
    // ===========================
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Produk')
                    ->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->default(function () {
                                // Generate and store SKU
                                $generatedSKU = self::generateSKU();
                                session(['generated_sku' => $generatedSKU]);
                                return $generatedSKU;
                            })
                            ->helperText(function (?string $state): string {
                                if (empty($state)) {
                                    // Get SKU - generate new if not in session
                                    $generatedSKU = session('generated_sku', function () {
                                        $newSKU = self::generateSKU();
                                        session(['generated_sku' => $newSKU]);
                                        return $newSKU;
                                    });
                                    return 'Kosongkan untuk auto-generate: ' . $generatedSKU;
                                }
                                $generatedSKU = session('generated_sku');
                                return 'SKU unik produk (auto-generate): ' . $generatedSKU;
                            }),

                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->helperText(function (?string $state): string {
                                if (empty($state)) {
                                    $ean13 = Product::generateEAN13();
                                    return 'Kosongkan untuk auto-generate: ' . $ean13 . ' Pastikan format EAN-13 valid';
                                }
                                return Product::isValidEAN13($state)
                                    ? ' Barcode EAN-13 valid'
                                    : ' Format mungkin tidak valid';
                        })
                        ->rules([
                            'nullable',
                            'string',
                            'size:13',
                            'unique:products,barcode,' . request()->route('record')
                        ])
                        ->dehydrated()
                        ->maxLength(13),
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('unit')
                            ->label('Satuan')
                            ->default('pcs')
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('Harga & Stok')
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('Harga Modal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('sell_price')
                            ->label('Harga Jual')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('discount')
                            ->label('Diskon')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->helperText('Diskon dalam persentase (0-100)'),

                        TextInput::make('tax_rate')
                            ->label('Pajak')
                            ->numeric()
                            ->suffix('%')
                            ->default(10),

                        TextInput::make('min_stock')
                            ->label('Min. Stok')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('qty_on_hand')
                            ->label('Stok Sekarang')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    // ===========================
    // TABLE (List View)
    // ===========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->name),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sell_price')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn($state) => idr($state))
                    ->sortable(),

                TextColumn::make('discount')
                    ->label('Diskon')
                    ->suffix('%')
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('discounted_price')
                    ->label('Harga Setelah Diskon')
                    ->formatStateUsing(fn($state, $record) => idr($record->discounted_price))
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('qty_on_hand')
                    ->label('Stok')
                    ->badge()
                    ->color(fn($state, $record) => $state <= $record->min_stock ? 'danger' : 'success')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean(),

                Tables\Filters\Filter::make('has_discount')
                    ->label('Produk Berdiskon')
                    ->query(fn($query) => $query->where('discount', '>', 0)),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn($query) => $query->whereColumn('qty_on_hand', '<=', 'min_stock')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('print_barcode')
                        ->label('Cetak Barcode')
                        ->icon('heroicon-o-printer')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id')->toArray();
                            session(['selected_product_ids' => $ids]);
                            return redirect()->route('barcode.print');
                        }),

                    BulkAction::make('apply_discount')
                        ->label('Terapkan Diskon')
                        ->icon('heroicon-o-tag')
                        ->form([
                            TextInput::make('discount')
                                ->label('Diskon (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['discount' => $data['discount']]);
                            });
                        }),
                ]),
            ]);
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    /**
     * Generate unique SKU
     */
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
     * Handle form submission for creating product
     */
    public static function createProduct($data): Model
    {
        // Generate SKU if empty
        if (empty($data['sku'])) {
            $data['sku'] = self::generateSKU();
        }

        // Generate barcode if empty
        if (empty($data['barcode'])) {
            $data['barcode'] = Product::generateEAN13();
        }

        return static::getModel()::create($data);
    }

    // ===========================
    // RELATIONS
    // ===========================
    public static function getRelations(): array
    {
        return [];
    }

    // ===========================
    // PAGES
    // ===========================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    // ===========================
    // AUTHORIZATION (ROLE OWNER)
    // ===========================

    /** Hanya owner yang boleh lihat menu Produk di sidebar */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('owner') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('owner') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->hasRole('owner') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('owner') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole('owner') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('owner') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('owner') ?? false;
    }
}
