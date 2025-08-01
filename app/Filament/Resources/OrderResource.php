<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\{ Pages, RelationManagers\PaymentsRelationManager, Widgets\OrderStatsOverview };
use App\Models\{ Order, Harga, Customer, Akademisi };
use Filament\Forms\Components\{ Select, TextInput, Grid, DatePicker, Repeater, FileUpload, Textarea };
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;
use Filament\Notifications\Notification;
use Carbon\Carbon;

// ...existing code...
// OrderStatsOverview dipindahkan ke Widgets/OrderStatsOverview.php dan menggunakan Trend

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Order';
    protected static ?string $modelLabel = 'Order';
    protected static ?string $pluralModelLabel = 'Order';
    protected static ?string $navigationGroup = 'Manajemen Order';
    protected static ?int $navigationSort = 1;

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
                            ->multiple() // Memungkinkan memilih lebih dari satu jokian
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

                                        TextInput::make('qty')
                                            ->label('Quantity')
                                            ->numeric(),
                                        Textarea::make('description')
                                            ->label('Deskripsi')
                                            ->rows(3),
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
                                // $state sekarang array of id harga
                                $hargaList = Harga::whereIn('id', (array)$state)->get();
                                $totalPrice = $hargaList->sum('harga');
                                $set('price', $totalPrice === 0 ? null : number_format($totalPrice, 0, '', '.'));
                                // price_dexain = 10% jika total <= 100000, 20% jika > 100000
                                if ($totalPrice <= 100000) {
                                    $dexain = (int) round($totalPrice * 0.1);
                                } else {
                                    $dexain = (int) round($totalPrice * 0.2);
                                }
                                $akademisi = $totalPrice - $dexain;
                                $set('price_dexain', $dexain === 0 ? null : number_format($dexain, 0, '', '.'));
                                $set('price_akademisi', $akademisi === 0 ? null : number_format($akademisi, 0, '', '.'));
                            }),
                        TextInput::make('nomer_nota')
                            ->label('Nomer Nota')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Auto Generate'),
                        // code tidak perlu di display di form
                    ]),
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
                                    ->maxLength(255)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // Jika user mengetik 08..., ubah ke +628...
                                        if (preg_match('/^08/', $state)) {
                                            $set('nomor', '+62' . substr($state, 1));
                                        } elseif (preg_match('/^\+62/', $state)) {
                                            $set('nomor', $state);
                                        } else {
                                            // Jika user mengetik tanpa 0 atau +62, tambahkan +62
                                            $set('nomor', '+62' . ltrim($state, '0'));
                                        }
                                    }),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(3),
                            ])
                            ->createOptionUsing(function (array $data) {
                                // Simpan data Customer baru dan return id-nya
                                $nomor = $data['nomor'] ?? '';
                                // Normalisasi nomor: pastikan selalu +62 di depan
                                if (preg_match('/^08/', $nomor)) {
                                    $nomor = '+62' . substr($nomor, 1);
                                } elseif (!preg_match('/^\+62/', $nomor)) {
                                    $nomor = '+62' . ltrim($nomor, '0');
                                }
                                $customer = Customer::create([
                                    'name' => $data['name'],
                                    'nomor' => $nomor,
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
                                // Set kode customer ke field code
                                $set('code', $customer?->code ?? null);
                            }),
                        TextInput::make('contact')
                            ->label('Contact')
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
                        Select::make('status')
                            ->label('Pengerjaan')
                            ->required()
                            ->options([
                                'Not started' => 'Not started',
                                'Inprogress' => 'Inprogress',
                                'Done' => 'Done',
                            ])
                            ->default('Not started'),
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
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->default(now()),
                        DatePicker::make('due_date')
                            ->label('Deadline'),
                        Select::make('akademisi_id')
                            ->label('Akademisi')
                            ->searchable()
                            ->multiple()
                            ->options(fn () => Akademisi::pluck('name', 'id'))
                            ->formatStateUsing(function ($state) {
                                if (is_string($state)) {
                                    $decoded = json_decode($state, true);
                                    return is_array($decoded) ? $decoded : [];
                                }
                                return $state;
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                // Jika akademisi lebih dari 1, tampilkan field price2
                                if (is_array($state) && count($state) > 1) {
                                    $set('show_price2', true);
                                } else {
                                    $set('show_price2', false);
                                    $set('price2', null);
                                }
                            }),
                    // Hidden field untuk mengontrol visibility price2
                    // Field harga per akademisi (dinamis jika akademisi > 1)
                    Repeater::make('price_akademisi2')
                        ->label(function ($get) {
                            $total = (int) preg_replace('/[^0-9]/', '', $get('price_akademisi'));
                            $rows = $get('price_akademisi2') ?? [];
                            $terpakai = 0;
                            foreach ($rows as $row) {
                                if (is_array($row) && isset($row['harga'])) {
                                    $terpakai += (int) preg_replace('/[^0-9]/', '', $row['harga']);
                                }
                            }
                            $sisa = $total - $terpakai;
                            $label = 'Harga per Akademisi';
                            if ($total > 0) {
                                $label .= ' (Sisa: Rp ' . number_format($sisa, 0, '', '.') . ')';
                            }
                            return $label;
                        })
                        ->schema([
                            Select::make('akademisi_id')
                                ->options(fn ($get) => \App\Models\Akademisi::whereIn('id', (array) $get('../../akademisi_id'))->pluck('name', 'id'))
                                ->required()
                                // ->hidden()
                                ->disabled()
                                ->dehydrated(true),
                            TextInput::make('harga')
                                ->label('Harga')
                                ->required()
                                ->prefix('Rp')
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
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
                        ])
                        ->columns(2)
                        ->default(function ($get) {
                            $ids = (array) $get('akademisi_id');
                            return collect($ids)->map(fn($id) => ['akademisi_id' => $id, 'harga' => null])->toArray();
                        })
                        ->formatStateUsing(function ($state, $get) {
                            $akademisiIds = (array) $get('akademisi_id');
                            $result = [];
                            // Jika associative array ({"1":"5000","2":"4000"})
                            if (is_array($state) && !empty($state) && array_values($state) !== $state) {
                                foreach ($state as $id => $harga) {
                                    $result[] = [
                                        'akademisi_id' => $id,
                                        'harga' => $harga,
                                    ];
                                }
                                return $result;
                            }
                            // Jika array campur object/string
                            if (is_array($state)) {
                                foreach ($state as $i => $row) {
                                    if (is_array($row) && isset($row['akademisi_id']) && isset($row['harga'])) {
                                        $result[] = $row;
                                    } elseif (is_array($row) && isset($row['harga'])) {
                                        $result[] = [
                                            'akademisi_id' => $akademisiIds[$i] ?? null,
                                            'harga' => $row['harga'],
                                        ];
                                    } elseif (is_string($row) || is_numeric($row)) {
                                        $result[] = [
                                            'akademisi_id' => $akademisiIds[$i] ?? null,
                                            'harga' => $row,
                                        ];
                                    }
                                }
                                return $result;
                            }
                            return [];
                        })
                        ->visible(fn ($get) => is_array($get('akademisi_id')) && count($get('akademisi_id')) > 1)
                        ->reactive()
                        ->afterStateHydrated(function ($state, $set, $get) {
                            $ids = (array) $get('akademisi_id');
                            $rows = collect($state ?? [])->keyBy('akademisi_id');
                            $newRows = collect($ids)->map(fn($id) => [
                                'akademisi_id' => $id,
                                'harga' => $rows[$id]['harga'] ?? null,
                            ])->values()->toArray();
                            $set('price_akademisi2', $newRows);
                        })
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $ids = (array) $get('akademisi_id');
                            $rows = collect($state ?? [])->keyBy('akademisi_id');
                            $newRows = collect($ids)->map(fn($id) => [
                                'akademisi_id' => $id,
                                'harga' => $rows[$id]['harga'] ?? null,
                            ])->values()->toArray();
                            $set('price_akademisi2', $newRows);
                        })
                        ->dehydrateStateUsing(function ($state, $get) {
                            $akademisiIds = (array) $get('akademisi_id');
                            return collect($state)
                                ->map(function($row, $i) use ($akademisiIds) {
                                    return [
                                        'akademisi_id' => $akademisiIds[$i] ?? null,
                                        'harga' => is_array($row) ? ($row['harga'] ?? null) : $row,
                                    ];
                                })
                                ->values()
                                ->toArray();
                        }),
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
                    ->label('Nota')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_code')
                    ->label('Customer')
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Jokian')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return implode(', ', $state);
                        }
                        return $state;
                    })
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
                TextColumn::make('price2')
                    ->label('Price 2')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format((int) $state, 0, '', '.') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('status')
                    ->label('Status Pengerjaan')
                    ->options([
                        'Not started' => 'Not started',
                        'Inprogress' => 'Inprogress',
                        'Done' => 'Done',
                    ]),
                SelectFilter::make('prioritas')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'urgent' => 'Urgent',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                Html2MediaAction::make('cetak_invoice')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->savePdf()
                    ->filename(fn($record) => 'Invoice-' . ($record->nomer_nota ?? $record->id) . '.pdf')
                    ->content(fn($record) => view('filament.resources.order-resource.invoice', ['record' => $record])),
                EditAction::make()
                    ->color('warning'),
                DeleteAction::make()
                    ->before(function ($record, $action) {
                        if (!empty($record->payment_ids)) {
                            Notification::make()
                                ->title('Order tidak bisa dihapus karena sudah ada payment!')
                                ->danger()
                                ->send();
                            $action->cancel();
                            return false;
                        }
                        return true;
                    }),
            ])
            ->bulkActions([
                // Tidak ada bulk actions
            ])
            ->groups([
                Group::make('created_at')
                    ->label('Order Date')
                    ->getTitleFromRecordUsing(fn ($record) => $record->created_at ? Carbon::parse($record->created_at)->translatedFormat('F Y') : 'Tanpa Tanggal'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
            ActivitylogRelationManager::class,
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return (string) Order::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStatsOverview::class,
        ];
    }
}
