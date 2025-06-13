<?php

namespace App\Filament\Resources\PackageResource\Forms;

use App\Filament\Components\FormFields\BasePricingField;
use App\Filament\Components\FormFields\CustomerCapacityPricingField;
use App\Filament\Components\FormFields\CustomerSegmentPricingField;
use App\Filament\Components\FormFields\PolicyField;
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
                        ]),

                    Tabs\Tab::make('Chính sách')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            PolicyField::make(),
                        ]),

                    Tabs\Tab::make('Thiết lập giá')
                        ->icon('heroicon-o-currency-dollar')
                        ->badge(fn($record) => $record?->basePrices?->count() ?? 0)
                        ->badgeColor('success')
                        ->schema([
                            ...BasePricingField::make(),
                        ]),

                    Tabs\Tab::make('Giá nhóm')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            ...CustomerCapacityPricingField::make()
                        ]),

                    Tabs\Tab::make('Giá đối tượng')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            ...CustomerSegmentPricingField::make()
                        ])
                ])->columnSpanFull()
        ]);
    }
}