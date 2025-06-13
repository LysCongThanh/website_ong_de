<?php

namespace App\Filament\Resources\PackageResource\Forms;

use Filament\Actions\Action;
use Filament\Forms\Components\{Card, Grid, Radio, Repeater, Section, Select, TagsInput, Textarea, TextInput};
use function Laravel\Prompts\suggest;

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
                                ->helperText('VD: Menu Sáng Truyền Thống')
                                ->columnSpan(2),

                            Select::make('type')
                                ->label('Loại bữa ăn')
                                ->options([
                                    'breakfast' => 'Bữa sáng',
                                    'lunch' => 'Bữa trưa',
                                    'dinner' => 'Bữa tối',
                                    'snack' => 'Ăn vặt',
                                ])
                                ->native(false)
                                ->required()
                                ->columnSpan(1),
                        ]),

                    Textarea::make('description')
                        ->label('Mô tả menu')
                        ->placeholder('Mô tả chi tiết về menu, đặc điểm nổi bật...')
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText('Mô tả sẽ hiển thị cho khách hàng')
                        ->nullable(), // ✅ Thêm nullable nếu không bắt buộc

                    Grid::make(12)
                        ->schema([
                            Radio::make('menu_structure')
                                ->label('Cấu trúc menu')
                                ->options([
                                    'fixed' => 'Menu cố định (danh sách món ăn cố định)',
                                    'options' => 'Menu nhiều lựa chọn (khách hàng chọn 1 trong nhiều menu)',
                                ])
                                ->columnSpan(3)
                                ->descriptions([
                                    'fixed' => 'Khách hàng sẽ nhận được tất cả món ăn trong danh sách',
                                    'options' => 'Khách hàng có thể chọn 1 menu từ nhiều menu khác nhau',
                                ])
                                ->default('fixed')
                                ->required() // ✅ Thêm required cho menu_structure
                                ->live() // ✅ Thay reactive() bằng live()
                                ->afterStateUpdated(function (callable $set, $state) {
                                    // ✅ Reset data khi thay đổi structure
                                    if ($state === 'fixed') {
                                        $set('options', []);
                                    } else {
                                        $set('fixedItems', []);
                                    }
                                }),

                            // ✅ Menu cố định - chỉ hiển thị khi chọn 'fixed'
                            Repeater::make('fixedItems')
                                ->label('Danh sách món ăn')
                                ->relationship()
                                ->columnSpan(9)
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Tên món')
                                        ->placeholder('VD: Phở bò tái')
                                        ->required()
                                        ->maxLength(100)
                                        ->prefixIcon('heroicon-m-cake'),

                                    TextInput::make('quantity')
                                        ->label('Số lượng')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->maxValue(20)
                                        ->nullable(),

                                    TextInput::make('unit')
                                        ->label('Đơn vị')
                                        ->placeholder('VD: KG, dĩa, cái,...')
                                        ->maxLength(50)
                                        ->nullable(),
                                ])
                                ->itemLabel(function (array $state): ?string {
                                    $name = $state['name'] ?? '';
                                    return empty($name) ? 'Món ăn mới' : "🍽️ {$name}";
                                })
                                ->addActionLabel('➕ Thêm món ăn')
                                ->reorderableWithButtons()
                                ->collapsible()
                                ->defaultItems(0) // ✅ Đổi từ 1 thành 0 để tránh lỗi khi switch
                                ->minItems(0) // ✅ Cho phép 0 items khi ẩn
                                ->visible(fn(callable $get) => $get('menu_structure') === 'fixed')
                                ->helperText('💡 Tất cả các món ăn trong danh sách sẽ được cung cấp cho khách hàng'),

                            // ✅ Menu với nhiều lựa chọn - chỉ hiển thị khi chọn 'options'
                            Repeater::make('options')
                                ->label('Danh sách menu lựa chọn')
                                ->columnSpan(9)
                                ->relationship()
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Tên menu lựa chọn')
                                        ->placeholder('VD: Menu Phở Bò, Menu Bún Chả, Menu Cơm Tấm...')
                                        ->required()
                                        ->maxLength(150)
                                        ->prefixIcon('heroicon-m-bookmark'),

                                    Textarea::make('description')
                                        ->label('Mô tả menu')
                                        ->placeholder('Mô tả chi tiết về menu này, nguyên liệu, cách chế biến...')
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->nullable()
                                        ->helperText('Thông tin này sẽ hiển thị cho khách hàng khi họ chọn menu'),

                                    Repeater::make('items')
                                        ->label('Món ăn trong menu')
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Tên món')
                                                ->placeholder('VD: Phở bò tái')
                                                ->required()
                                                ->maxLength(100)
                                                ->prefixIcon('heroicon-m-cake'),

                                            TextInput::make('quantity')
                                                ->label('Số lượng')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->maxValue(20)
                                                ->nullable(),

                                            TextInput::make('unit')
                                                ->label('Đơn vị')
                                                ->placeholder('VD: KG, dĩa, cái,...')
                                                ->maxLength(50)
                                                ->nullable(),
                                        ])
                                        ->itemLabel(function (array $state): ?string {
                                            $name = $state['name'] ?? '';
                                            return empty($name) ? 'Món ăn mới' : "🍽️ {$name}";
                                        })
                                        ->addActionLabel('➕ Thêm món ăn')
                                        ->reorderableWithButtons()
                                        ->collapsible()
                                        ->defaultItems(0)
                                        ->minItems(0)
                                ])
                                ->itemLabel(function (array $state): ?string {
                                    $name = $state['name'] ?? '';
                                    $itemCount = count($state['items'] ?? []);
                                    return empty($name) ? 'Menu mới' : "🍽️ {$name} ({$itemCount} món)";
                                })
                                ->addActionLabel('➕ Thêm menu lựa chọn mới')
                                ->reorderableWithButtons()
                                ->collapsible()
                                ->defaultItems(0) // ✅ Đổi từ 1 thành 0
                                ->minItems(0) // ✅ Cho phép 0 items khi ẩn
                                ->visible(fn(callable $get) => $get('menu_structure') === 'options')
                                ->helperText('💡 Khách hàng sẽ chọn 1 menu từ danh sách các menu có sẵn'),
                        ])
                ])
                ->itemLabel(function (array $state): ?string {
                    $name = $state['name'] ?? '';
                    $type = $state['type'] ?? '';
                    $structure = $state['menu_structure'] ?? 'fixed';

                    if (empty($name)) return 'Menu mới';

                    $typeLabels = [
                        'breakfast' => '🌅 Sáng',
                        'lunch' => '🌞 Trưa',
                        'dinner' => '🌙 Tối',
                        'snack' => '🍿 Vặt',
                    ];

                    $structureLabels = [
                        'fixed' => '📋 Cố định',
                        'options' => '🔀 Nhiều lựa chọn',
                    ];

                    $typeText = $typeLabels[$type] ?? $type;
                    $structureText = $structureLabels[$structure] ?? $structure;

                    return "{$name} - {$typeText} ({$structureText})";
                })
                ->addActionLabel('➕ Thêm menu mới')
                ->reorderableWithButtons()
                ->collapsible()
                ->defaultItems(1)
                ->minItems(1)
                ->helperText('📋 Quản lý thực đơn theo từng menu. Chọn cấu trúc phù hợp: cố định hoặc nhiều lựa chọn.')
                ->columnSpanFull()
        ];
    }
}