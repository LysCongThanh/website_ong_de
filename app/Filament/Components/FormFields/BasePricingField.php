<?php

namespace App\Filament\Components\FormFields;

use App\Models\PriceType;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;

class BasePricingField
{
    public static function make(): array
    {
        return [
            Repeater::make('basePrices')
                ->label('')
                ->relationship('basePrices')
                ->schema([
                    Grid::make(1)
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
                                ->createOptionModalHeading('Tạo mới loại giá')
                                ->createOptionUsing(function (array $data) {
                                    return PriceType::create($data)->id;
                                }),

                            Grid::make(12)
                                ->schema([
                                    TextInput::make('price')
                                        ->label('Giá áp dụng')
                                        ->helperText('Giá cuối cùng khách hàng phải trả')
                                        ->placeholder('0')
                                        ->numeric()
                                        ->columnSpan(8)
                                        ->required()
                                        ->prefix('₫')
                                        ->step(1000)
                                        ->minValue(0)
                                        ->maxValue(999999999),

                                    Toggle::make('is_active')
                                        ->label('Kích hoạt')
                                        ->columnSpan(4)
                                        ->default(true)
                                        ->inline(false)
                                ])
                        ])
                ])
                ->defaultItems(1)
                ->addActionLabel('+ Thêm mức giá mới')
                ->reorderableWithButtons()
                ->collapsible()
                ->cloneable()
                ->grid(3)
                ->deleteAction(
                    fn(Action $action) => $action
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận xóa mức giá')
                        ->modalDescription('Bạn có chắc chắn muốn xóa mức giá này không?')
                )
                ->itemLabel(function (array $state): string {
                    $priceType = !empty($state['price_type_id'])
                        ? PriceType::find($state['price_type_id'])?->name
                        : null;

                    $price = !empty($state['price'])
                        ? number_format((float)str_replace([',', '.'], '', $state['price']), 0, ',', '.') . 'đ'
                        : '0đ';

                    $discount = !empty($state['discount_percent'])
                        ? " (-{$state['discount_percent']}%)"
                        : '';

                    $status = ($state['is_active'] ?? true)
                        ? '🟢'
                        : '🔴';

                    if ($priceType && $price !== '0đ') {
                        return "{$status} {$priceType}: {$price}{$discount}";
                    }

                    return 'Mức giá mới - Chưa hoàn thiện';
                })
                ->extraItemActions([
                    Action::make('duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (array $arguments, Repeater $component): void {
                            $items = $component->getState();
                            $item = $items[$arguments['item']];
                            unset($item['id']); // Remove ID để tạo item mới
                            $items[] = $item;
                            $component->state($items);
                        }),
                ]),

            self::createPricingSummary(),
        ];
    }

    private static function createPricingSummary(): Placeholder
    {
        return Placeholder::make('price_summary')
            ->label('')
            ->content(function ($get, $record) {
                $prices = $record?->basePrices ?? collect();

                if ($prices->isEmpty()) {
                    return new HtmlString('
                    <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-900/20 dark:via-indigo-900/20 dark:to-purple-900/20 border border-blue-200/60 dark:border-blue-800/40 rounded-2xl p-8 text-center backdrop-blur-sm">
                        <!-- Decorative background elements -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-300/20 to-purple-300/20 dark:from-blue-600/10 dark:to-purple-600/10 rounded-full -translate-y-16 translate-x-16 blur-2xl"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-indigo-300/20 to-blue-300/20 dark:from-indigo-600/10 dark:to-blue-600/10 rounded-full translate-y-12 -translate-x-12 blur-2xl"></div>
                        
                        <!-- Content -->
                        <div class="relative z-10">
                            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">Chưa có mức giá nào</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-base max-w-md mx-auto mb-6">Hãy thêm ít nhất một mức giá để khách hàng có thể đặt dịch vụ của bạn</p>
                            <div class="flex justify-center space-x-3">
                                <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium bg-blue-100/80 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 border border-blue-200/50 dark:border-blue-700/50">
                                    🚀 Bắt đầu thiết lập
                                </span>
                                <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium bg-purple-100/80 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200 border border-purple-200/50 dark:border-purple-700/50">
                                    💰 Tạo mức giá đầu tiên
                                </span>
                            </div>
                        </div>
                    </div>
                ');
                }

                $activePrices = $prices->where('is_active', true);
                $inactivePrices = $prices->where('is_active', false);
                $minPrice = $activePrices->min('price');
                $maxPrice = $activePrices->max('price');

                return new HtmlString("
               <div class='relative bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50 dark:from-purple-900/20 dark:via-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 mt-6 border border-gray-200/50 dark:border-gray-700/50 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-300'>
                    <!-- Decorative background elements -->
                    <div class='absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-300/20 to-emerald-300/20 dark:from-blue-600/10 dark:to-emerald-600/10 rounded-full -translate-y-20 translate-x-20 blur-3xl animate-pulse'></div>
                    <div class='absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-cyan-300/20 to-blue-300/20 dark:from-cyan-600/10 dark:to-blue-600/10 rounded-full translate-y-16 -translate-x-16 blur-2xl'></div>
                    
                    <!-- Header Section -->
                    <div class='relative z-10 mb-8'>
                        <div class='flex items-center justify-between mb-6'>
                            <div class='flex items-center space-x-4'>
                                <div class='w-10 h-10 border-gray-100 rounded-xl flex items-center justify-center shadow-lg'>
                                    <svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class='text-2xl font-bold text-gray-900 dark:text-gray-100'>Tổng quan bảng giá</h4>
                                    <p class='text-gray-600 dark:text-gray-400 text-sm'>Thống kê tổng hợp các mức giá dịch vụ</p>
                                </div>
                            </div>
                            <div class='flex items-center space-x-3'>
                                <span class='px-4 py-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm text-blue-700 dark:text-blue-300 text-sm font-bold rounded-full border border-blue-200/50 dark:border-blue-700/50 shadow-sm'>
                                    {$prices->count()} mức giá
                                </span>
                                " . ($activePrices->count() > 0 ? "
                                <span class='px-4 py-2 bg-green-100/80 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm font-semibold rounded-full border border-green-200/50 dark:border-green-700/50'>
                                    ✓ Hoạt động
                                </span>
                                " : "") . "
                            </div>
                        </div>
                    </div>

                    <!-- Main Stats Grid -->
                    <div class='relative z-10 grid grid-cols-2 md:grid-cols-4 gap-6 mb-8'>
                        <!-- Active Prices -->
                        <div class='group relative overflow-hidden bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-xl'>
                            <div class='absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400/20 to-emerald-400/20 rounded-full -translate-y-10 translate-x-10 blur-xl group-hover:blur-lg transition-all duration-300'></div>
                            <div class='relative'>
                                <div class='flex items-center justify-between mb-3'>
                                    <div class='w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg'>
                                        <svg class='w-5 h-5 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 font-medium group-hover:text-gray-600 dark:group-hover:text-gray-300'>Hoạt động</span>
                                </div>
                                <div class='text-3xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors mb-1'>
                                    {$activePrices->count()}
                                </div>
                                <div class='text-sm text-gray-500 dark:text-gray-400'>Mức giá đang áp dụng</div>
                            </div>
                        </div>

                        <!-- Inactive Prices -->
                        <div class='group relative overflow-hidden bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-xl'>
                            <div class='absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-400/20 to-pink-400/20 rounded-full -translate-y-10 translate-x-10 blur-xl group-hover:blur-lg transition-all duration-300'></div>
                            <div class='relative'>
                                <div class='flex items-center justify-between mb-3'>
                                    <div class='w-10 h-10 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg'>
                                        <svg class='w-5 h-5 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 font-medium group-hover:text-gray-600 dark:group-hover:text-gray-300'>Tạm dừng</span>
                                </div>
                                <div class='text-3xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors mb-1'>
                                    {$inactivePrices->count()}
                                </div>
                                <div class='text-sm text-gray-500 dark:text-gray-400'>Mức giá đã tắt</div>
                            </div>
                        </div>

                        <!-- Min Price -->
                        <div class='group relative overflow-hidden bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-xl'>
                            <div class='absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400/20 to-cyan-400/20 rounded-full -translate-y-10 translate-x-10 blur-xl group-hover:blur-lg transition-all duration-300'></div>
                            <div class='relative'>
                                <div class='flex items-center justify-between mb-3'>
                                    <div class='w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg'>
                                        <svg class='w-5 h-5 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 font-medium group-hover:text-gray-600 dark:group-hover:text-gray-300'>Thấp nhất</span>
                                </div>
                                <div class='text-2xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-1'>
                                    " . number_format($minPrice ?? 0, 0, ',', '.') . "
                                </div>
                                <div class='text-sm text-gray-500 dark:text-gray-400'>Giá tối thiểu (đ)</div>
                            </div>
                        </div>

                        <!-- Max Price -->
                        <div class='group relative overflow-hidden bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300 hover:scale-105 hover:shadow-xl'>
                            <div class='absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-400/20 to-pink-400/20 rounded-full -translate-y-10 translate-x-10 blur-xl group-hover:blur-lg transition-all duration-300'></div>
                            <div class='relative'>
                                <div class='flex items-center justify-between mb-3'>
                                    <div class='w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg'>
                                        <svg class='w-5 h-5 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'></path>
                                        </svg>
                                    </div>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 font-medium group-hover:text-gray-600 dark:group-hover:text-gray-300'>Cao nhất</span>
                                </div>
                                <div class='text-2xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors mb-1'>
                                    " . number_format($maxPrice ?? 0, 0, ',', '.') . "
                                </div>
                                <div class='text-sm text-gray-500 dark:text-gray-400'>Giá tối đa (đ)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Price List -->
                    " . ($activePrices->count() > 0 ? "
                    <div class='border-t border-gray-200 dark:border-gray-700 pt-4'>
                        <h5 class='font-medium text-gray-900 dark:text-gray-100 mb-3'>Danh sách giá</h5>
                        <div class='space-y-2'>
                            " . $activePrices->sortBy('price')->map(function ($price) {
                            $priceTypeName = $price->priceType?->name ?? 'Không xác định';
                            $priceValue = number_format($price->price, 0, ',', '.') . 'đ';

                            return "
                                <div class='flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg'>
                                    <div class='flex items-center space-x-3'>
                                        <div class='w-3 h-3 bg-green-500 rounded-full'></div>
                                        <span class='font-medium text-gray-900 dark:text-gray-100'>{$priceTypeName}</span>
                                    </div>
                                    <span class='font-bold text-gray-900 dark:text-gray-100'>{$priceValue}</span>
                                </div>";
                        })->join('') . "
                        </div>
                    </div>
                    " : "") . "
                </div>
            ");
            });
    }
}