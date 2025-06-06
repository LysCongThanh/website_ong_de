<?php

namespace App\Filament\Resources\RentalServiceResource\Pages;

use App\Filament\Resources\RentalServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRentalService extends EditRecord
{
    protected static string $resource = RentalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
