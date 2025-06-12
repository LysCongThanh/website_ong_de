<?php

namespace App\Filament\Components\FormFields;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class MediaField
{
    public static function make(): array {
        return [
            Section::make('Hình ảnh chính')
                ->description('Hình ảnh đại diện chính cho hoạt động')
                ->icon('heroicon-m-photo')
                ->collapsible()
                ->schema([
                    SpatieMediaLibraryFileUpload::make('main_image')
                        ->label('Hình ảnh chính')
                        ->collection('main_image')
                        ->image()
                        ->maxFiles(1)
                        ->downloadable()
                        ->previewable(true)
                        ->helperText('Chọn hình ảnh đại diện chính cho hoạt động (tối đa 1 ảnh)'),
                ]),

            Section::make('Thư viện ảnh')
                ->description('Các hình ảnh bổ sung cho hoạt động')
                ->icon('heroicon-m-photo')
                ->collapsible()
                ->collapsed()
                ->schema([
                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->label('Thư viện ảnh')
                        ->collection('gallery')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->downloadable()
                        ->previewable(true)
                        ->helperText('Tải lên các hình ảnh bổ sung cho hoạt động (có thể chọn nhiều ảnh)'),
                ]),
        ];
    }
}