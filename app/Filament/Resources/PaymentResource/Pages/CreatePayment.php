<?php
namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Panggil PaymentController@store
        $controller = App::make(PaymentController::class);
        $request = Request::create('', 'POST', $data);
        $response = $controller->store($request);
        $result = $response->getData(true);
        if (\Illuminate\Support\Arr::get($result, 'success')) {
            // Return model instance agar Filament redirect ke detail
            return \App\Models\Payment::find($result['payment']['id']);
        } else {
            $this->notify('danger', \Illuminate\Support\Arr::get($result, 'message', 'Gagal membuat payment'));
            throw new \Exception(\Illuminate\Support\Arr::get($result, 'message', 'Gagal membuat payment'));
        }
    }
}
