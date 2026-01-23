<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model; // <--- TAMBAHAN

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
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('barcode')
                    ->label('Barcode')
                    ->unique(ignoreRecord: true)
                    ->nullable(),

                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->columnSpanFull(),

                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable(),

                TextInput::make('unit')
                    ->label('Satuan')
                    ->default('pcs')
                    ->maxLength(20),

                TextInput::make('cost_price')
                    ->label('Harga Modal')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('sell_price')
                    ->label('Harga Jual')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('tax_rate')
                    ->label('Pajak')
                    ->numeric()
                    ->suffix('%')
                    ->default(0),

                TextInput::make('min_stock')
                    ->label('Min. Stok')
                    ->numeric()
                    ->default(0),

                TextInput::make('qty_on_hand')
                    ->label('Stok Sekarang')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])
            ->columns(4);
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
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                TextColumn::make('sell_price')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn($state) => idr($state))
                    ->sortable(),

                TextColumn::make('qty_on_hand')
                    ->label('Stok')
                    ->badge()
                    ->color(fn($state, $record) => $state <= $record->min_stock ? 'danger' : 'success')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('print_barcode')
                        ->label('Cetak Barcode')
                        ->icon('heroicon-o-printer')
                        // ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            // Ambil ID dari record yang dipilih
                            session()->forget('selected_product_ids');
                            $ids = $records->pluck('id')->toArray();
                            // Simpan ke session
                            session(['selected_product_ids' => $ids]);
                            // Redirect ke halaman Blade custom
                            return redirect()->route('barcode.print');
                        }),
                ]),
            ]);
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
