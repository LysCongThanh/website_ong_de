<?php

namespace App\Filament\Components\FormFields;

use App\Models\CustomerSegment;
use App\Models\PriceType;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

class CustomerSegmentPricingField
{
    public static function make(): Section
    {
        return Section::make('Giá theo phân khúc khách hàng')
            ->description('Thiết lập giá đặc biệt cho từng đối tượng khách hàng (trẻ em, người cao tuổi, sinh viên, ...)')
            ->icon('heroicon-o-user-circle')
            ->schema([
                Repeater::make('segmentPrices')
                    ->relationship('segmentPrices')
                    ->label('Bảng giá theo đối tượng')
                    ->schema([
                        Select::make('customer_segment_id')
                            ->label('Đối tượng khách hàng')
                            ->relationship('customerSegment', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Tên đối tượng')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Mô tả'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return CustomerSegment::create($data)->id; // Tạo bản ghi mới và trả về ID
                            })
                            ->columnSpan(1),

                        Select::make('price_type_id')
                            ->label('Loại thời điểm')
                            ->options(PriceType::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        TextInput::make('price')
                            ->label('Giá (VNĐ)')
                            ->helperText('Nhập 0 nếu miễn phí')
                            ->numeric()
                            ->required()
                            ->prefix('đ')
                            ->step(1000)
                            ->minValue(0)
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->default(true)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Thêm giá đối tượng')
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {
                        if (empty($state['customer_segment_id']) || !isset($state['price'])) {
                            return 'Giá đối tượng mới';
                        }

                        $segment = CustomerSegment::find($state['customer_segment_id'])?->name ?? '';
                        $priceType = !empty($state['price_type_id'])
                            ? ' (' . PriceType::find($state['price_type_id'])?->name . ')'
                            : '';

                        // Xử lý $state['price'] để chuyển chuỗi thành số
                        $priceValue = is_numeric(str_replace([',', '.'], '', $state['price']))
                            ? (float)str_replace([',', '.'], '', $state['price'])
                            : 0;
                        $price = $priceValue == 0 ? 'Miễn phí' : number_format($priceValue) . 'đ';

                        return $segment . $priceType . ': ' . $price;
                    }),
            ]);
    }
}