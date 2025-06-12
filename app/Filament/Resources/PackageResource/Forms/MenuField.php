<?php

namespace App\Filament\Resources\PackageResource\Forms;

use Filament\Actions\Action;
use Filament\Forms\Components\{Card, Grid, Repeater, Section, Select, TagsInput, Textarea, TextInput};

class MenuField
{
    public static function make(): array
    {
        return [
            Repeater::make('menus')
                ->relationship()
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('name')
                                ->label('Tên menu')
                                ->placeholder('Nhập tên menu...')
                                ->required()
                                ->maxLength(100)
                                ->helperText('VD: Menu 1')
                                ->columnSpan(2),

                            Select::make('type')
                                ->label('Loại bữa ăn')
                                ->options([
                                    'breakfast' => '🌅 Bữa sáng',
                                    'lunch' => '🌞 Bữa trưa',
                                    'dinner' => '🌙 Bữa tối',
                                    'snack' => '🍿 Ăn vặt',
                                ])
                                ->native(false)
                                ->required()
                                ->columnSpan(1),
                        ]),

                    Textarea::make('description')
                        ->label('Mô tả bữa ăn')
                        ->placeholder('Mô tả chi tiết về bữa ăn, đặc điểm nổi bật...')
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText('Mô tả sẽ hiển thị cho khách hàng'),

                    // Menu Options Section
                    Section::make('Thực đơn & Combo')
                        ->description('Quản lý các combo và món ăn trong bữa ăn')
                        ->schema([
                            Repeater::make('options')
                                ->relationship()
                                ->schema([
                                    // Combo Information
                                    Card::make()
                                        ->schema([
                                            Grid::make(2)
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->label('Tên combo/set')
                                                        ->placeholder('VD: Combo Sáng Truyền Thống')
                                                        ->required()
                                                        ->maxLength(150)
                                                        ->prefixIcon('heroicon-m-bookmark')
                                                        ->columnSpan(1),

                                                    TextInput::make('price')
                                                        ->label('Giá combo')
                                                        ->placeholder('VD: 50000')
                                                        ->numeric()
                                                        ->prefix('₫')
                                                        ->minValue(0)
                                                        ->columnSpan(1),
                                                ]),

                                            Textarea::make('description')
                                                ->label('Mô tả combo')
                                                ->placeholder('Mô tả chi tiết về combo, nguyên liệu, cách chế biến...')
                                                ->rows(3)
                                                ->maxLength(500)
                                                ->helperText('Thông tin này sẽ hiển thị cho khách hàng'),
                                        ]),

                                    // Menu Items Section
                                    Section::make('Danh sách món ăn')
                                        ->description('Các món ăn trong combo này')
                                        ->schema([
                                            Repeater::make('items')
                                                ->relationship()
                                                ->schema([
                                                    Card::make()
                                                        ->schema([
                                                            Grid::make(4)
                                                                ->schema([
                                                                    TextInput::make('name')
                                                                        ->label('Tên món')
                                                                        ->placeholder('VD: Phở bò tái')
                                                                        ->required()
                                                                        ->maxLength(100)
                                                                        ->prefixIcon('heroicon-m-cake')
                                                                        ->columnSpan(2),

                                                                    TextInput::make('quantity')
                                                                        ->label('Số lượng')
                                                                        ->numeric()
                                                                        ->default(1)
                                                                        ->minValue(1)
                                                                        ->maxValue(20)
                                                                        ->columnSpan(1),

                                                                    Select::make('unit')
                                                                        ->label('Đơn vị')
                                                                        ->options([
                                                                            'bowl' => '🥣 Tô',
                                                                            'plate' => '🍽️ Đĩa',
                                                                            'cup' => '☕ Ly',
                                                                            'piece' => '🔢 Cái',
                                                                            'portion' => '🍱 Phần',
                                                                            'bottle' => '🍾 Chai',
                                                                            'glass' => '🥛 Cốc',
                                                                        ])
                                                                        ->native(false)
                                                                        ->columnSpan(1),
                                                                ]),
                                                        ])
                                                ])
                                                ->itemLabel(function (array $state): ?string {
                                                    $name = $state['name'] ?? '';
                                                    $quantity = $state['quantity'] ?? 1;
                                                    $unit = $state['unit'] ?? '';

                                                    if (empty($name)) return 'Món ăn mới';

                                                    $unitLabels = [
                                                        'bowl' => 'tô',
                                                        'plate' => 'đĩa',
                                                        'cup' => 'ly',
                                                        'piece' => 'cái',
                                                        'portion' => 'phần',
                                                        'bottle' => 'chai',
                                                        'glass' => 'cốc',
                                                    ];

                                                    $unitText = $unitLabels[$unit] ?? $unit;
                                                    return "{$name} ({$quantity} {$unitText})";
                                                })
                                                ->addActionLabel('➕ Thêm món ăn')

                                                ->reorderableWithButtons()
                                                ->collapsible()
                                                ->defaultItems(1)
                                                ->minItems(1)
                                        ])
                                        ->collapsible()
                                        ->persistCollapsed(false)
                                ])
                                ->itemLabel(function (array $state): ?string {
                                    $name = $state['name'] ?? '';
                                    $price = $state['price'] ?? '';
                                    $available = $state['is_available'] ?? true;

                                    if (empty($name)) return 'Combo mới';

                                    $status = $available ? '✅' : '❌';
                                    $priceText = $price ? number_format($price) . '₫' : '';

                                    return "{$status} {$name}" . ($priceText ? " - {$priceText}" : '');
                                })
                                ->addActionLabel('➕ Thêm combo mới')

                                ->reorderableWithButtons()
                                ->collapsible()
                                ->defaultItems(1)
                                ->minItems(1)
                        ])
                        ->collapsible()
                        ->persistCollapsed(false)
                ])
                ->itemLabel(function (array $state): ?string {
                    $name = $state['name'] ?? '';
                    $type = $state['type'] ?? '';

                    if (empty($name)) return 'Bữa ăn mới';

                    $typeLabels = [
                        'breakfast' => '🌅 Sáng',
                        'lunch' => '🌞 Trưa',
                        'dinner' => '🌙 Tối',
                        'snack' => '🍿 Vặt',
                    ];

                    $typeText = $typeLabels[$type] ?? $type;
                    return "{$name} - {$typeText}";
                })
                ->addActionLabel('➕ Thêm bữa ăn mới')

                ->reorderableWithButtons()
                ->collapsible()
                ->defaultItems(1)
                ->minItems(1)
                ->helperText('📋 Quản lý toàn bộ thực đơn theo từng bữa ăn. Mỗi bữa ăn có thể có nhiều combo khác nhau.')
                ->columnSpanFull()
        ];
    }
}