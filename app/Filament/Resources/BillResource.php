<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Filament\Resources\BillResource\RelationManagers;
use App\Models\Bill;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $pluralModelLabel = 'Tagihan Akademisi';
    protected static ?string $navigationGroup = 'Manajemen Order';
    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tr_code')
                    ->label('Kode Transaksi (Nomer Nota)')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('akademisi_id')
                    ->label('Akademisi')
                    ->relationship('akademisi', 'name')
                    ->required()
                    ->reactive()
                    ->disabled()
                    ->afterStateUpdated(function ($state, $set) {
                        // Otomatis set akademisi_name dari relasi
                        $akademisi = \App\Models\Akademisi::find($state);
                        $set('akademisi_name', $akademisi?->name ?? '');
                    }),
                Forms\Components\TextInput::make('price')
                    ->label('Price Akademisi')
                    ->prefix('Rp')
                    ->numeric()
                    ->disabled()
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        // Format input ke IDR saat user mengetik
                        $number = preg_replace('/[^0-9]/', '', $state);
                        $set('price', $number === '' ? null : number_format((int) $number, 0, '', '.'));
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state === null || $state === '') return null;
                        $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                        return number_format((int) $number, 0, '', '.');
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return preg_replace('/[^0-9]/', '', $state);
                    }),
                Forms\Components\TextInput::make('amt_reff')
                    ->label('Bayar')
                    ->prefix('Rp')
                    ->required()
                    ->live()
                    ->inputMode('decimal')
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Format input ke IDR saat user mengetik (seperti PaymentResource)
                        $number = preg_replace('/[^0-9]/', '', $state);
                        $set('amt_reff', $number === '' ? null : number_format((int) $number, 0, '', '.'));
                        // Update status secara live
                        $amt_reff = (int) $number;
                        $price = (int) preg_replace('/[^0-9]/', '', $get('price'));
                        if ($amt_reff === 0) {
                            $set('status', 'belum');
                        } elseif ($amt_reff < $price) {
                            $set('status', 'dp');
                        } elseif ($amt_reff === $price) {
                            $set('status', 'lunas');
                        }
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state === null || $state === '') return null;
                        $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                        return number_format((int) $number, 0, '', '.');
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return preg_replace('/[^0-9]/', '', $state);
                    }),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'belum' => 'Belum',
                        'dp' => 'DP',
                        'lunas' => 'Lunas',
                    ])
                    ->disabled()
                    ->dehydrated(true)
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(function ($state, $set, $get) {
                        $amt_reff = (int) preg_replace('/[^0-9]/', '', $get('amt_reff'));
                        $price = (int) preg_replace('/[^0-9]/', '', $get('price'));
                        if ($amt_reff === 0) {
                            $set('status', 'belum');
                        } elseif ($amt_reff < $price) {
                            $set('status', 'dp');
                        } elseif ($amt_reff === $price) {
                            $set('status', 'lunas');
                        }
                    })
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $amt_reff = (int) preg_replace('/[^0-9]/', '', $get('amt_reff'));
                        $price = (int) preg_replace('/[^0-9]/', '', $get('price'));
                        if ($amt_reff === 0) {
                            $set('status', 'belum');
                        } elseif ($amt_reff < $price) {
                            $set('status', 'dp');
                        } elseif ($amt_reff === $price) {
                            $set('status', 'lunas');
                        }
                    }),
                Repeater::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran (File)')
                    ->schema([
                        FileUpload::make('file')
                            ->label('File')
                            ->directory('bill-bukti')
                    ])
                    ->addActionLabel('Tambah File')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('seq')->label('No'),
                Tables\Columns\TextColumn::make('tr_code')->label('NOTA'),
                Tables\Columns\TextColumn::make('akademisi.name')->label('Akademisi'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price Akademisi')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
                Tables\Columns\TextColumn::make('belum_dibayar')
                    ->label('Belum Dibayar')
                    ->getStateUsing(fn($record) => (int)$record->price - (int)$record->amt_reff)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
                Tables\Columns\TextColumn::make('amt_reff')
                    ->label('Dibayar')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
                    // ->summarize(
                    //     Tables\Columns\Summarizers\Sum::make()
                    //         ->label('Sum')
                    //         ->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.'))
                    // ),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->color(fn($state) => match($state) {
                    'lunas' => 'success', 'dp' => 'warning', 'belum' => 'danger', default => 'secondary',
                }),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->groups([
                \Filament\Tables\Grouping\Group::make('tr_code')
                    ->label('Nomer Nota')
                    ->getTitleFromRecordUsing(function ($record) {
                        $totalBelumDibayar = \App\Models\Bill::where('tr_code', $record->tr_code)
                            ->get()
                            ->sum(function ($bill) {
                                return (int)$bill->price - (int)$bill->amt_reff;
                            });
                        return $record->tr_code . ' | Belum Dibayar: Rp ' . number_format($totalBelumDibayar, 0, '', '.');
                    }),
            ])
            ->defaultGroup('tr_code');
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
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}
