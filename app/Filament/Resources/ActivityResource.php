<?php

namespace App\Filament\Resources;

use App\Filament\Components\FormFields\BasePricingField;
use App\Filament\Components\FormFields\CustomerCapacityPricingField;
use App\Filament\Components\FormFields\CustomerSegmentPricingField;
use App\Filament\Components\FormFields\PolicyField;
use App\Filament\Components\TableFields\IsActiveColumn;
use App\Filament\Components\TableFields\TrackableColumn;
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

    protected static ?string $navigationLabel = 'Quản lí hoạt Động';

    protected static ?string $modelLabel = 'Hoạt Động';

    protected static ?string $pluralModelLabel = 'Hoạt Động';

    protected static ?string $navigationGroup = 'Quản Lý Nội Dung';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Thông tin hoạt động')
                    ->tabs([
                        Tabs\Tab::make('Thông tin cơ bản')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Section::make('Thông tin chính')
                                    ->description('Nhập các thông tin cơ bản về hoạt động')
                                    ->icon('heroicon-m-document-text')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Tên hoạt động')
                                                    ->placeholder('Nhập tên hoạt động...')
                                                    ->prefixIcon('heroicon-m-tag')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->helperText('Tên này sẽ được hiển thị công khai cho người dùng')
                                                    ->columnSpan(['md' => 2]),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('Kích hoạt hoạt động')
                                                    ->helperText('Chỉ những hoạt động được kích hoạt mới hiển thị công khai')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->columnSpan(['md' => 1]),
                                            ])->columns(3),
                                        Forms\Components\Textarea::make('short_description')
                                            ->label('Mô tả ngắn')
                                            ->placeholder('Nhập mô tả ngắn gọn về hoạt động...')
                                            ->rows(4)
                                            ->hintIcon('heroicon-o-pencil')
                                            ->maxLength(500)
                                            ->helperText('Mô tả ngắn gọn sử dụng trong danh sách và tóm tắt (tối đa 500 ký tự)'),
                                    ]),

                                Section::make('Mô tả chi tiết')
                                    ->description('Thông tin chi tiết về hoạt động')
                                    ->icon('heroicon-m-document-text')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\RichEditor::make('long_description')
                                            ->label('Mô tả chi tiết')
                                            ->placeholder('Nhập mô tả chi tiết về hoạt động...')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'undo',
                                                'redo',
                                            ])
                                            ->helperText('Mô tả đầy đủ về hoạt động, có thể sử dụng định dạng văn bản'),

                                        Forms\Components\RichEditor::make('conditions')
                                            ->label('Điều kiện tham gia')
                                            ->placeholder('Nhập các điều kiện, yêu cầu để tham gia...')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                                'orderedList',
                                            ])
                                            ->helperText('Các điều kiện, yêu cầu cần thiết để tham gia hoạt động'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Địa điểm & Số lượng')
                            ->icon('heroicon-m-map-pin')
                            ->schema([
                                Section::make('Thông tin địa điểm')
                                    ->description('Vị trí tổ chức hoạt động')
                                    ->icon('heroicon-m-map-pin')
                                    ->schema([
                                        Forms\Components\TextInput::make('location_area')
                                            ->label('Khu vực tổ chức')
                                            ->placeholder('Ví dụ: Sân cỏ, mương...')
                                            ->prefixIcon('heroicon-m-map-pin')
                                            ->maxLength(255)
                                            ->helperText('Khu vực hoặc địa điểm tổ chức hoạt động'),
                                    ]),

                                Section::make('Số lượng tham gia')
                                    ->description('Giới hạn số người tham gia')
                                    ->icon('heroicon-m-users')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('min_participants')
                                                    ->label('Số người tối thiểu')
                                                    ->placeholder('0')
                                                    ->prefixIcon('heroicon-m-user-minus')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->helperText('Số người tham gia tối thiểu để tổ chức'),

                                                Forms\Components\TextInput::make('max_participants')
                                                    ->label('Số người tối đa')
                                                    ->placeholder('100')
                                                    ->prefixIcon('heroicon-m-user-plus')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->helperText('Số người tham gia tối đa có thể chấp nhận'),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Media & Hình ảnh')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\Section::make('Hình ảnh chính')
                                    ->description('Hình ảnh đại diện chính cho hoạt động')
                                    ->icon('heroicon-m-photo')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\SpatieMediaLibraryFileUpload::make('main_image')
                                            ->label('Hình ảnh chính')
                                            ->collection('main_image')
                                            ->image()
                                            ->maxFiles(1)
                                            ->downloadable()
                                            ->previewable(true)
                                            ->helperText('Chọn hình ảnh đại diện chính cho hoạt động (tối đa 1 ảnh)'),
                                    ]),

                                Forms\Components\Section::make('Thư viện ảnh')
                                    ->description('Các hình ảnh bổ sung cho hoạt động')
                                    ->icon('heroicon-m-photo')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                                            ->label('Thư viện ảnh')
                                            ->collection('gallery')
                                            ->image()
                                            ->multiple()
                                            ->reorderable()
                                            ->downloadable()
                                            ->previewable(true)
                                            ->helperText('Tải lên các hình ảnh bổ sung cho hoạt động (có thể chọn nhiều ảnh)'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Chính sách')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                PolicyField::make()
                            ]),

                        Tabs\Tab::make('Thiết lập giá')
                            ->icon('heroicon-o-currency-dollar')
                            ->badge(fn($record) => $record?->basePrices?->count() ?? 0)
                            ->badgeColor('success')
                            ->schema([
                                BasePricingField::make(),
                            ]),

                        Tabs\Tab::make('Giá nhóm')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                CustomerCapacityPricingField::make()
                            ]),

                        Tabs\Tab::make('Giá đối tượng')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                CustomerSegmentPricingField::make()
                            ])
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
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