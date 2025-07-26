<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $amt_reff = (int) preg_replace('/[^0-9]/', '', $data['amt_reff'] ?? 0);
        $price = (int) preg_replace('/[^0-9]/', '', $data['price'] ?? 0);
        if ($amt_reff === 0) {
            $data['status'] = 'belum';
        } elseif ($amt_reff < $price) {
            $data['status'] = 'dp';
        } elseif ($amt_reff === $price) {
            $data['status'] = 'lunas';
        }
        return $data;
    }
}
