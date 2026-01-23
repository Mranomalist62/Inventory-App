<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;


class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('name')->label('Nama')->required()->maxLength(120),
                Select::make('parent_id')->label('Induk')->relationship('parent','name')->searchable()->nullable(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('parent.name')->label('Induk'),
                TextColumn::make('created_at')->dateTime('d M Y H:i'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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
