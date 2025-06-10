<?php

namespace App\Filament\Resources\TicketResource\Forms;

use App\Filament\Components\FormFields\BasePricingField;
use App\Filament\Components\FormFields\CustomerCapacityPricingField;
use App\Filament\Components\FormFields\CustomerSegmentPricingField;
use App\Filament\Components\FormFields\PolicyField;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;

class TicketForm
{
    public static function make(Form $form): Form {
        return $form->schema([
            Tabs::make('Quản lý vé')
                ->tabs([
                    Tabs\Tab::make('Thông tin cơ bản')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            TicketBasicField::make()
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
                            BasePricingField::make(),
                        ]),

                    Tabs\Tab::make('Giá nhóm')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            CustomerCapacityPricingField::make()
                        ]),

                    Tabs\Tab::make('Giá đối tượng')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            CustomerSegmentPricingField::make()
                        ])
                ])->columnSpanFull()
        ]);
    }
}