<?php

namespace App\Filament\Resources;

use App\Filament\Components\FormFields\BasePricingField;
use App\Filament\Components\FormFields\CustomerCapacityPricingField;
use App\Filament\Components\FormFields\CustomerSegmentPricingField;
use App\Filament\Components\FormFields\PolicyField;
use App\Filament\Components\TableFields\IsActiveColumn;
use App\Filament\Components\TableFields\TrackableColumn;
use App\Filament\Resources\ActivityResource\Forms\ActivityForm;
use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Illuminate\Support\Str;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Hoạt động & trãi nghiệm';

    protected static ?string $modelLabel = 'Hoạt Động & trãi nghiệm';

    protected static ?string $pluralModelLabel = 'Hoạt Động & trãi nghiệm';

    protected static ?string $navigationGroup = 'Quản Lý Nội Dung';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return ActivityForm::make($form);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('main_image')
                    ->label('Hình ảnh chính')
                    ->collection('main_image')
                    ->width(100)
                    ->height(100),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên hoạt động')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn(Activity $record): string => $record->short_description ?? 'Không có mô tả'),

                Tables\Columns\TextColumn::make('location_area')
                    ->label('Khu vực')
                    ->searchable()
                    ->color('gray')
                    ->badge()
                    ->sortable()
                    ->placeholder('Chưa xác định')
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('participants_range')
                    ->label('Số người tham gia')
                    ->getStateUsing(function (Activity $record): string {
                        $min = $record->min_participants ?? 0;
                        $max = $record->max_participants ?? '∞';
                        return "{$min} - {$max}";
                    })
                    ->color('gray')
                    ->badge()
                    ->alignCenter()
                    ->icon('heroicon-m-users'),

                IsActiveColumn::make(),

                ...TrackableColumn::make()
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái hoạt động')
                    ->placeholder('Tất cả trạng thái')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã tắt'),

                Tables\Filters\SelectFilter::make('location_area')
                    ->label('Khu vực')
                    ->options(function () {
                        return Activity::whereNotNull('location_area')
                            ->distinct()
                            ->pluck('location_area', 'location_area')
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple(),

                Tables\Filters\Filter::make('has_participants_limit')
                    ->label('Có giới hạn số người')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('max_participants')
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Xem chi tiết')
                        ->slideOver()
                        ->color('success')
                        ->icon('heroicon-o-eye')
                        ->modalIcon('heroicon-o-information-circle')
                        ->modalHeading(fn($record) => "Chi tiết hoạt động: {$record->name}")
                        ->modalWidth(MaxWidth::SevenExtraLarge)
                        ->tooltip('Xem thông tin chi tiết hoạt động'),
                    Tables\Actions\EditAction::make()
                        ->label('Chỉnh sửa')
                        ->color('warning')
                        ->icon('heroicon-o-pencil')
                        ->tooltip('Chỉnh sửa thông tin hoạt động'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Xóa')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->tooltip('Xóa hoạt động (có thể khôi phục)')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận xóa hoạt động')
                        ->modalDescription('Bạn có chắc chắn muốn xóa hoạt động này? Hành động này có thể được khôi phục.')
                        ->modalSubmitActionLabel('Xóa'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Khôi phục')
                        ->color('info')
                        ->icon('heroicon-o-arrow-path')
                        ->tooltip('Khôi phục hoạt động đã xóa')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận khôi phục hoạt động')
                        ->modalDescription('Bạn có muốn khôi phục hoạt động này?')
                        ->modalSubmitActionLabel('Khôi phục'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Xóa vĩnh viễn')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->tooltip('Xóa vĩnh viễn hoạt động (không thể khôi phục)')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận xóa vĩnh viễn')
                        ->modalDescription('Bạn có chắc chắn muốn xóa vĩnh viễn hoạt động này? Hành động này không thể hoàn tác.')
                        ->modalSubmitActionLabel('Xóa vĩnh viễn'),
                ])
                    ->label('Thao tác')
                    ->icon('heroicon-s-ellipsis-vertical')
                    ->button()
                    ->color('gray')
                    ->size(ActionSize::Medium)
                    ->dropdown()
                    ->tooltip('Chọn hành động'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa đã chọn'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi phục đã chọn'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn'),

                    // Custom bulk actions
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Kích hoạt')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Kích hoạt các hoạt động đã chọn')
                        ->modalDescription('Bạn có chắc chắn muốn kích hoạt tất cả các hoạt động đã chọn?'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Tắt kích hoạt')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Tắt kích hoạt các hoạt động đã chọn')
                        ->modalDescription('Bạn có chắc chắn muốn tắt kích hoạt tất cả các hoạt động đã chọn?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s'); // Auto refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            // Có thể thêm RelationManagers nếu cần
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),

            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'warning' : 'primary';
    }
}