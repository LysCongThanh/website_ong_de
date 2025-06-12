<?php

namespace App\Filament\Resources\RentalServiceResource\Forms;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;

class RentalServiceBasicField
{
    public static function make(): array
    {
        return [
            Section::make('Thông tin cơ bản')
                ->description('Thông tin chính về dịch vụ thuê')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    TextInput::make('name')
                        ->label('Tên dịch vụ')
                        ->placeholder('Nhập tên dịch vụ (VD: Thuê xe máy, Thuê phòng...)')
                        ->helperText('Tên dịch vụ sẽ hiển thị cho khách hàng')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $context, $state, Set $set) {
                            if ($context === 'create') {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }
                        }),

                    Toggle::make('is_active')
                        ->label('Trạng thái hoạt động')
                        ->helperText('Bật/tắt để kích hoạt hoặc vô hiệu hóa dịch vụ')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),

            Section::make('Mô tả dịch vụ')
                ->description('Thông tin chi tiết về dịch vụ')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('short_description')
                        ->label('Mô tả ngắn')
                        ->hintIcon('heroicon-o-pencil')
                        ->placeholder('Mô tả ngắn gọn về dịch vụ')
                        ->helperText('Mô tả ngắn sẽ hiển thị trong danh sách dịch vụ')
                        ->rows(4)
                        ->maxLength(500),

                    TinyEditor::make('long_description')
                        ->label('Mô tả chi tiết')
                        ->hintIcon('heroicon-o-pencil')
                        ->placeholder('Mô tả chi tiết về dịch vụ, bao gồm tính năng, lợi ích...')
                        ->helperText('Mô tả chi tiết sẽ hiển thị trên trang chi tiết dịch vụ'),
                ]),
        ];
    }
}