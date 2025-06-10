<?php

namespace App\Filament\Components\TableFields;

use Filament\Tables\Columns\IconColumn;

class IsActiveColumn
{
    public static function make(): IconColumn
    {
        return IconColumn::make('is_active')
            ->label('Trạng thái')
            ->boolean()
            ->alignCenter()
            ->trueIcon('heroicon-o-check-circle')
            ->falseIcon('heroicon-o-x-circle')
            ->trueColor('success')
            ->falseColor('danger')
            ->sortable();
    }
}