<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('payment')
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
            Forms\Components\TextInput::make('price_normal')
                ->label('Price Normal (Sebelum Bayar)')
                ->prefix('Rp')
                ->disabled()
                ->required()
                ->dehydrated(true)
                ->default(function ($livewire) {
                    $order = $livewire->getOwnerRecord();
                    if (!$order) return 0;
                    // Hitung sisa yang belum dibayar dari semua payment sebelumnya
                    $totalPaid = $order->payments()->sum('price_bayar');
                    $sisa = (int) $order->price - (int) $totalPaid;
                    return number_format($sisa, 0, '', '.');
                })
                ->formatStateUsing(function ($state) {
                    if ($state === null || $state === '') return null;
                    $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                    return number_format((int) $number, 0, '', '.');
                })
                ->dehydrateStateUsing(function ($state) {
                    return preg_replace('/[^0-9]/', '', $state);
                }),
            Forms\Components\TextInput::make('price_bayar')
                ->label('Harga Bayar')
                ->required()
                ->prefix('Rp')
                ->live()
                ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                    // Format input saat user mengetik
                    $number = preg_replace('/[^0-9]/', '', $state);
                    $formatted = $number === '' ? null : number_format((int) $number, 0, '', '.');
                    $set('price_bayar', $formatted);
                    // Kurangi price order secara otomatis
                    $order = $livewire->getOwnerRecord();
                    // Ambil total pembayaran sebelumnya (exclude current form input)
                    $totalPaid = $order->payments()->sum('price_bayar') ?? 0;
                    $bayar = (int) $number;
                    $sisa = (int) $order->price - $totalPaid - $bayar;
                    $set('price_normal', number_format(max($sisa, 0), 0, '', '.'));
                })
                ->formatStateUsing(function ($state) {
                    if ($state === null || $state === '') return null;
                    $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                    return number_format((int) $number, 0, '', '.');
                })
                ->dehydrateStateUsing(function ($state) {
                    return preg_replace('/[^0-9]/', '', $state);
                }),
            Forms\Components\FileUpload::make('bukti_pembayaran')
                ->label('Bukti Pembayaran')
                ->directory('payment-bukti')
                ->required(false),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('seq')
                    ->label('No'),
                Tables\Columns\TextColumn::make('payment')->label('Metode Pembayaran'),
                Tables\Columns\TextColumn::make('price_bayar')->label('Harga Bayar')->formatStateUsing(fn($state) => 'Rp ' . number_format((int)$state, 0, '', '.')),
            ])
            ->headerActions([
                Action::make('createPayment')
                    ->label('Tambah Payment')
                    ->form([
                        Forms\Components\Placeholder::make('info_sisa_bayar')
                            ->label('Sisa Bayar Saat Ini')
                            ->content(function($livewire) {
                                $order = $livewire->getOwnerRecord();
                                $lastPayment = $order->payments()->latest('id')->first();
                                $sisa = $lastPayment ? $lastPayment->price_sisa : $order->price;
                                return 'Rp ' . number_format((int) $sisa, 0, '', '.');
                            }),
                        Forms\Components\Select::make('payment')
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
                        Forms\Components\TextInput::make('price_normal')
                            ->label('Price asli')
                            ->default(fn($livewire) => $livewire->getOwnerRecord()->price)
                            ->disabled()
                            ->prefix('Rp')
                            ->dehydrated(true)
                            ->required()
                            ->formatStateUsing(function ($state) {
                                if ($state === null || $state === '') return null;
                                $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                                return number_format((int) $number, 0, '', '.');
                            }),
                        Forms\Components\TextInput::make('price_bayar')
                            ->label('Harga Bayar')
                            ->prefix('Rp')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Format input saat user mengetik
                                $number = preg_replace('/[^0-9]/', '', $state);
                                $formatted = $number === '' ? null : number_format((int) $number, 0, '', '.');
                                $set('price_bayar', $formatted);

                                // Hitung sisa bayar
                                $priceNormal = (int) preg_replace('/[^0-9]/', '', $get('price_normal'));
                                $priceBayar = (int) $number;
                                $sisa = $priceNormal - $priceBayar;
                                $set('price_sisa', $sisa > 0 ? $sisa : null);
                            })
                            ->formatStateUsing(function ($state) {
                                if ($state === null || $state === '') return null;
                                $number = preg_replace('/[^0-9]/', '', str_replace([',', '.'], '', $state));
                                return number_format((int) $number, 0, '', '.');
                            })
                            ->dehydrateStateUsing(function ($state) {
                                return preg_replace('/[^0-9]/', '', $state);
                            }),
                        Forms\Components\FileUpload::make('bukti_pembayaran')
                            ->label('Bukti Pembayaran')
                            ->directory('payment-bukti')
                            ->required(false),
                    ])
                    ->action(function (array $data, $livewire) {
                        $order = $livewire->getOwnerRecord();
                        $lastPayment = $order->payments()->latest('id')->first();
                        $sisaSebelumnya = $lastPayment ? (int) $lastPayment->price_sisa : (int) $order->price;
                        $priceBayar = (int) preg_replace('/[^0-9]/', '', $data['price_bayar']);
                        if ($priceBayar > $sisaSebelumnya) {
                            \Filament\Notifications\Notification::make()
                                ->title('Nominal bayar tidak boleh lebih dari sisa pembayaran ('.number_format($sisaSebelumnya, 0, '', '.').')')
                                ->danger()
                                ->send();
                            return;
                        }
                        $data['order_id'] = $order->id;
                        $data['price_normal'] = $order->price;
                        $data['price_sisa'] = $sisaSebelumnya - $priceBayar > 0 ? $sisaSebelumnya - $priceBayar : 0;
                        $controller = app(\App\Http\Controllers\PaymentController::class);
                        $request = new \Illuminate\Http\Request();
                        $request->replace($data);
                        $controller->store($request);
                        \Filament\Notifications\Notification::make()
                            ->title('Payment berhasil ditambahkan!')
                            ->success()
                            ->send();
                        $livewire->redirect(request()->header('Referer') ?? url()->current());
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record, $livewire) {
                        $order = $record->order;
                        if ($order) {
                            // Ambil payment terakhir setelah delete (exclude yang dihapus)
                            $lastPayment = $order->payments()->where('id', '!=', $record->id)->latest('id')->first();
                            $order->amt_reff = $lastPayment ? $lastPayment->price_bayar : 0;
                            $order->save();

                            // Update price_sisa payment terakhir (jika ada)
                            if ($lastPayment) {
                                $lastPayment->price_sisa = $order->price - $lastPayment->price_bayar;
                                $lastPayment->save();
                            }
                        }
                        $livewire->redirect(request()->header('Referer') ?? url()->current());
                    }),
            ]);
    }

    public static function getTitle($ownerRecord = null, $pageClass = null): string
    {
        if ($ownerRecord) {
            $lastPayment = $ownerRecord->payments()->latest('id')->first();
            $sisa = $lastPayment ? $lastPayment->price_sisa : $ownerRecord->price;
            return 'Belum Bayar: Rp ' . number_format((int) $sisa, 0, '', '.');
        }
        return 'Payments';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil order terkait
        $order = $this->getOwnerRecord();
        // Set price_normal dari order->price
        $data['price_normal'] = $order ? $order->price : 0;
        return $data;
    }

    public static function afterCreateTableAction($record, $data)
    {
        Log::info('afterCreateTableAction', ['order_id' => $record->order_id, 'payment_id' => $record->id]);
        $order = \App\Models\Order::find($record->order_id);
        if ($order) {
            Log::info('Order ditemukan', ['order_id' => $order->id]);
            $order->amt_reff = $record->price_bayar;
            $order->save();
        } else {
            Log::warning('Order tidak ditemukan', ['order_id' => $record->order_id]);
        }
    }

    public function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::handleRecordCreation($data);

        // Update amt_reff pada order
        $order = \App\Models\Order::find($record->order_id);
        if ($order) {
            $order->amt_reff = $record->price_bayar;
            $order->save();
        }

        return $record;
    }
}
