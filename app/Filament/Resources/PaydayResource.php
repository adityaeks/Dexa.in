<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaydayResource\Pages;
use App\Filament\Resources\PaydayResource\RelationManagers;
use App\Models\Payday;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class PaydayResource extends Resource
{
    protected static ?string $model = Payday::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Pemasukan Owner';
    protected static ?string $modelLabel = 'Pemasukan';
    protected static ?string $pluralModelLabel = 'Pemasukan Owner';
    protected static ?string $navigationGroup = 'Manajemen Gaji';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('order_id')
                    ->label('Order (Nota)')
                    ->relationship('order', 'tr_code')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        $order = \App\Models\Order::find($state);
                        $set('tr_code', $order?->tr_code ?? '');
                    }),
                TextInput::make('tr_code')
                    ->label('Kode Transaksi (tr_code)')
                    ->disabled()
                    ->dehydrated(),
                Select::make('akademisi_id')
                    ->label('Akademisi')
                    ->relationship('akademisi', 'name')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        $akademisi = \App\Models\Akademisi::find($state);
                        $set('akademisi_name', $akademisi?->name ?? '');
                    }),
                TextInput::make('akademisi_name')
                    ->label('Nama Akademisi')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('price_base')
                    ->label('Harga Dasar')
                    ->numeric()
                    ->required(),
                TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.tr_code')->label('Nota'),
                TextColumn::make('tr_code')->label('Kode Transaksi'),
                TextColumn::make('akademisi.name')->label('Akademisi'),
                TextColumn::make('akademisi_name')->label('Nama Akademisi'),
                TextColumn::make('price_base')->label('Harga Dasar')->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
                TextColumn::make('price')->label('Harga')->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                DeleteBulkAction::make(),
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
            'index' => Pages\ListPaydays::route('/'),
            'create' => Pages\CreatePayday::route('/create'),
            'edit' => Pages\EditPayday::route('/{record}/edit'),
        ];
    }
}
