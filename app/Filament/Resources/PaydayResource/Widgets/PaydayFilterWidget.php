<?php

namespace App\Filament\Resources\PaydayResource\Widgets;

use App\Models\Payday;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;

class PaydayFilterWidget extends Widget
{
    protected static string $view = 'filament.widgets.payday-filter-widget';

    public $startDate = '';
    public $dueDate = '';

    public function mount(): void
    {
        $this->startDate = Session::get('payday_filter_start_date', '');
        $this->dueDate = Session::get('payday_filter_due_date', '');
    }

    public function updatedStartDate($value): void
    {
        Session::put('payday_filter_start_date', $value);
        $this->dispatch('payday-filter-updated');
    }

    public function updatedDueDate($value): void
    {
        Session::put('payday_filter_due_date', $value);
        $this->dispatch('payday-filter-updated');
    }

    public function clearFilters(): void
    {
        $this->startDate = '';
        $this->dueDate = '';

        Session::forget(['payday_filter_start_date', 'payday_filter_due_date']);
        $this->dispatch('payday-filter-updated');
    }
}
