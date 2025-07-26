<?php
namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Tables;
use Livewire\Attributes\On;

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
                ->modifyQueryUsing(fn ($query) => $query->whereHas('order', fn($q) => $q->where('status_payment', 'lunas'))),
            'dp' => Tab::make('DP')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('order', fn($q) => $q->where('status_payment', 'dp'))),
            'belum' => Tab::make('Belum')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('order', fn($q) => $q->where('status_payment', 'belum'))),
        ];
    }
}
