<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Hoạt Động';

    protected static ?string $modelLabel = 'Hoạt Động';

    protected static ?string $pluralModelLabel = 'Hoạt Động';

    protected static ?string $navigationGroup = 'Quản Lý Sự Kiện';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Header Section với thông tin tổng quan
                Section::make('Thông Tin Tổng Quan')
                    ->description('Cung cấp thông tin cơ bản và quan trọng nhất về hoạt động')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->persistCollapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Tên Hoạt Động')
                                    ->placeholder('Nhập tên hoạt động (VD: Hội thảo Marketing Digital 2025)')
                                    ->required()
                                    ->maxLength(255)
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->live(onBlur: true)
                                    ->prefixIcon('heroicon-o-tag')
                                    ->prefixIconColor('primary')
                                    ->helperText('Tên hoạt động nên ngắn gọn, hấp dẫn và dễ nhớ')
                                    ->columnSpan(2),

                                TextInput::make('slug')
                                    ->label('Đường Dẫn Thân Thiện (Slug)')
                                    ->placeholder('duong-dan-than-thien-cua-hoat-dong')
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-link')
                                    ->prefixIconColor('success')
                                    ->helperText('URL thân thiện được tạo tự động từ tên hoạt động')
                                    ->columnSpan(1),

                                TextInput::make('location_area')
                                    ->label('📍 Khu Vực Tổ Chức')
                                    ->placeholder('VD: Hà Nội, TP. Hồ Chí Minh, Đà Nẵng')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->prefixIconColor('amber')
                                    ->helperText('Địa điểm hoặc khu vực dự kiến tổ chức hoạt động')
                                    ->columnSpan(1),
                            ]),
                    ]),

                // Mô tả chi tiết
                Section::make('Nội Dung & Mô Tả')
                    ->description('Mô tả chi tiết về hoạt động để thu hút và thông tin cho người tham gia')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->persistCollapsed()
                    ->schema([
                        RichEditor::make('short_description')
                            ->label('Mô Tả Ngắn Gọn')
                            ->placeholder('Viết mô tả ngắn gọn về hoạt động trong 1-2 câu để thu hút sự chú ý...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                            ])
                            ->maxLength(500)
                            ->helperText('Mô tả ngắn gọn, hấp dẫn để hiển thị trong danh sách và preview')
                            ->columnSpanFull(),

                        RichEditor::make('long_description')
                            ->label('Mô Tả Chi Tiết')
                            ->placeholder('Mô tả chi tiết về nội dung, mục tiêu, lợi ích của hoạt động...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                                'h2',
                                'h3',
                            ])
                            ->helperText('Sử dụng định dạng rich text để làm nổi bật thông tin quan trọng')
                            ->columnSpanFull(),
                    ]),

                // Yêu cầu và quy định
                Section::make('Yêu Cầu & Quy Định Tham Gia')
                    ->description('Thiết lập các điều kiện và giới hạn cho người tham gia')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->persistCollapsed()
                    ->schema([
                        Fieldset::make('Điều Kiện Tham Gia')
                            ->schema([
                                Textarea::make('conditions')
                                    ->label('✅ Yêu Cầu Cụ Thể')
                                    ->placeholder('VD: Độ tuổi từ 18-35, có kinh nghiệm làm việc tối thiểu 2 năm, sinh viên năm cuối...')
                                    ->rows(4)
                                    ->helperText('Liệt kê rõ ràng các yêu cầu, điều kiện để tham gia hoạt động')
                                    ->columnSpanFull(),
                            ]),

                        Fieldset::make('👥 Giới Hạn Số Lượng Tham Gia')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('min_participants')
                                            ->label('Số Người Tối Thiểu')
                                            ->placeholder('VD: 10')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(9999)
                                            ->prefixIcon('heroicon-o-users')
                                            ->prefixIconColor('green')
                                            ->helperText('Số lượng tối thiểu để tổ chức thành công')
                                            ->suffix('người'),

                                        TextInput::make('max_participants')
                                            ->label('Số Người Tối Đa')
                                            ->placeholder('VD: 50')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(9999)
                                            ->prefixIcon('heroicon-o-users')
                                            ->prefixIconColor('red')
                                            ->helperText('Số lượng tối đa được phép tham gia')
                                            ->suffix('người'),
                                    ]),
                            ]),
                    ]),

                // Quản lý và trạng thái
                Section::make('Quản Lý & Trạng Thái')
                    ->description('Cài đặt trạng thái và thông tin quản lý hoạt động')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->collapsible()
                    ->persistCollapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Kích Hoạt Hoạt Động')
                                    ->helperText('Bật để hiển thị hoạt động công khai trên hệ thống')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-o-check-circle')
                                    ->offIcon('heroicon-o-x-circle')
                                    ->inline(false)
                                    ->columnSpan(1),


                            ]),
                    ]),

                // Thông tin hệ thống (chỉ hiển thị khi edit)
                Section::make('📊 Thông Tin Hệ Thống')
                    ->description('Thông tin tự động được hệ thống ghi nhận')
                    ->icon('heroicon-o-computer-desktop')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('📅 Ngày Tạo')
                                    ->content(fn ($record): string => $record?->created_at ? $record->created_at->format('d/m/Y H:i:s') : 'Chưa có')
                                    ->columnSpan(1),

                                Placeholder::make('updated_at')
                                    ->label('🔄 Ngày Cập Nhật Cuối')
                                    ->content(fn ($record): string => $record?->updated_at ? $record->updated_at->format('d/m/Y H:i:s') : 'Chưa có')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->hidden(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('🏷️ Tên Hoạt Động')
                    ->searchable(['name', 'short_description'])
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->wrap()
                    ->description(fn ($record) => Str::limit(strip_tags($record->short_description), 60))
                    ->tooltip(fn ($record): string => $record->name),

                Tables\Columns\TextColumn::make('slug')
                    ->label('🔗 Slug')
                    ->searchable()
                    ->sortable()
                    ->color('gray')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('Đã sao chép slug!')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('location_area')
                    ->label('📍 Khu Vực')
                    ->sortable()
                    ->color('amber')
                    ->icon('heroicon-o-map-pin')
                    ->default('Chưa xác định')
                    ->badge(),

                Tables\Columns\TextColumn::make('min_participants')
                    ->label('👥 Tối Thiểu')
                    ->sortable()
                    ->color('green')
                    ->badge()
                    ->default('Không giới hạn')
                    ->alignCenter()
                    ->suffix(' người'),

                Tables\Columns\TextColumn::make('max_participants')
                    ->label('👥 Tối Đa')
                    ->sortable()
                    ->color('red')
                    ->badge()
                    ->default('Không giới hạn')
                    ->alignCenter()
                    ->suffix(' người'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('🟢 Trạng Thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->tooltip(fn ($record): string => $record->is_active ? 'Đang hoạt động' : 'Ngừng hoạt động'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('👤 Người Tạo')
                    ->sortable()
                    ->color('blue')
                    ->icon('heroicon-o-user-circle')
                    ->default('Không xác định')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updater.name')
                    ->label('✏️ Người Cập Nhật')
                    ->sortable()
                    ->color('amber')
                    ->icon('heroicon-o-pencil-square')
                    ->default('Không xác định')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('📅 Ngày Tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-calendar')
                    ->tooltip(fn ($record): string => $record->created_at->format('l, d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('🔄 Ngày Cập Nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-clock')
                    ->tooltip(fn ($record): string => $record->updated_at->format('l, d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('🗑️ Trạng Thái Thùng Rác')
                    ->placeholder('Tất cả bản ghi')
                    ->trueLabel('Chỉ bản ghi đã xóa')
                    ->falseLabel('Chỉ bản ghi chưa xóa'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('🟢 Trạng Thái Hoạt Động')
                    ->placeholder('Tất cả trạng thái')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Ngừng hoạt động'),

                SelectFilter::make('location_area')
                    ->label('📍 Lọc Theo Khu Vực')
                    ->placeholder('Tất cả khu vực')
                    ->options(
                        Activity::query()
                            ->whereNotNull('location_area')
                            ->distinct()
                            ->pluck('location_area', 'location_area')
                            ->toArray()
                    ),

                Filter::make('participants_range')
                    ->label('👥 Lọc Theo Số Lượng Tham Gia')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_participants_from')
                                    ->label('Từ số người tối thiểu')
                                    ->numeric()
                                    ->placeholder('0'),
                                TextInput::make('max_participants_to')
                                    ->label('Đến số người tối đa')
                                    ->numeric()
                                    ->placeholder('1000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_participants_from'],
                                fn (Builder $query, $value): Builder => $query->where('min_participants', '>=', $value),
                            )
                            ->when(
                                $data['max_participants_to'],
                                fn (Builder $query, $value): Builder => $query->where('max_participants', '<=', $value),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Xem Chi Tiết')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->label('Chỉnh Sửa')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Xóa')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Khôi Phục')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Xóa Vĩnh Viễn')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger'),
                ])
                    ->button()
                    ->label('Thao Tác')
                    ->color('primary')
                    ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('🗑️ Xóa Nhiều')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('♻️ Khôi Phục Nhiều')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('💀 Xóa Vĩnh Viễn Nhiều')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->deferLoading()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
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