<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('All'),
            'lunas' => \Filament\Resources\Components\Tab::make('Lunas')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'lunas')),
            'dp' => \Filament\Resources\Components\Tab::make('DP')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'dp')),
            'belum' => \Filament\Resources\Components\Tab::make('Belum')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'belum')),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\BillResource\Widgets\BillStatsOverview::class,
        ];
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
