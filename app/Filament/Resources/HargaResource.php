<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HargaResource\Pages;
use App\Models\Harga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class HargaResource extends Resource
{
    protected static ?string $model = Harga::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Harga';
    protected static ?string $modelLabel = 'Harga';
    protected static ?string $pluralModelLabel = 'Harga';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->unique(ignoreRecord: true, table: 'hargas', column: 'nama')
                            ->validationMessages([
                                'unique' => 'Nama sudah terdaftar, silakan gunakan nama lain.',
                            ])
                    ->maxLength(255),

                Forms\Components\Select::make('tingkat')
                    ->label('Tingkat')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('harga')
                    ->label('Harga')
                    ->required()
                    ->prefix('Rp')
                    // ->numeric()
                    ->inputMode('decimal')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        // Format input saat user mengetik
                        $number = preg_replace('/[^0-9]/', '', $state);
                        $set('harga', $number === '' ? null : number_format((int) $number, 0, '', '.'));
                    })
                    ->formatStateUsing(function ($state, $record) {
                        // Saat edit, pastikan tetap tampil format ribuan dengan titik (IDR)
                        if ($state === null || $state === '') return null;
                        $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                        return number_format((int) $number, 0, '', '.');
                    })
                    ->dehydrateStateUsing(function ($state) {
                        // Hapus karakter non-digit sebelum simpan ke DB
                        return preg_replace('/[^0-9]/', '', $state);
                    }),

                Forms\Components\Select::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'pendidikan' => 'Pendidikan',
                        'instansi' => 'Instansi',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state === null || $state === '') return '-';
                        $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                        return 'Rp ' . number_format((int) $number, 0, '', '.');
                    }),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tidak ada bulk actions
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHargas::route('/'),
            'create' => Pages\CreateHarga::route('/create'),
            'edit' => Pages\EditHarga::route('/{record}/edit'),
        ];
    }
}
