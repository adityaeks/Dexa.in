<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OrderCalendarWidget;
use Filament\Pages\Page;

class OrderCalendarPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.order-calendar-page';

    protected static ?string $navigationLabel = 'Kalender';

    protected static ?string $title = 'Kalender';

    public function getHeaderWidgets(): array
    {
        return [
            OrderCalendarWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }
}
