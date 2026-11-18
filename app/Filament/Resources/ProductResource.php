<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Manajemen Master';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')->label('Kode Produk')->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('barcode')->label('Barcode')->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('name')->label('Nama Produk')->required()->maxLength(255),
            Select::make('category_id')->label('Kategori')
                ->relationship('categoryMaster', 'name')->searchable()->preload()->required(),
            Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
            TextInput::make('price')->label('Harga')->numeric()->prefix('Rp')->minValue(0)->required(),
            TextInput::make('stock')->label('Stok')->numeric()->integer()->minValue(0)->default(0)->required(),
            FileUpload::make('image')->label('Gambar Produk')->image()->disk('public')->directory('products')->imageEditor(),
            Toggle::make('is_active')->label('Produk Aktif')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Gambar')->disk('public')->square(),
                TextColumn::make('code')->label('Kode')->searchable()->toggleable(),
                TextColumn::make('name')->label('Nama Produk')->searchable()->sortable(),
                TextColumn::make('categoryMaster.name')->label('Kategori')->sortable(),
                TextColumn::make('price')->label('Harga')->money('IDR')->sortable(),
                TextColumn::make('stock')->label('Stok')->badge()
                    ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'success')->sortable(),
                TextColumn::make('is_active')->label('Status')->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('category')->label('Filter Kategori')->relationship('categoryMaster', 'name'),
                TernaryFilter::make('is_active')->label('Status Produk')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif'),
                TernaryFilter::make('low_stock')->label('Stok Menipis')
                    ->queries(
                        true: fn ($query) => $query->where('stock', '<=', 5),
                        false: fn ($query) => $query->where('stock', '>', 5),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
