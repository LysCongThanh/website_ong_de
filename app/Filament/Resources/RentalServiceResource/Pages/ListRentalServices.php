<?php

namespace App\Filament\Resources\RentalServiceResource\Pages;

use App\Filament\Resources\RentalServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRentalServices extends ListRecords
{
    protected static string $resource = RentalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
