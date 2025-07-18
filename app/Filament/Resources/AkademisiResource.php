<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkademisiResource\Pages;
use App\Filament\Resources\AkademisiResource\RelationManagers;
use App\Models\Akademisi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Support\Facades\Auth;

use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class AkademisiResource extends Resource
{
    // Permission handled by policy/Filament Shield
    protected static ?string $model = Akademisi::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Akademisi';

    protected static ?string $modelLabel = 'Akademisi';

    protected static ?string $pluralModelLabel = 'Akademisi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('nomor')
                    ->label('Nomor Telepon')
                    ->required()
                    ->maxLength(255)
                    ->prefix('+62')
                    ->placeholder('contoh: 812345678')
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

                Forms\Components\TextInput::make('jurusan')
                    ->label('Jurusan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('asal_kampus')
                    ->label('Asal Kampus')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TagsInput::make('minat')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('asal_kampus')
                    ->label('Asal Kampus')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('minat')
                    ->label('Minat')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state) && !empty($state)) {
                            return collect($state)->take(3)->implode(', ') .
                                   (count($state) > 3 ? ' +' . (count($state) - 3) . ' lainnya' : '');
                        }
                        if (is_string($state) && !empty($state)) {
                            // Jika tersimpan sebagai string JSON, parse dulu
                            $decoded = json_decode($state, true);
                            if (is_array($decoded) && !empty($decoded)) {
                                return collect($decoded)->take(3)->implode(', ') .
                                       (count($decoded) > 3 ? ' +' . (count($decoded) - 3) . ' lainnya' : '');
                            }
                            return $state;
                        }
                        return '-';
                    })
                    ->tooltip(function ($record) {
                        $minat = $record->minat;
                        if (is_array($minat) && !empty($minat)) {
                            return implode(', ', $minat);
                        }
                        if (is_string($minat) && !empty($minat)) {
                            $decoded = json_decode($minat, true);
                            if (is_array($decoded) && !empty($decoded)) {
                                return implode(', ', $decoded);
                            }
                            return $minat;
                        }
                        return null;
                    }),

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
            ->filters([
                Tables\Filters\SelectFilter::make('jurusan')
                    ->label('Filter Jurusan')
                    ->options(function () {
                        return \App\Models\Akademisi::distinct('jurusan')
                            ->pluck('jurusan', 'jurusan')
                            ->toArray();
                    }),

                Tables\Filters\SelectFilter::make('asal_kampus')
                    ->label('Filter Kampus')
                    ->options(function () {
                        return \App\Models\Akademisi::distinct('asal_kampus')
                            ->pluck('asal_kampus', 'asal_kampus')
                            ->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
