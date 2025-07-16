<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Harga;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Order';
    protected static ?string $modelLabel = 'Order';
    protected static ?string $pluralModelLabel = 'Order';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('nama')
                            ->label('Nama Jokian')
                            ->required()
                            ->searchable()
                            ->options(fn () =>
                                Harga::all()->mapWithKeys(function($harga) {
                                    $label = $harga->nama;
                                    if (isset($harga->tingkat)) {
                                        $label .= ' - ' . $harga->tingkat;
                                    }
                                    return [$harga->id => $label];
                                })
                            )
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $harga = Harga::find($state);
                                $price = $harga?->harga ?? null;
                                $set('price', $price === 0 ? null : number_format($price, 0, '', '.'));
                                // price_dexain = 10% jika price <= 100000, 20% jika > 100000
                                if ((int)$price <= 100000) {
                                    $dexain = (int) round((int)$price * 0.1);
                                } else {
                                    $dexain = (int) round((int)$price * 0.2);
                                }
                                $akademisi = (int)$price - $dexain;
                                $set('price_dexain', $dexain === 0 ? null : number_format($dexain, 0, '', '.'));
                                $set('price_akademisi', $akademisi === 0 ? null : number_format($akademisi, 0, '', '.'));
                            }),
                        Forms\Components\TextInput::make('nomer_nota')
                            ->label('Nomer Nota')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Auto Generate'),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->required()
                            ->searchable()
                            ->options(fn () => \App\Models\Customer::all()->mapWithKeys(function($customer) {
                                $label = $customer->code . ' - ' . $customer->name;
                                return [$customer->id => $label];
                            }))
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $customer = \App\Models\Customer::find($state);
                                $nomor = $customer?->nomor ?? '';
                                $nomor = ltrim($nomor, '0');
                                $set('contact', $nomor);
                            }),
                        Forms\Components\Select::make('status')
                            ->label('Penegerjaan')
                            ->required()
                            ->options([
                                'Not started' => 'Not started',
                                'Inprogress' => 'Inprogress',
                                'Done' => 'Done',
                            ])
                            ->default('Not started'),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('prioritas')
                            ->label('Prioritas')
                            ->required()
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'urgent' => 'Urgent',
                            ]),
                        Forms\Components\Select::make('status_payment')
                            ->label('Status Payment')
                            ->options([
                                'belum' => 'Belum',
                                'DP' => 'DP',
                                'Lunas' => 'Lunas',
                            ]),
                    ]),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Price Normal')
                            ->required()
                            ->prefix('Rp')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                // price_dexain = 10% jika price <= 100000, 20% jika > 100000
                                $price = (int) preg_replace('/[^0-9]/', '', $state);
                                if ($price <= 100000) {
                                    $dexain = (int) round($price * 0.1);
                                } else {
                                    $dexain = (int) round($price * 0.2);
                                }
                                $akademisi = $price - $dexain;
                                // Format IDR langsung saat onchange
                                $set('price', $price === 0 ? null : number_format($price, 0, '', '.'));
                                $set('price_dexain', $dexain === 0 ? null : number_format($dexain, 0, '', '.'));
                                $set('price_akademisi', $akademisi === 0 ? null : number_format($akademisi, 0, '', '.'));
                            })
                            ->formatStateUsing(function ($state) {
                                if ($state === null || $state === '') return null;
                                $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                                return number_format((int) $number, 0, '', '.');
                            })
                            ->dehydrateStateUsing(function ($state) {
                                return preg_replace('/[^0-9]/', '', $state);
                            })
                            ->disabled(),
                        Forms\Components\TextInput::make('price_dexain')
                            ->label('Price Dexa.in')
                            ->prefix('Rp')
                            ->formatStateUsing(function ($state) {
                                if ($state === null || $state === '') return null;
                                $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                                return number_format((int) $number, 0, '', '.');
                            })
                            ->dehydrateStateUsing(function ($state) {
                                return preg_replace('/[^0-9]/', '', $state);
                            })
                            ->dehydrated()
                            ->disabled(),
                        Forms\Components\TextInput::make('price_akademisi')
                            ->label('Price Akademisi')
                            ->prefix('Rp')
                            ->formatStateUsing(function ($state) {
                                if ($state === null || $state === '') return null;
                                $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                                return number_format((int) $number, 0, '', '.');
                            })
                            ->dehydrateStateUsing(function ($state) {
                                return preg_replace('/[^0-9]/', '', $state);
                            })
                            ->dehydrated()
                            ->disabled(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('due_days')
                            ->label('Due Date'),
                        Forms\Components\TextInput::make('contact')
                            ->label('Contact')
                            ->prefix('+62')
                            ->disabled()
                            ->reactive()
                            ->afterStateHydrated(function ($state, $set, $get) {
                                // Isi contact otomatis dari customer
                                $customerId = $get('customer_id');
                                if ($customerId) {
                                    $customer = \App\Models\Customer::find($customerId);
                                    $nomor = $customer?->nomor ?? '';
                                    // Hilangkan leading 0 jika ada
                                    $nomor = ltrim($nomor, '0');
                                    $set('contact', $nomor);
                                }
                            }),
                    ]),
                    Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Repeater::make('file_tambahan')
                            ->label('File/Media Tambahan (File)')
                            ->schema([
                                Forms\Components\FileUpload::make('file')
                                    ->label('File')
                                    ->directory('order-tambahan'),
                            ])
                            ->addActionLabel('Tambah File'),
                        Forms\Components\Repeater::make('link_tambahan')
                            ->label('File/Media Tambahan (Link)')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->label('Link')
                                    ->placeholder('https://...')
                                    ->url(),
                            ])
                            ->addActionLabel('Tambah Link'),
                    ]),
                Forms\Components\Select::make('akademisi_id')
                    ->label('Akademisi')
                    ->searchable()
                    ->options(fn () => \App\Models\Akademisi::pluck('name', 'id')),
                Forms\Components\FileUpload::make('bukti_payment')
                    ->label('Bukti Payment')
                    ->directory('order-bukti'),
                Forms\Components\Textarea::make('note')
                    ->label('Note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomer_nota')
                    ->label('Nomer Nota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Harga')
                    ->getStateUsing(fn ($record) => $record->harga?->nama)
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_id')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->customer?->name)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prioritas')
                    ->label('Prioritas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_payment')
                    ->label('Status Payment')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, '', '.'))
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
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
