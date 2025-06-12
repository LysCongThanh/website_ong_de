<?php

namespace App\Filament\Resources\PackageResource\Forms;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;

class PackageForm
{
    public static function make(Form $form): Form
    {
        return $form->schema([
            Tabs::make('')
                ->tabs([
                    Tabs\Tab::make('Thông tin gói')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            ...PackageInformationField::make(),
                        ]),
                    Tabs\Tab::make('Dịch vụ bao gồm')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            ...IncludedServiceField::make(),
                        ]),

                    Tabs\Tab::make('Menu & Thực đơn')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            ...MenuField::make(),
                        ])
                ])->columnSpanFull()
        ]);
    }
}