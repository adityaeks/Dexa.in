<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\{Order, Harga, Customer, Akademisi};
use Filament\Forms\Components\{ Select, TextInput, Grid, DatePicker, Repeater, FileUpload, Textarea};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

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
                Grid::make(2)
                    ->schema([
                        Select::make('nama')
                            ->label('Jokian')
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
                            ->createOptionForm([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('nama')
                                            ->label('Nama')
                                            ->required()
                                            ->unique(ignoreRecord: true, table: 'hargas', column: 'nama')
                                            ->validationMessages([
                                                'unique' => 'Nama sudah terdaftar, silakan gunakan nama lain.',
                                            ]),
                                        Select::make('tingkat')
                                            ->label('Tingkat')
                                            ->options([
                                                'low' => 'Low',
                                                'medium' => 'Medium',
                                                'high' => 'High',
                                            ])
                                            ->required(),
                                        TextInput::make('harga')
                                            ->label('Harga')
                                            ->required()
                                            ->prefix('Rp')
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set) {
                                                // Format input saat user mengetik
                                                $number = preg_replace('/[^0-9]/', '', $state);
                                                $set('harga', $number === '' ? null : number_format((int) $number, 0, '', '.'));
                                            })
                                            ->formatStateUsing(function ($state) {
                                                if ($state === null || $state === '') return null;
                                                $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                                                return number_format((int) $number, 0, '', '.');
                                            })
                                            ->dehydrateStateUsing(function ($state) {
                                                return preg_replace('/[^0-9]/', '', $state);
                                            }),
                                        Select::make('tipe')
                                            ->label('Tipe')
                                            ->options([
                                                'pendidikan' => 'Pendidikan',
                                                'instansi' => 'Instansi',
                                            ])
                                            ->required(),
                                    ]),
                            ])
                            ->createOptionUsing(function (array $data) {
                                // Simpan data Harga baru dan return id-nya
                                $harga = Harga::create([
                                    'nama' => $data['nama'],
                                    'tingkat' => $data['tingkat'],
                                    'harga' => preg_replace('/[^0-9]/', '', $data['harga']),
                                    'tipe' => $data['tipe'],
                                ]);
                                return $harga->id;
                            })
                            ->createOptionAction(function ($action) {
                                $action->modalHeading('Tambah Harga Baru');
                            })
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
                        TextInput::make('nomer_nota')
                            ->label('Nomer Nota')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Auto Generate'),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->required()
                            ->searchable()
                            ->options(fn () => Customer::all()->mapWithKeys(function($customer) {
                                $label = $customer->code . ' - ' . $customer->name;
                                return [$customer->id => $label];
                            }))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama Customer')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('nomor')
                                    ->label('Nomor')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(3),
                            ])
                            ->createOptionUsing(function (array $data) {
                                // Simpan data Customer baru dan return id-nya
                                $customer = Customer::create([
                                    'name' => $data['name'],
                                    'nomor' => $data['nomor'],
                                    'description' => $data['description'] ?? null,
                                ]);
                                return $customer->id;
                            })
                            ->createOptionAction(function ($action) {
                                $action->modalHeading('Tambah Customer Baru');
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $customer = Customer::find($state);
                                $nomor = $customer?->nomor ?? '';
                                $nomor = ltrim($nomor, '0');
                                $set('contact', $nomor);
                            }),
                        Select::make('status')
                            ->label('Penegerjaan')
                            ->required()
                            ->options([
                                'Not started' => 'Not started',
                                'Inprogress' => 'Inprogress',
                                'Done' => 'Done',
                            ])
                            ->default('Not started'),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('prioritas')
                            ->label('Prioritas')
                            ->required()
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'urgent' => 'Urgent',
                            ]),
                        Select::make('status_payment')
                            ->label('Status Payment')
                            ->disabled()
                            ->options([
                                'belum' => 'Belum',
                                'DP' => 'DP',
                                'Lunas' => 'Lunas',
                            ])
                            ->default(function ($get) {
                                $amt_reff = (int) $get('amt_reff');
                                $price = (int) $get('price');
                                if ($amt_reff == 0) {
                                    return 'belum';
                                } elseif ($amt_reff < $price) {
                                    return 'DP';
                                } elseif ($amt_reff == $price) {
                                    return 'Lunas';
                                }
                                return 'belum';
                            })
                            ->reactive()
                            ->afterStateHydrated(function ($state, $set, $get) {
                                $amt_reff = (int) $get('amt_reff');
                                $price = (int) $get('price');
                                if ($amt_reff == 0) {
                                    $set('status_payment', 'belum');
                                } elseif ($amt_reff < $price) {
                                    $set('status_payment', 'DP');
                                } elseif ($amt_reff == $price) {
                                    $set('status_payment', 'Lunas');
                                }
                            }),
                    ]),
                Grid::make(3)
                    ->schema([
                        TextInput::make('price')
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
                        TextInput::make('price_dexain')
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
                            ->dehydrated(true)
                            ->disabled(),
                        TextInput::make('price_akademisi')
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
                            ->dehydrated(true)
                            ->disabled(),
                    ]),
                Grid::make(2)
                    ->schema([
                        DatePicker::make('due_days')
                            ->label('Due Date'),
                        TextInput::make('contact')
                            ->label('Contact')
                            ->prefix('+62')
                            ->disabled()
                            ->reactive()
                            ->afterStateHydrated(function ($state, $set, $get) {
                                // Isi contact otomatis dari customer
                                $customerId = $get('customer_id');
                                if ($customerId) {
                                    $customer = Customer::find($customerId);
                                    $nomor = $customer?->nomor ?? '';
                                    // Hilangkan leading 0 jika ada
                                    $nomor = ltrim($nomor, '0');
                                    $set('contact', $nomor);
                                }
                            }),
                    ]),
                    Grid::make(2)
                    ->schema([
                        Repeater::make('file_tambahan')
                            ->label('File/Media Tambahan (File)')
                            ->schema([
                                FileUpload::make('file')
                                    ->label('File')
                                    ->directory('order-tambahan'),
                            ])
                            ->addActionLabel('Tambah File'),
                        Repeater::make('link_tambahan')
                            ->label('File/Media Tambahan (Link)')
                            ->schema([
                                TextInput::make('url')
                                    ->label('Link')
                                    ->placeholder('https://...')
                                    ->url(),
                            ])
                            ->addActionLabel('Tambah Link'),
                    ]),
                Select::make('akademisi_id')
                    ->label('Akademisi')
                    ->searchable()
                    ->options(fn () => Akademisi::pluck('name', 'id')),
                FileUpload::make('bukti_payment')
                    ->label('Bukti Payment')
                    ->directory('order-bukti'),
                Textarea::make('note')
                    ->label('Note'),

                // Payment section is now managed by PaymentsRelationManager (table with modal), not a Repeater.
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomer_nota')
                    ->label('Nomer Nota')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Jokian')
                    ->getStateUsing(fn ($record) => $record->harga?->nama)
                    ->sortable(),
                TextColumn::make('customer_id')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->customer?->name)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Pengerjaan')
                    ->sortable()
                    ->color(fn ($state) => match (strtolower($state)) {
                        'done' => 'success', // hijau
                        'inprogress' => 'warning', // kuning
                        'not started' => 'gray', // abu
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('prioritas')
                    ->label('Prioritas')
                    ->sortable()
                    ->color(fn ($state) => match (strtolower($state)) {
                        'urgent' => 'danger', // merah
                        'medium' => 'warning', // kuning
                        'low' => 'gray', // abu
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('status_payment')
                    ->label('Status Payment')
                    ->sortable()
                    ->color(fn ($state) => match (strtolower($state)) {
                        'lunas' => 'success', // hijau
                        'dp' => 'warning', // kuning
                        'belum' => 'danger', // merah
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, '', '.'))
                    ->sortable(),
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
            OrderResource\RelationManagers\PaymentsRelationManager::class,
            ActivitylogRelationManager::class,
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
