<?php

namespace App\Filament\Components\FormFields;

use App\Models\CustomerSegment;
use App\Models\PriceType;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class CustomerSegmentPricingField
{
    public static function make(): array
    {
        return [
            Repeater::make('segmentPrices')
                ->relationship('segmentPrices')
                ->label('Bảng giá theo đối tượng')
                ->schema([
                    Grid::make(12)
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
                                ->columnSpan(5),

                            Select::make('price_type_id')
                                ->label('Phân loại giá')
                                ->helperText('Chọn loại giá áp dụng (có thể là theo thời gian, sự kiện, hoặc chiến lược kinh doanh)')
                                ->placeholder('Chọn hoặc tạo mới phân loại giá...')
                                ->required()
                                ->preload()
                                ->prefixIcon('heroicon-o-tag')
                                ->searchable()
                                ->relationship('priceType', 'name')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('Tên phân loại')
                                        ->helperText('VD: Giá niêm yết, Giá khuyến mại, Giá cuối tuần, Giá Black Friday...')
                                        ->placeholder('Nhập tên phân loại giá')
                                        ->required()
                                        ->maxLength(100)
                                        ->live(onBlur: true),
                                    Textarea::make('description')
                                        ->label('Mô tả chi tiết')
                                        ->helperText('Giải thích khi nào và điều kiện áp dụng loại giá này')
                                        ->placeholder('VD: Áp dụng cho tất cả ngày trong tuần từ 9h-17h, không áp dụng vào ngày lễ...')
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    Grid::make(2)->schema([
                                        TextInput::make('priority')
                                            ->label('Độ ưu tiên')
                                            ->helperText('Số cao hơn = ưu tiên cao hơn')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(100),

                                        Toggle::make('is_default')
                                            ->label('Mặc định')
                                            ->helperText('Sử dụng làm giá mặc định')
                                            ->default(false),
                                    ])
                                ])
                                ->columnSpan(5)
                                ->createOptionModalHeading('Tạo mới loại giá')
                                ->createOptionUsing(function (array $data) {
                                    return PriceType::create($data)->id;
                                }),

                            Toggle::make('is_active')
                                ->label('Kích hoạt')
                                ->helperText('Bật/tắt mức giá này')
                                ->default(true)
                                ->inline(false)
                                ->columnSpan(2),
                        ]),

                    Grid::make(12)
                        ->schema([
                            TextInput::make('price')
                                ->label('Giá áp dụng')
                                ->helperText('Nhập 0 nếu miễn phí cho đối tượng này')
                                ->numeric()
                                ->required()
                                ->prefix('₫')
                                ->step(1000)
                                ->minValue(0)
                                ->maxValue(999999999)
                                ->placeholder('0')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set) {
                                    // Auto-format and set helper text
                                    if ($state == 0) {
                                        $set('pricing_note', 'Miễn phí');
                                    } else {
                                        $formatted = number_format($state, 0, ',', '.');
                                        $set('pricing_note', $formatted . 'đ');
                                    }
                                })
                                ->columnSpan(9),

                            // Price Preview
                            Placeholder::make('pricing_note')
                                ->label('Hiển thị giá')
                                ->content(function (Get $get) {
                                    $price = $get('price') ?? 0;
                                    if ($price == 0) {
                                        return new HtmlString('<span class="text-green-600 dark:text-green-400 font-semibold">🎁 Miễn phí</span>');
                                    }
                                    return new HtmlString('<span class="text-blue-600 dark:text-blue-400 font-semibold">💰 ' . number_format($price, 0, ',', '.') . 'đ</span>');
                                })
                                ->columnSpan(3),
                        ])
                ])
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
            self::createSegmentPricingSummary()
        ];
    }

    /**
     * Create segment pricing summary
     */
    private static function createSegmentPricingSummary(): Placeholder
    {
        return Placeholder::make('segment_summary')
            ->label('')
            ->content(function ($get, $record) {
                $prices = $record?->segmentPrices ?? collect();

                if ($prices->isEmpty()) {
                    return new HtmlString('
                    <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Chưa có giá theo đối tượng</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Thêm mức giá phù hợp cho từng nhóm khách hàng</p>
                    </div>
                ');
                }

                $activePrices = $prices->where('is_active', true);
                $inactivePrices = $prices->where('is_active', false);
                $freePrices = $activePrices->where('price', 0);
                $paidPrices = $activePrices->where('price', '>', 0);
                $segments = $activePrices->pluck('customerSegment.name')->filter()->unique();

                return new HtmlString("
                <div class='bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 mt-4'>
                    <!-- Header -->
                    <div class='flex items-center justify-between mb-6'>
                        <div class='flex items-center space-x-3'>
                            <div class='w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center'>
                                <svg class='w-4 h-4 text-purple-600 dark:text-purple-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'></path>
                                </svg>
                            </div>
                            <h4 class='text-lg font-semibold text-gray-900 dark:text-gray-100'>Tổng quan giá theo đối tượng</h4>
                        </div>
                        <span class='text-sm text-gray-500 dark:text-gray-400'>
                            {$prices->count()} mức giá
                        </span>
                    </div>

                    <!-- Stats -->
                    <div class='grid grid-cols-2 md:grid-cols-4 gap-4 mb-6'>
                        <div class='text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800'>
                            <div class='text-2xl font-bold text-green-600 dark:text-green-400'>
                                {$activePrices->count()}
                            </div>
                            <div class='text-sm text-green-600 dark:text-green-400'>Đang hoạt động</div>
                        </div>
                        
                        <div class='text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800'>
                            <div class='text-2xl font-bold text-red-600 dark:text-red-400'>
                                {$inactivePrices->count()}
                            </div>
                            <div class='text-sm text-red-600 dark:text-red-400'>Đã tắt</div>
                        </div>
                        
                        <div class='text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800'>
                            <div class='text-2xl font-bold text-blue-600 dark:text-blue-400'>
                                {$freePrices->count()}
                            </div>
                            <div class='text-sm text-blue-600 dark:text-blue-400'>Miễn phí</div>
                        </div>
                        
                        <div class='text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800'>
                            <div class='text-2xl font-bold text-purple-600 dark:text-purple-400'>
                                {$paidPrices->count()}
                            </div>
                            <div class='text-sm text-purple-600 dark:text-purple-400'>Có phí</div>
                        </div>
                    </div>

                    <!-- Active Prices List -->
                    " . ($activePrices->count() > 0 ? "
                    <div class='border-t border-gray-200 dark:border-gray-700 pt-4'>
                        <h5 class='font-medium text-gray-900 dark:text-gray-100 mb-3'>Danh sách giá theo đối tượng</h5>
                        <div class='space-y-2'>
                            " . $activePrices->sortBy('price')->map(function ($price) {
                            $segmentName = $price->customerSegment?->name ?? 'Không xác định';
                            $priceTypeName = $price->priceType?->name ?? '';
                            $priceValue = $price->price == 0 ? 'Miễn phí' : number_format($price->price, 0, ',', '.') . 'đ';
                            $badgeColor = $price->price == 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';

                            return "
                                <div class='flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg'>
                                    <div class='flex items-center space-x-3'>
                                        <div class='w-3 h-3 bg-green-500 rounded-full'></div>
                                        <div>
                                            <span class='font-medium text-gray-900 dark:text-gray-100'>{$segmentName}</span>
                                            " . ($priceTypeName ? "<span class='text-sm text-gray-500 dark:text-gray-400 ml-2'>• {$priceTypeName}</span>" : "") . "
                                        </div>
                                    </div>
                                    <span class='px-2 py-1 rounded-full text-xs font-medium {$badgeColor}'>
                                        {$priceValue}
                                    </span>
                                </div>";
                        })->join('') . "
                        </div>
                    </div>
                    " : "") . "

                    <!-- Customer Segments Overview -->
                    " . ($segments->isNotEmpty() ? "
                    <div class='border-t border-gray-200 dark:border-gray-700 pt-4 mt-4'>
                        <h5 class='font-medium text-gray-900 dark:text-gray-100 mb-3'>Đối tượng được phục vụ</h5>
                        <div class='flex flex-wrap gap-2'>
                            " . $segments->map(function ($segment) {
                            return "<span class='px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 text-sm rounded-full border border-purple-200 dark:border-purple-700'>
                                    👥 {$segment}
                                </span>";
                        })->join('') . "
                        </div>
                    </div>
                    " : "") . "
                </div>
            ");
            });
    }
}