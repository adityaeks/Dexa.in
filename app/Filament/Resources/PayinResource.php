<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayinResource\{ Pages, Widgets\PayinStatsOverview };
use App\Models\Payin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class PayinResource extends Resource
{
    protected static ?string $model = Payin::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationLabel = 'Pemasukan';
    protected static ?string $modelLabel = 'Pemasukan';
    protected static ?string $pluralModelLabel = 'Payins';
    protected static ?string $navigationGroup = 'Manajemen Dexa.in';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->rows(3)
                    ->placeholder('Masukkan deskripsi pemasukan...'),

                TextInput::make('price')
                    ->label('Harga')
                    ->prefix('Rp')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        // Format input ke IDR saat user mengetik
                        $number = preg_replace('/[^0-9]/', '', $state);
                        if ($number !== '') {
                            $formatted = number_format((int) $number, 0, '', '.');
                            $set('price', $formatted);
                        }
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state === null || $state === '') return null;
                        $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                        return number_format((int) $number, 0, '', '.');
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return preg_replace('/[^0-9]/', '', $state);
                    })
                    ->placeholder('0')
                    ->helperText('Masukkan jumlah dalam rupiah'),

                FileUpload::make('bukti')
                    ->label('Bukti')
                    ->multiple()
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                    ->maxFiles(5)
                    ->directory('payin-bukti')
                    ->visibility('public')
                    ->helperText('Upload bukti pemasukan (JPG, PNG, WebP, PDF). Maksimal 5 file.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('bukti')
                    ->label('Bukti')
                    ->listWithLineBreaks()
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'Tidak ada bukti';
                        return count($state) . ' file';
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            PayinStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayins::route('/'),
            'create' => Pages\CreatePayin::route('/create'),
            'edit' => Pages\EditPayin::route('/{record}/edit'),
        ];
    }
}
