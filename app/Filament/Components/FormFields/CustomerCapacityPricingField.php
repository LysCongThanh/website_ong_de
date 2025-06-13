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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class CustomerCapacityPricingField
{
    public static function make(): array
    {
        return [
            Repeater::make('capacityPrices')
                ->relationship('capacityPrices')
                ->label('Bảng giá theo số lượng')
                ->schema([
                    Grid::make(12)
                        ->schema([
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
                                        ->maxLength(100),
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

                            Select::make('customer_segment_id')
                                ->label('Đối tượng khách hàng')
                                ->helperText('Phân loại khách hàng để áp dụng giá phù hợp')
                                ->relationship('customerSegment', 'name')
                                ->required()
                                ->columnSpan(5)
                                ->searchable()
                                ->prefixIcon('heroicon-o-users')
                                ->placeholder('Chọn đối tượng khách hàng...')
                                ->preload()
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    $icon = match($record->type ?? 'general') {
                                        'adult' => '👨‍👩‍👧‍👦',
                                        'child' => '🧒',
                                        'student' => '🎓',
                                        'senior' => '👴',
                                        'vip' => '⭐',
                                        'group' => '👥',
                                        default => '👤'
                                    };
                                    return $icon . ' ' . $record->name .
                                        ($record->age_range ? " ({$record->age_range})" : '') .
                                        ($record->description ? " • {$record->description}" : '');
                                })
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('Tên đối tượng')
                                        ->helperText('Tên hiển thị của nhóm khách hàng (tối đa 255 ký tự).')
                                        ->placeholder('VD: Người lớn, Trẻ em, Sinh viên...')
                                        ->prefixIcon('heroicon-o-user')
                                        ->required()
                                        ->maxLength(255)
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
                                ->createOptionUsing(function (array $data): int {
                                    return CustomerSegment::create($data)->id;
                                })
                                ->createOptionAction(function ($action) {
                                    return $action
                                        ->modalHeading('Tạo đối tượng khách hàng mới')
                                        ->modalSubmitActionLabel('Tạo đối tượng')
                                        ->slideOver()
                                        ->modalWidth('xl');
                                }),
                            Toggle::make('is_active')
                                ->label('Kích hoạt')
                                ->default(true)
                                ->inline(false)
                                ->columnSpan(2),
                        ]),

                    Grid::make(12)->schema([
                        // Capacity range
                        TextInput::make('min_person')
                            ->label('Số người tối thiểu')
                            ->helperText('Số lượng khách tối thiểu để áp dụng mức giá này')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(1000)
                            ->placeholder('1')
                            ->prefixIcon('heroicon-o-minus')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set) {
                                // Auto-suggest max_person if not set
                                if ($state && $state > 1) {
                                    $set('max_person', $state + 9); // Suggest range of 10
                                }
                            })
                            ->columnSpan(3),

                        TextInput::make('max_person')
                            ->label('Số người tối đa')
                            ->helperText('Để trống nếu không giới hạn số lượng tối đa')
                            ->numeric()
                            ->placeholder('Không giới hạn')
                            ->prefixIcon('heroicon-o-plus')
                            ->minValue(fn(Get $get) => max(1, $get('min_person') ?? 1))
                            ->maxValue(10000)
                            ->columnSpan(3),

                        // Pricing
                        TextInput::make('price')
                            ->label('Giá/người')
                            ->helperText('Giá tiền cho mỗi khách hàng theo mức giá này')
                            ->numeric()
                            ->required()
                            ->prefix('₫')
                            ->step(1000)
                            ->minValue(0)
                            ->maxValue(999999999)
                            ->placeholder('0')
                            ->live(onBlur: true)
                            ->columnSpan(4),

                        // Price calculation preview
                        Placeholder::make('price_preview')
                            ->label('Ví dụ tổng giá')
                            ->content(function (Get $get) {
                                $minPerson = $get('min_person') ?? 0;
                                $maxPerson = $get('max_person');
                                $price = $get('price') ?? 0;

                                if (!$minPerson || !$price) {
                                    return new HtmlString('<span class="text-gray-400 text-sm">Nhập số lượng và giá để xem ví dụ</span>');
                                }

                                $minTotal = $minPerson * $price;
                                $maxTotal = $maxPerson ? ($maxPerson * $price) : null;

                                $preview = '<div class="text-sm space-y-1">';
                                $preview .= '<div class="text-blue-600 font-medium">';
                                $preview .= $minPerson . ' người: ' . number_format($minTotal, 0, ',', '.') . 'đ';
                                $preview .= '</div>';

                                if ($maxTotal && $maxTotal !== $minTotal) {
                                    $preview .= '<div class="text-green-600 font-medium">';
                                    $preview .= $maxPerson . ' người: ' . number_format($maxTotal, 0, ',', '.') . 'đ';
                                    $preview .= '</div>';
                                }
                                $preview .= '</div>';

                                return new HtmlString($preview);
                            })
                            ->columnSpan(2),
                        ])
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
                        ? (int)str_replace([',', '.'], '', $state['price'])
                        : (int)($state['price'] ?? 0);

                    return $priceType . $range . ' - ' . number_format($price) . 'đ';
                }),

            self::createCapacityPricingSummary()
        ];
    }


    /**
     * Enhanced Capacity Pricing Summary with modern UI and dark mode
     */
    private static function createCapacityPricingSummary(): Placeholder
    {
        return Placeholder::make('capacity_summary')
            ->label('')
            ->content(function ($get, $record) {
                $prices = $record?->capacityPrices ?? collect();

                if ($prices->isEmpty()) {
                    return new HtmlString('
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-xl p-8 text-center backdrop-blur-sm">
                        <div class="w-16 h-16 mx-auto mb-4 bg-amber-100 dark:bg-amber-900/40 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200 mb-2">Chưa có mức giá nhóm</h3>
                        <p class="text-amber-700 dark:text-amber-300 text-sm max-w-md mx-auto">Thêm mức giá theo số lượng để khách hàng nhóm có thể đặt dịch vụ của bạn</p>
                        <div class="mt-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-200/50 dark:bg-amber-800/50 text-amber-800 dark:text-amber-200">
                                💡 Bắt đầu với mức giá đầu tiên
                            </span>
                        </div>
                    </div>
                ');
                }

                $activePrices = $prices->where('is_active', true);
                $inactivePrices = $prices->where('is_active', false);
                $segments = $activePrices->pluck('customerSegment.name')->filter()->unique();
                $minCapacity = $activePrices->min('min_person');
                $maxCapacity = $activePrices->max('max_person');
                $minPrice = $activePrices->min('price');
                $maxPrice = $activePrices->max('price');
                $avgPrice = $activePrices->avg('price');

                // Calculate total potential revenue
                $totalMinRevenue = $activePrices->sum(fn($price) => ($price->min_person ?? 0) * ($price->price ?? 0));
                $totalMaxRevenue = $activePrices->sum(fn($price) => ($price->max_person ?? $price->min_person ?? 0) * ($price->price ?? 0));

                return new HtmlString("
                <div class='relative bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50 dark:from-purple-900/20 dark:via-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 mt-6 border border-gray-200/50 dark:border-gray-700/50 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-300'>
                    <!-- Decorative elements -->
                    <div class='absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-200/30 to-blue-200/30 dark:from-purple-600/10 dark:to-blue-600/10 rounded-full -translate-y-16 translate-x-16 blur-2xl'></div>
                    <div class='absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-blue-200/30 to-indigo-200/30 dark:from-blue-600/10 dark:to-indigo-600/10 rounded-full translate-y-12 -translate-x-12 blur-2xl'></div>
                    
                    <!-- Header -->
                    <div class='relative'>
                        <div class='flex items-center justify-between mb-6'>
                            <div class='flex items-center space-x-3'>
                                <div class='w-10 h-10 border-gray-100 rounded-xl flex items-center justify-center shadow-lg'>
                                    <svg class='w-5 h-5 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class='text-xl font-bold text-gray-900 dark:text-gray-100'>Tổng quan bảng giá nhóm</h4>
                                    <p class='text-sm text-gray-600 dark:text-gray-400'>Phân tích chi tiết mức giá theo số lượng</p>
                                </div>
                            </div>
                            <div class='flex items-center space-x-2'>
                                <span class='px-3 py-1 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm text-purple-700 dark:text-purple-300 text-sm font-semibold rounded-full border border-purple-200/50 dark:border-purple-700/50'>
                                    {$prices->count()} mức giá
                                </span>
                                " . ($activePrices->count() > 0 ? "
                                <span class='px-3 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm font-medium rounded-full'>
                                    ✓ Hoạt động
                                </span>
                                " : "") . "
                            </div>
                        </div>
                        
                        <!-- Stats Grid -->
                        <div class='grid grid-cols-2 md:grid-cols-4 gap-4 mb-6'>
                            <!-- Active Prices -->
                            <div class='group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-lg'>
                                <div class='flex items-center justify-between mb-2'>
                                    <div class='w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center'>
                                        <svg class='w-4 h-4 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'>Hoạt động</span>
                                </div>
                                <div class='text-2xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors'>
                                    {$activePrices->count()}
                                </div>
                                <div class='text-xs text-gray-500 dark:text-gray-400'>Mức giá đang áp dụng</div>
                            </div>

                            <!-- Inactive Prices -->
                            <div class='group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-lg'>
                                <div class='flex items-center justify-between mb-2'>
                                    <div class='w-8 h-8 bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg flex items-center justify-center'>
                                        <svg class='w-4 h-4 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 14L21 3m0 0h-5m5 0v5M9 21H4a2 2 0 01-2-2V9a2 2 0 012-2h5m0 0L21 3'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'>Tạm dừng</span>
                                </div>
                                <div class='text-2xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-gray-600 dark:group-hover:text-gray-400 transition-colors'>
                                    {$inactivePrices->count()}
                                </div>
                                <div class='text-xs text-gray-500 dark:text-gray-400'>Mức giá đã tắt</div>
                            </div>

                            <!-- Customer Segments -->
                            <div class='group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-lg'>
                                <div class='flex items-center justify-between mb-2'>
                                    <div class='w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center'>
                                        <svg class='w-4 h-4 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'>Phân khúc</span>
                                </div>
                                <div class='text-2xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors'>
                                    {$segments->count()}
                                </div>
                                <div class='text-xs text-gray-500 dark:text-gray-400'>Đối tượng khách hàng</div>
                            </div>

                            <!-- Capacity Range -->
                            <div class='group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-lg'>
                                <div class='flex items-center justify-between mb-2'>
                                    <div class='w-8 h-8 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center'>
                                        <svg class='w-4 h-4 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'>Số lượng</span>
                                </div>
                                <div class='text-lg font-bold text-gray-900 dark:text-gray-100 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors'>
                                    {$minCapacity}-" . ($maxCapacity ?? '∞') . "
                                </div>
                                <div class='text-xs text-gray-500 dark:text-gray-400'>Khoảng số người</div>
                            </div>
                        </div>

                        <!-- Price Analysis -->
                        " . ($activePrices->count() > 0 ? "
                        <div class='bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-5 border border-gray-200/50 dark:border-gray-700/50 mb-6'>
                            <h5 class='font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center'>
                                <svg class='w-5 h-5 mr-2 text-purple-600 dark:text-purple-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'></path>
                                </svg>
                                Phân tích giá
                            </h5>
                            <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
                                <div class='text-center p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200/50 dark:border-green-700/50'>
                                    <div class='text-sm text-green-600 dark:text-green-400 font-medium mb-1'>Giá thấp nhất</div>
                                    <div class='text-2xl font-bold text-green-700 dark:text-green-300'>
                                        " . number_format($minPrice ?? 0, 0, ',', '.') . "đ
                                    </div>
                                </div>
                                <div class='text-center p-4 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-lg border border-blue-200/50 dark:border-blue-700/50'>
                                    <div class='text-sm text-blue-600 dark:text-blue-400 font-medium mb-1'>Giá trung bình</div>
                                    <div class='text-2xl font-bold text-blue-700 dark:text-blue-300'>
                                        " . number_format($avgPrice ?? 0, 0, ',', '.') . "đ
                                    </div>
                                </div>
                                <div class='text-center p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg border border-purple-200/50 dark:border-purple-700/50'>
                                    <div class='text-sm text-purple-600 dark:text-purple-400 font-medium mb-1'>Giá cao nhất</div>
                                    <div class='text-2xl font-bold text-purple-700 dark:text-purple-300'>
                                        " . number_format($maxPrice ?? 0, 0, ',', '.') . "đ
                                    </div>
                                </div>
                            </div>
                        </div>
                        " : "") . "

                        <!-- Customer Segments -->
                        " . ($segments->isNotEmpty() ? "
                        <div class='bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-5 border border-gray-200/50 dark:border-gray-700/50'>
                            <h5 class='font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center'>
                                <svg class='w-5 h-5 mr-2 text-blue-600 dark:text-blue-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'></path>
                                </svg>
                                Đối tượng khách hàng được phục vụ
                            </h5>
                            <div class='flex flex-wrap gap-2'>
                                " . $segments->map(function ($segment) {
                            return "<span class='inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-100 to-cyan-100 dark:from-blue-900/40 dark:to-cyan-900/40 text-blue-800 dark:text-blue-200 text-sm font-medium rounded-full border border-blue-200/50 dark:border-blue-700/50 hover:from-blue-200 hover:to-cyan-200 dark:hover:from-blue-800/60 dark:hover:to-cyan-800/60 transition-all duration-200'>
                                        <svg class='w-3 h-3 mr-1.5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'></path>
                                        </svg>
                                        {$segment}
                                    </span>";
                        })->join('') . "
                            </div>
                        </div>
                        " : "") . "
                    </div>
                </div>
            ");
            });
    }
}
