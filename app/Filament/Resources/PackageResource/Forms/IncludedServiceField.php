<?php

namespace App\Filament\Resources\PackageResource\Forms;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Guava\FilamentIconPicker\Forms\IconPicker;

class IncludedServiceField
{
    public static function make(): array
    {
        return [
            Repeater::make('services')
                ->label('Dịch vụ')
                ->relationship()
                ->schema([
                    Grid::make(1)
                        ->schema([
                            TextInput::make('name')
                                ->label('Tên dịch vụ')
                                ->placeholder('VD: Vé tham quan, đồ bà ba...')
                                ->required()
                                ->maxLength(100)
                                ->live(onBlur: true),

                            IconPicker::make('icon')
                                ->label('Icon')
                                ->placeholder('Chọn icon phù hợp')
                                ->preload(),

                            Textarea::make('description')
                                ->label('Mô tả chi tiết')
                                ->placeholder('Mô tả chi tiết về dịch vụ, bao gồm những gì khách hàng sẽ nhận được...')
                                ->maxLength(1000),
                        ])
                ])
                ->itemLabel(function (array $state): ?string {
                    $name = $state['name'] ?? 'Dịch vụ mới';
                    $status = [];

                    $statusText = !empty($status) ? ' (' . implode(', ', $status) . ')' : '';

                    return $name . $statusText;
                })
                ->addActionLabel('+ Thêm dịch vụ mới')
                ->reorderableWithButtons()
                ->cloneable()
                ->collapsed()
                ->collapsible()
                ->deleteAction(
                    fn (Action $action) => $action
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận xóa dịch vụ')
                        ->modalDescription('Bạn có chắc chắn muốn xóa dịch vụ này không? Hành động này không thể hoàn tác.')
                        ->modalSubmitActionLabel('Xóa')
                        ->color('danger')
                )
                ->reorderAction(
                    fn (Action $action) => $action
                        ->label('Sắp xếp lại')
                        ->icon('heroicon-o-arrows-up-down')
                )
                ->cloneAction(
                    fn (Action $action) => $action
                        ->label('Sao chép')
                        ->icon('heroicon-o-document-duplicate')
                )
                ->grid(3)
                ->minItems(1)
                ->maxItems(20)
                ->defaultItems(1)
                ->helperText('Quản lý danh sách các dịch vụ trong gói của bạn. Có thể kéo thả để sắp xếp lại thứ tự.'),
        ];
    }
}