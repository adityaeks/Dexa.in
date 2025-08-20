<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkademisiResource\Pages;
use App\Models\Akademisi;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class AkademisiResource extends Resource
{
    // Permission handled by policy/Filament Shield
    protected static ?string $model = Akademisi::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Akademisi';

    protected static ?string $modelLabel = 'Akademisi';

    protected static ?string $pluralModelLabel = 'Akademisi';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('nomor')
                    ->label('Nomor Telepon')
                    ->required()
                    ->maxLength(255)
                    ->prefix('+62')
                    ->placeholder('812345678')
                    ->dehydrateStateUsing(function ($state) {
                        // Saat menyimpan, pastikan ada +62
                        if (!empty($state) && !str_starts_with($state, '+62')) {
                            $nomor = ltrim($state, '+0');
                            return '+62' . $nomor;
                        }
                        return $state;
                    })
                    ->formatStateUsing(function ($state) {
                        // Saat menampilkan (edit), hilangkan +62
                        if (!empty($state) && str_starts_with($state, '+62')) {
                            return substr($state, 3);
                        }
                        return $state;
                    }),

                TextInput::make('jurusan')
                    ->label('Jurusan')
                    ->required()
                    ->maxLength(255),

                TextInput::make('asal_kampus')
                    ->label('Asal Kampus')
                    ->required()
                    ->maxLength(255),

                TextInput::make('rekening')
                    ->label('Rekening')
                    ->placeholder('Bank - A/N - No rekening')
                    ->maxLength(255),
                TagsInput::make('minat')
                    ->label('Minat')
                    ->separator(',')
                    ->placeholder('Ketik minat dan tekan Enter')
                    ->helperText('Masukkan minat akademisi, pisahkan dengan Enter atau koma'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('nomor')
                    ->label('Nomor Telepon')
                    ->searchable(),
                TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->searchable(),
                TextColumn::make('asal_kampus')
                    ->label('Asal Kampus')
                    ->searchable(),
                TextColumn::make('rekening')
                    ->label('Rekening')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jurusan')
                    ->label('Filter Jurusan')
                    ->options(function () {
                        return Akademisi::distinct('jurusan')
                            ->pluck('jurusan', 'jurusan')
                            ->toArray();
                    }),

                SelectFilter::make('asal_kampus')
                    ->label('Filter Kampus')
                    ->options(function () {
                        return Akademisi::distinct('asal_kampus')
                            ->pluck('asal_kampus', 'asal_kampus')
                            ->toArray();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                // Tidak ada bulk actions - tidak ada selection checkbox
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
            'index' => Pages\ListAkademisis::route('/'),
            'create' => Pages\CreateAkademisi::route('/create'),
            'edit' => Pages\EditAkademisi::route('/{record}/edit'),
        ];
    }
}
