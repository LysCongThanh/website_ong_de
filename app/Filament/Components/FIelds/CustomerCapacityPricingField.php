<?php

namespace App\Filament\Components\FIelds;

use App\Models\CustomerSegment;
use App\Models\PriceType;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

class CustomerCapacityPricingField
{
    public static function make() {
        return Tab::make('Giá nhóm')
            ->icon('heroicon-o-user-group')
            ->schema([
                Section::make('Giá theo số lượng khách')
                    ->description('Thiết lập giá ưu đãi theo số lượng người tham gia')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Repeater::make('capacityPrices')
                            ->relationship('capacityPrices')
                            ->label('Bảng giá theo số lượng')
                            ->schema([
                                Select::make('price_type_id')
                                    ->label('Loại thời điểm')
                                    ->placeholder('Chọn loại thời điểm áp dụng')
                                    ->required()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-clock')
                                    ->searchable()
                                    ->relationship('priceType', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ($record->description ? " - {$record->description}" : ''))
                                    ->createOptionForm([
                                        Grid::make(1)->schema([
                                            TextInput::make('name')
                                                ->label('Tên loại giá')
                                                ->placeholder('VD: Ngày thường, Cuối tuần, Ngày lễ...')
                                                ->required()
                                                ->maxLength(100),
                                        ]),

                                        Textarea::make('description')
                                            ->label('Mô tả chi tiết')
                                            ->placeholder('Mô tả khi nào áp dụng loại giá này...')
                                            ->rows(3)
                                            ->maxLength(500),
                                    ])
                                    ->createOptionModalHeading('Tạo mới loại giá')
                                    ->createOptionUsing(function (array $data) {
                                        return PriceType::create($data)->id;
                                    }),

                                Select::make('customer_segment_id')
                                    ->label('Đối tượng khách hàng')
                                    ->helperText('Chọn nhóm khách hàng áp dụng vé (ví dụ: Người lớn, Trẻ em, Sinh viên...)')
                                    ->relationship('customerSegment', 'name')
                                    ->required()
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->placeholder('Chọn đối tượng khách hàng...')
                                    ->searchPrompt('Nhập tên đối tượng để tìm kiếm...')
                                    ->noSearchResultsMessage('Không tìm thấy đối tượng khách hàng phù hợp.')
                                    ->loadingMessage('Đang tải danh sách đối tượng...')
                                    ->preload()
                                    ->createOptionForm([
                                        Section::make('Tạo đối tượng khách hàng mới')
                                            ->description('Nhập thông tin để tạo nhóm khách hàng mới cho vé.')
                                            ->icon('heroicon-o-plus-circle')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Tên đối tượng')
                                                    ->helperText('Tên hiển thị của nhóm khách hàng (tối đa 255 ký tự).')
                                                    ->placeholder('VD: Người lớn, Trẻ em, Sinh viên...')
                                                    ->prefixIcon('heroicon-o-user')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->autofocus()
                                                    ->hint('Tên sẽ hiển thị trong hệ thống và cho khách hàng.'),
                                                Textarea::make('description')
                                                    ->label('Mô tả')
                                                    ->helperText('Mô tả chi tiết về nhóm khách hàng (tùy chọn, tối đa 500 ký tự).')
                                                    ->placeholder('VD: Nhóm khách hàng từ 18 tuổi trở lên...')
                                                    ->rows(4)
                                                    ->maxLength(500)
                                                    ->hint('Mô tả giúp quản lý dễ nhận diện.'),
                                            ])
                                            ->columns(1),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return CustomerSegment::create($data)->id;
                                    })
                                    ->createOptionAction(function ($action) {
                                        return $action
                                            ->modalHeading('Tạo đối tượng khách hàng mới')
                                            ->modalSubmitActionLabel('Tạo đối tượng')
                                            ->slideOver()
                                            ->modalWidth('xl');
                                    })
                                    ->columnSpan(['md' => 1]),

                                TextInput::make('min_person')
                                    ->label('Số người tối thiểu')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->columnSpan(1),

                                TextInput::make('max_person')
                                    ->label('Số người tối đa')
                                    ->helperText('Để trống nếu không có giới hạn')
                                    ->numeric()
                                    ->minValue(fn (Get $get) => $get('min_person') ?? 1)
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label('Giá/người (VNĐ)')
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
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Thêm mức giá nhóm')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (empty($state['min_person']) || empty($state['price'])) {
                                    return 'Mức giá nhóm mới';
                                }

                                $priceType = !empty($state['price_type_id'])
                                    ? (PriceType::find($state['price_type_id'])?->name . ' - ' ?? '')
                                    : '';

                                $range = $state['min_person'] .
                                    (!empty($state['max_person']) ? '-' . $state['max_person'] : '+') .
                                    ' người';

                                // Convert price to numeric value for number_format
                                $price = is_string($state['price'])
                                    ? (int) str_replace([',', '.'], '', $state['price'])
                                    : (int) ($state['price'] ?? 0);

                                return $priceType . $range . ' - ' . number_format($price) . 'đ';
                            }),
                    ]),
            ]);
    }
}