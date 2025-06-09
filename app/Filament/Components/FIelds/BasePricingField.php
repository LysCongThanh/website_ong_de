<?php

namespace App\Filament\Components\FIelds;

use App\Models\PriceType;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;

class BasePricingField
{
    public static function make(): Tab
    {
        return Tab::make('Thiết lập giá')
            ->icon('heroicon-o-currency-dollar')
            ->badge(fn($record) => $record?->basePrices?->count() ?? 0)
            ->badgeColor('success')
            ->schema([
                Section::make()
                    ->heading('Bảng giá dịch vụ')
                    ->description('Thiết lập giá theo từng loại thời điểm và điều kiện đặc biệt')
                    ->icon('heroicon-o-table-cells')
                    ->schema([
                        Repeater::make('basePrices')
                            ->label('')
                            ->relationship('basePrices')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        // Loại giá - chiếm 4 cột
                                        Select::make('price_type_id')
                                            ->label('Loại thời điểm')
                                            ->placeholder('Chọn loại thời điểm áp dụng')
                                            ->required()
                                            ->preload()
                                            ->prefixIcon('heroicon-o-clock')
                                            ->searchable()
                                            ->relationship('priceType', 'name')
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ($record->description ? " - {$record->description}" : ''))
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

                                        // Giá - chiếm 3 cột
                                        TextInput::make('price')
                                            ->label('Giá dịch vụ')
                                            ->placeholder('0')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₫')
                                            ->step(1000)
                                            ->minValue(0)
                                            ->maxValue(999999999),

                                        // Trạng thái - chiếm 2 cột
                                        Toggle::make('is_active')
                                            ->label('Kích hoạt')
                                            ->default(true)
                                            ->inline(true)
                                    ])
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('+ Thêm mức giá mới')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->grid(2)
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

                        // Tổng kết giá
                        Placeholder::make('price_summary')
                            ->label('')
                            ->content(function ($get, $record) {
                                $prices = $record?->basePrices ?? collect();

                                if ($prices->isEmpty()) {
                                    return new HtmlString('<div class="text-gray-500 text-center py-4">Chưa có mức giá nào được thiết lập</div>');
                                }

                                $activePrices = $prices->where('is_active', true);
                                $minPrice = $activePrices->min('price');
                                $maxPrice = $activePrices->max('price');

                                return new HtmlString("
                            <div class='bg-gray-50 rounded-lg p-4 mt-4'>
                                <h4 class='font-medium text-gray-900 mb-2'>📊 Tổng quan bảng giá</h4>
                                <div class='grid grid-cols-3 gap-4 text-sm'>
                                    <div>
                                        <span class='text-gray-600'>Tổng số mức giá:</span>
                                        <div class='font-semibold text-blue-600'>{$prices->count()} mức</div>
                                    </div>
                                    <div>
                                        <span class='text-gray-600'>Đang hoạt động:</span>
                                        <div class='font-semibold text-green-600'>{$activePrices->count()} mức</div>
                                    </div>
                                    <div>
                                        <span class='text-gray-600'>Khoảng giá:</span>
                                        <div class='font-semibold text-orange-600'>
                                            " . ($minPrice ? number_format($minPrice, 0, ',', '.') . 'đ' : '0đ') . " - " .
                                    ($maxPrice ? number_format($maxPrice, 0, ',', '.') . 'đ' : '0đ') . "
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ");
                            }),
                    ]),
            ]);
    }
}