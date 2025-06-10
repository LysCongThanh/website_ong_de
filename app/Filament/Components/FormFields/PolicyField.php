<?php

namespace App\Filament\Components\FormFields;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class PolicyField
{
    public static function make(): Section
    {
        return Section::make('Chính sách vé')
            ->description('Quản lý các chính sách áp dụng cho vé (ví dụ: hoàn tiền, đổi vé, hủy vé...)')
            ->icon('heroicon-o-document-text')
            ->schema([
                Repeater::make('policies')
                    ->label('Danh sách chính sách')
                    ->relationship('policies')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên chính sách')
                            ->helperText('Tên hiển thị của chính sách (ví dụ: Chính sách hoàn tiền, Chính sách đổi vé...)')
                            ->placeholder('VD: Chính sách hoàn tiền')
                            ->prefixIcon('heroicon-o-document')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Select::make('type')
                            ->required()
                            ->label('Loại chính sách')
                            ->helperText('Chọn loại chính sách để phân loại (nếu có).')
                            ->placeholder('Chọn loại chính sách...')
                            ->options([
                                'Đổi trả' => 'Đổi trả',
                                'Hoàn tiền' => 'Hoàn tiền'
                            ])
                            ->prefixIcon('heroicon-o-tag')
                            ->searchable(),
                        Textarea::make('description')
                            ->label('Mô tả ngắn')
                            ->helperText('Mô tả ngắn gọn về chính sách để khách hàng dễ hiểu.')
                            ->placeholder('VD: Hoàn tiền 100% nếu hủy trước 24 giờ...')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Textarea::make('content')
                            ->label('Nội dung chi tiết')
                            ->helperText('Nội dung đầy đủ của chính sách, bao gồm các điều kiện và lưu ý.')
                            ->placeholder('VD: 
• Hoàn tiền 100% nếu hủy trước 24 giờ.
• Hoàn tiền 50% nếu hủy trước 12 giờ.
• Không hoàn tiền nếu hủy sau thời gian trên.')
                            ->rows(6)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Trạng thái hoạt động')
                            ->helperText('Bật/tắt hiển thị chính sách cho khách hàng.')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                    ->collapsible()
                    ->cloneable()
                    ->defaultItems(0)
                    ->addActionLabel('Thêm chính sách mới')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}