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

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    \Illuminate\Support\Facades\Log::info('Delete action triggered for order', [
                        'order_id' => $record->id,
                        'google_calendar_event_id' => $record->google_calendar_event_id
                    ]);

                    // Hapus event Google Calendar sebelum menghapus order
                    if ($record && $record->google_calendar_event_id) {
                        try {
                            $googleCalendarService = new \App\Services\GoogleCalendarService();
                            $success = $googleCalendarService->deleteEventFromOrder($record);

                            \Illuminate\Support\Facades\Log::info('Google Calendar delete result', [
                                'order_id' => $record->id,
                                'success' => $success
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error saat hapus Google Calendar event', [
                                'order_id' => $record->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::info('Order tidak memiliki Google Calendar event', [
                            'order_id' => $record->id
                        ]);
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\OrderResource::getWidgets()[0],
        ];
    }
}
