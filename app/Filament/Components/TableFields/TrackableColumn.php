<?php

namespace App\Filament\Components\TableFields;

use Filament\Tables\Columns\TextColumn;

class TrackableColumn
{
    public static function make(): array {
        return [
            TextColumn::make('creator.name')
                ->label('Người tạo')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->placeholder('Không có thông tin'),

            TextColumn::make('lastUpdater.name')
                ->label('Người cập nhật')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->placeholder('Không có thông tin'),

            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label('Cập nhật cuối')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('deleted_at')
                ->label('Ngày xóa')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->placeholder('Đang hoạt động'),
        ];
    }
}