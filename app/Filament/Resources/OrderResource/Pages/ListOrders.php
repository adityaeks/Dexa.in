<?php


namespace App\Filament\Resources\OrderResource\Pages;
use Filament\Resources\Components\Tab;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
// ...existing code...

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'not_started' => Tab::make('Not started')
                ->query(fn ($query) => $query->where('status', 'Not started')),
            'inprogress' => Tab::make('Inprogress')
                ->query(fn ($query) => $query->where('status', 'Inprogress')),
            'done' => Tab::make('Done')
                ->query(fn ($query) => $query->where('status', 'Done')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\OrderResource::getWidgets()[0],
        ];
    }
}
