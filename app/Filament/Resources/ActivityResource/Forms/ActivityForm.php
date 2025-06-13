<?php

namespace App\Filament\Resources\ActivityResource\Forms;

use App\Filament\Components\FormFields\BasePricingField;
use App\Filament\Components\FormFields\CustomerCapacityPricingField;
use App\Filament\Components\FormFields\CustomerSegmentPricingField;
use App\Filament\Components\FormFields\MediaField;
use App\Filament\Components\FormFields\PolicyField;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class ActivityForm
{
    public static function make(Form $form): Form {
        return $form->schema([
            Tabs::make('Thông tin hoạt động')
                ->tabs([
                    Tabs\Tab::make('Thông tin cơ bản')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            Section::make('Thông tin chính')
                                ->description('Nhập các thông tin cơ bản về hoạt động')
                                ->icon('heroicon-m-document-text')
                                ->collapsible()
                                ->schema([
                                    Grid::make()
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Tên hoạt động')
                                                ->placeholder('Nhập tên hoạt động...')
                                                ->prefixIcon('heroicon-m-tag')
                                                ->required()
                                                ->maxLength(255)
                                                ->helperText('Tên này sẽ được hiển thị công khai cho người dùng')
                                                ->columnSpan(['md' => 2]),

                                            Toggle::make('is_active')
                                                ->label('Kích hoạt hoạt động')
                                                ->helperText('Chỉ những hoạt động được kích hoạt mới hiển thị công khai')
                                                ->default(true)
                                                ->inline(false)
                                                ->columnSpan(['md' => 1]),
                                        ])->columns(3),
                                    Textarea::make('short_description')
                                        ->label('Mô tả ngắn')
                                        ->placeholder('Nhập mô tả ngắn gọn về hoạt động...')
                                        ->rows(4)
                                        ->hintIcon('heroicon-o-pencil')
                                        ->maxLength(500)
                                        ->helperText('Mô tả ngắn gọn sử dụng trong danh sách và tóm tắt (tối đa 500 ký tự)'),
                                ]),

                            Section::make('Mô tả chi tiết')
                                ->description('Thông tin chi tiết về hoạt động')
                                ->icon('heroicon-m-document-text')
                                ->collapsible()
                                ->schema([
                                    RichEditor::make('long_description')
                                        ->label('Mô tả chi tiết')
                                        ->hintIcon('heroicon-o-pencil')
                                        ->placeholder('Nhập mô tả chi tiết về hoạt động...')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'underline',
                                            'bulletList',
                                            'orderedList',
                                            'link',
                                            'undo',
                                            'redo',
                                        ])
                                        ->helperText('Mô tả đầy đủ về hoạt động, có thể sử dụng định dạng văn bản'),

                                    Textarea::make('conditions')
                                        ->hintIcon('heroicon-o-pencil')
                                        ->rows(4)
                                        ->label('Điều kiện tham gia')
                                        ->placeholder('Nhập các điều kiện, yêu cầu để tham gia...')
                                        ->helperText('Các điều kiện, yêu cầu cần thiết để tham gia hoạt động'),
                                ]),
                        ]),

                    Tabs\Tab::make('Địa điểm & Số lượng')
                        ->icon('heroicon-m-map-pin')
                        ->schema([
                            Section::make('Thông tin địa điểm')
                                ->description('Vị trí tổ chức hoạt động')
                                ->icon('heroicon-m-map-pin')
                                ->schema([
                                    TextInput::make('location_area')
                                        ->label('Khu vực tổ chức')
                                        ->placeholder('Ví dụ: Sân cỏ, mương...')
                                        ->prefixIcon('heroicon-m-map-pin')
                                        ->maxLength(255)
                                        ->helperText('Khu vực hoặc địa điểm tổ chức hoạt động'),
                                ]),

                            Section::make('Số lượng tham gia')
                                ->description('Giới hạn số người tham gia')
                                ->icon('heroicon-m-users')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('min_participants')
                                                ->label('Số người tối thiểu')
                                                ->placeholder('0')
                                                ->prefixIcon('heroicon-m-user-minus')
                                                ->numeric()
                                                ->minValue(0)
                                                ->helperText('Số người tham gia tối thiểu để tổ chức'),

                                            TextInput::make('max_participants')
                                                ->label('Số người tối đa')
                                                ->placeholder('100')
                                                ->prefixIcon('heroicon-m-user-plus')
                                                ->numeric()
                                                ->minValue(1)
                                                ->helperText('Số người tham gia tối đa có thể chấp nhận'),
                                        ]),
                                ]),
                        ]),

                    Tabs\Tab::make('Media & Hình ảnh')
                        ->icon('heroicon-m-photo')
                        ->schema([
                            ...MediaField::make()
                        ]),

                    Tabs\Tab::make('Chính sách')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            PolicyField::make()
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
                ])
                ->columnSpanFull()
                ->persistTabInQueryString(),
        ]);
    }
}