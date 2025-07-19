<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class LatestOrdersTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Order::query()->latest('created_at')->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('created_at')->label('Order Date')->date('M d, Y'),
            TextColumn::make('nomer_nota')->label('Number'),
            TextColumn::make('customer.name')->label('Customer'),
            TextColumn::make('status')->badge()->color(fn($state) => match($state) {
                'Done' => 'success',
                'Inprogress' => 'warning',
                default => 'primary',
            }),
            TextColumn::make('currency')->label('Currency')->default('IDR'),
            TextColumn::make('price')->label('Total price')->money('IDR', true),
            TextColumn::make('shipping_cost')->label('Shipping cost')->default('-'),
            TextColumn::make('open_status')->label('Open')->default('Open')->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        return false;
    }
}
