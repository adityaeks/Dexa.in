<?php

namespace App\Filament\Resources;

use App\Models\Payment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pembayaran Customer';
    protected static ?string $modelLabel = 'Payment';
    protected static ?string $pluralModelLabel = 'Pembayaran Customer';
    protected static ?string $navigationGroup = 'Manajemen Order';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('order_id')
                ->label('Nota')
                ->options(function () {
                    // Untuk mode edit, include semua order termasuk yang lunas
                    $query = \App\Models\Order::query();

                    // Jika ini adalah mode create, filter hanya yang belum lunas
                    if (request()->route()->getName() === 'filament.admin.resources.payments.create') {
                        $query->where(function($q) {
                            $q->where('status_payment', '!=', 'Lunas')
                              ->orWhereNull('status_payment');
                        });
                    }

                    return $query->orderBy('nomer_nota', 'asc')
                        ->pluck('nomer_nota', 'id')
                        ->toArray();
                })
                ->searchable()
                ->required()
                ->default(fn () => request('order_id'))
                ->disabled(fn ($livewire) => request()->has('order_id'))
                ->hidden(fn ($livewire) => request()->has('order_id'))
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $order = \App\Models\Order::find($state);
                        $price = $order?->price ?? 0;
                        $amt_reff = $order?->amt_reff ?? 0;
                        $sisa = (int)$price - (int)$amt_reff;
                        $set('price_normal', number_format((int)$price, 0, '', '.'));
                        $set('price_sisa', number_format((int)$sisa, 0, '', '.'));
                    } else {
                        $set('price_normal', null);
                        $set('price_sisa', null);
                    }
                }),
            Select::make('payment')
                ->label('Metode Pembayaran')
                ->options([
                    'bri' => 'BRI',
                    'bca' => 'BCA',
                    'bni' => 'BNI',
                    'dana' => 'DANA',
                    'ovo' => 'OVO',
                    'gopay' => 'GoPay',
                    'shoopepay' => 'ShoopePay',
                    'seabank' => 'SeaBank',
                ])
                ->required(),
            TextInput::make('price_normal')
                ->label('Price Normal (Sebelum Bayar)')
                ->prefix('Rp')
                ->disabled()
                ->dehydrated(true)
                ->formatStateUsing(function ($state) {
                    if ($state === null || $state === '') return null;
                    $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                    return number_format((int) $number, 0, '', '.');
                })
                ->dehydrateStateUsing(function ($state) {
                    return preg_replace('/[^0-9]/', '', $state);
                }),
            TextInput::make('price_bayar')
                ->label('Harga Bayar')
                ->required()
                ->prefix('Rp')
                ->live()
                ->formatStateUsing(function ($state) {
                    if ($state === null || $state === '') return null;
                    $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                    return number_format((int) $number, 0, '', '.');
                })
                ->dehydrateStateUsing(function ($state) {
                    return preg_replace('/[^0-9]/', '', $state);
                })
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                        $formatted = number_format((int) $number, 0, '', '.');
                        $set('price_bayar', $formatted);
                    }
                }),
            TextInput::make('price_sisa')
                ->label('Sisa Bayar')
                ->prefix('Rp')
                ->disabled()
                ->formatStateUsing(function ($state) {
                    if ($state === null || $state === '') return null;
                    $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                    return number_format((int) $number, 0, '', '.');
                }),
            FileUpload::make('bukti_pembayaran')
                ->label('Bukti Pembayaran')
                ->directory('payment-bukti')
                ->required(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seq')->label('No'),
                TextColumn::make('tr_code')
                    ->label('Nota Order')
                    ->searchable(),
                TextColumn::make('order.status_payment')
                    ->label('Status Bayar')
                    ->badge()
                    ->color(fn ($state) => match (strtolower($state)) {
                        'lunas' => 'success',
                        'dp' => 'warning',
                        'belum' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('price_bayar')->label('Harga Bayar')->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
                TextColumn::make('price_sisa')->label('Sisa Bayar')->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
            ])
            ->filters([
                SelectFilter::make('order_status_payment')
                    ->label('Status Bayar')
                    ->options([
                        'lunas' => 'Lunas',
                        'dp' => 'DP',
                        'belum' => 'Belum',
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('order', function ($q) use ($data) {
                                $q->where('status_payment', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->after(function ($record) {
                        $order = \App\Models\Order::find($record->order_id);
                        if ($order) {
                            $amt_reff = $order->payments()->where('id', '!=', $record->id)->sum('price_bayar');
                            $order->amt_reff = $amt_reff;
                            // Update payment_ids (hapus id payment yang dihapus)
                            $order->payment_ids = $order->payments()->where('id', '!=', $record->id)->pluck('id')->toArray();
                            // Update status_payment otomatis
                            if ($amt_reff == 0) {
                                $order->status_payment = 'belum';
                            } elseif ($amt_reff < $order->price) {
                                $order->status_payment = 'DP';
                            } elseif ($amt_reff == $order->price) {
                                $order->status_payment = 'Lunas';
                            }
                            $order->save();
                        }
                    }),
                ])
            ->groups([
                Group::make('tr_code')
                    ->label('Nomer Nota'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PaymentResource\Pages\ListPayments::route('/'),
            'create' => PaymentResource\Pages\CreatePayment::route('/create'),
            'edit' => PaymentResource\Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\PaymentResource\Widgets\PaymentStatsOverview::class,
        ];
    }
}
