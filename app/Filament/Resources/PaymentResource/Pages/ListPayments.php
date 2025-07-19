<?php
namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PaymentResource\Widgets\PaymentStatsOverview::class,
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'lunas' => Tab::make('Lunas')
                ->modifyQueryUsing(fn ($query) => $query->where('payment', 'lunas')),
            'dp' => Tab::make('DP')
                ->modifyQueryUsing(fn ($query) => $query->where('payment', 'dp')),
            'belum' => Tab::make('Belum')
                ->modifyQueryUsing(fn ($query) => $query->where('payment', 'belum')),
        ];
    }
}
