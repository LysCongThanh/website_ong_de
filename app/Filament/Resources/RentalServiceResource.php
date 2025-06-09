<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalServiceResource\Pages;
use App\Models\RentalService;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class RentalServiceResource extends Resource
{
    protected static ?string $model = RentalService::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Dịch Vụ Cho Thuê';

    protected static ?string $pluralLabel = 'Danh Sách Dịch Vụ Cho Thuê';

    protected static ?string $modelLabel = 'Dịch Vụ Cho Thuê';

    protected static ?string $navigationGroup = 'Quản Lý Dịch Vụ';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('📋 Thông Tin Dịch Vụ')
                    ->description('Cung cấp thông tin chi tiết về dịch vụ cho thuê của bạn')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                // Phần thông tin cơ bản
                                Fieldset::make('Thông Tin Cơ Bản')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên Dịch Vụ')
                                            ->placeholder('Nhập tên dịch vụ (VD: Thuê Xe Du Lịch 16 Chỗ VIP)')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $context, $state, callable $set) {
                                                if ($context === 'create') {
                                                    $set('slug', Str::slug($state));
                                                }
                                            })
                                            ->prefixIcon('heroicon-o-tag')
                                            ->prefixIconColor('primary')
                                            ->helperText('Tên dịch vụ nên ngắn gọn, rõ ràng và hấp dẫn khách hàng')
                                            ->columnSpan(12),

                                        Textarea::make('short_description')
                                            ->label('Mô Tả Ngắn')
                                            ->placeholder('Viết mô tả ngắn gọn về dịch vụ trong 1-2 câu để thu hút khách hàng...')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $set('short_description_count', strlen($state ?? ''));
                                            })
                                            ->helperText(function ($get) {
                                                $count = strlen($get('short_description') ?? '');
                                                return "Đã nhập: {$count}/500 ký tự. Mô tả ngắn sẽ hiển thị trong danh sách dịch vụ.";
                                            })
                                            ->columnSpan(12),
                                    ])
                                    ->columnSpan(12),
                            ]),
                    ]),

                Section::make('📝 Nội Dung Chi Tiết')
                    ->description('Thông tin chi tiết và điều kiện sử dụng dịch vụ')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                RichEditor::make('long_description')
                                    ->label('Mô Tả Chi Tiết')
                                    ->placeholder('Viết mô tả chi tiết về dịch vụ, bao gồm:
• Đặc điểm nổi bật của dịch vụ
• Quyền lợi khách hàng nhận được
• Hướng dẫn sử dụng
• Thông tin liên hệ và hỗ trợ')
                                    ->toolbarButtons([
                                        'attachFiles',
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('rental-services')
                                    ->helperText('Sử dụng định dạng văn bản để làm nổi bật thông tin quan trọng. Có thể đính kèm hình ảnh minh họa.')
                                    ->columnSpanFull(),

                                Textarea::make('conditions')
                                    ->label('Điều Kiện & Yêu Cầu')
                                    ->placeholder('Liệt kê các điều kiện và yêu cầu cụ thể:
• Điều kiện đặt cọc (VD: 30% giá trị dịch vụ)
• Yêu cầu về giấy tờ tùy thân
• Thời gian hủy dịch vụ
• Chính sách hoàn tiền
• Các lưu ý đặc biệt khác')
                                    ->rows(6)
                                    ->maxLength(2000)
                                    ->helperText('Quy định rõ ràng giúp tránh hiểu lầm và tranh chấp với khách hàng')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('⚙️ Cấu Hình Dịch Vụ')
                    ->description('Thiết lập trạng thái và quyền hạn cho dịch vụ')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Fieldset::make('Trạng Thái Hoạt Động')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Kích Hoạt Dịch Vụ')
                                            ->helperText('Bật để hiển thị dịch vụ trên website và cho phép khách hàng đặt dịch vụ')
                                            ->default(true)
                                            ->onIcon('heroicon-m-eye')
                                            ->offIcon('heroicon-m-eye-slash')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (!$state) {
                                                    $set('status_note', 'Dịch vụ sẽ bị ẩn khỏi danh sách công khai');
                                                } else {
                                                    $set('status_note', 'Dịch vụ sẽ hiển thị công khai cho khách hàng');
                                                }
                                            }),

                                        Forms\Components\Placeholder::make('status_note')
                                            ->label('')
                                            ->content(function (callable $get) {
                                                return $get('is_active')
                                                    ? '✅ Dịch vụ sẽ hiển thị công khai cho khách hàng'
                                                    : '❌ Dịch vụ sẽ bị ẩn khỏi danh sách công khai';
                                            })
                                            ->extraAttributes(['class' => 'text-sm']),
                                    ])
                                    ->columnSpan(1),

                                Fieldset::make('Thông Tin Hệ Thống')
                                    ->schema([
                                        Forms\Components\Placeholder::make('system_info')
                                            ->label('Ghi Chú')
                                            ->content('
• Thông tin người tạo và cập nhật sẽ được lưu tự động
• Dịch vụ có thể được khôi phục sau khi xóa
• Sử dụng tính năng tìm kiếm để quản lý dễ dàng
                                            ')
                                            ->extraAttributes(['class' => 'text-sm text-gray-600']),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên Dịch Vụ')
                    ->description(fn ($record): string => Str::limit($record->short_description, 50))
                    ->searchable(['name', 'short_description'])
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary')
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Đã sao chép tên dịch vụ')
                    ->tooltip('Click để sao chép'),

                TextColumn::make('short_description')
                    ->label('Mô Tả Ngắn')
                    ->limit(80)
                    ->tooltip(fn ($record): string => $record->short_description ?? 'Chưa có mô tả')
                    ->default('Chưa có mô tả')
                    ->color('gray')
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Trạng Thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record): string => $record->is_active ? 'Đang hoạt động' : 'Tạm dừng')
                    ->alignCenter(),

                BadgeColumn::make('creator.name')
                    ->label('Người Tạo')
                    ->sortable()
                    ->default('Hệ thống')
                    ->color('info')
                    ->icon('heroicon-o-user-plus')
                    ->tooltip('Người tạo dịch vụ')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('lastUpdater.name')
                    ->label('Người Cập Nhật ')
                    ->sortable()
                    ->default('Chưa cập nhật')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->tooltip('Người cập nhật gần nhất')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày Tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-calendar-days')
                    ->tooltip('Thời gian tạo dịch vụ')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập Nhật Cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-clock')
                    ->tooltip('Lần cập nhật cuối cùng')
                    ->since()
                    ->toggleable(),

                TextColumn::make('deleted_at')
                    ->label('Ngày Xóa')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->tooltip('Thời gian xóa dịch vụ')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Trạng Thái Xóa')
                    ->placeholder('Tất cả dịch vụ')
                    ->trueLabel('Đã xóa')
                    ->falseLabel('Đang hoạt động')
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Trạng Thái Kích Hoạt')
                    ->placeholder('Tất cả trạng thái')
                    ->trueLabel('Đang kích hoạt')
                    ->falseLabel('Tạm dừng')
                    ->native(false),

                SelectFilter::make('created_by')
                    ->label('Người Tạo')
                    ->relationship('creator', 'name')
                    ->placeholder('Tất cả người tạo')
                    ->native(false)
                    ->multiple()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Ngày Tạo')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Từ ngày')
                            ->placeholder('Chọn ngày bắt đầu'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Đến ngày')
                            ->placeholder('Chọn ngày kết thúc'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Tạo từ: ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Tạo đến: ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Xem Chi Tiết')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading('Chi Tiết Dịch Vụ')
                        ->slideOver()
                        ->modalWidth('7xl'),

                    Tables\Actions\EditAction::make()
                        ->label('Chỉnh Sửa')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->modalHeading('Chỉnh Sửa Dịch Vụ')

                        ->modalWidth('7xl'),

                    Tables\Actions\Action::make('toggle_status')
                        ->label(fn ($record) => $record->is_active ? 'Tạm Dừng' : 'Kích Hoạt')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                        ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                        ->action(function ($record) {
                            $record->update(['is_active' => !$record->is_active]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => $record->is_active ? 'Tạm dừng dịch vụ?' : 'Kích hoạt dịch vụ?')
                        ->modalDescription(fn ($record) => $record->is_active
                            ? 'Dịch vụ sẽ bị ẩn khỏi danh sách công khai.'
                            : 'Dịch vụ sẽ hiển thị công khai cho khách hàng.'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Xóa')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->modalHeading('Xóa Dịch Vụ')
                        ->modalDescription('Bạn có chắc chắn muốn xóa dịch vụ này? Dịch vụ có thể được khôi phục sau khi xóa.'),

                    Tables\Actions\RestoreAction::make()
                        ->label('Khôi Phục')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->modalHeading('Khôi Phục Dịch Vụ')
                        ->modalDescription('Dịch vụ sẽ được khôi phục và hiển thị trở lại trong danh sách.'),

                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Xóa Vĩnh Viễn')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->modalHeading('Xóa Vĩnh Viễn')
                        ->modalDescription('⚠️ CẢNH BÁO: Hành động này không thể hoàn tác! Dịch vụ sẽ bị xóa hoàn toàn khỏi hệ thống.'),
                ])
                    ->button()
                    ->label('Thao Tác')
                    ->color('primary')
                    ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Kích Hoạt Hàng Loạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Kích hoạt các dịch vụ đã chọn?')
                        ->modalDescription('Tất cả dịch vụ được chọn sẽ được kích hoạt và hiển thị công khai.'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Tạm Dừng Hàng Loạt')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Tạm dừng các dịch vụ đã chọn?')
                        ->modalDescription('Tất cả dịch vụ được chọn sẽ bị ẩn khỏi danh sách công khai.'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa Hàng Loạt')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->modalHeading('Xóa các dịch vụ đã chọn?')
                        ->modalDescription('Các dịch vụ sẽ được chuyển vào thùng rác và có thể khôi phục sau.'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi Phục Hàng Loạt')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->modalHeading('Khôi phục các dịch vụ đã chọn?')
                        ->modalDescription('Tất cả dịch vụ được chọn sẽ được khôi phục và hiển thị trở lại.'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa Vĩnh Viễn Hàng Loạt')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->modalHeading('⚠️ Xóa vĩnh viễn các dịch vụ đã chọn?')
                        ->modalDescription('CẢNH BÁO: Hành động này không thể hoàn tác! Tất cả dịch vụ sẽ bị xóa hoàn toàn.'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tạo Dịch Vụ Đầu Tiên')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->emptyStateHeading('Chưa Có Dịch Vụ Nào')
            ->emptyStateDescription('Hãy bắt đầu bằng cách tạo dịch vụ cho thuê đầu tiên của bạn.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->deferLoading()
            ->poll('30s')
            ->paginationPageOptions([10, 25, 50, 100])
            ->recordUrl(null)
            ->recordAction(null);
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

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'primary';
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
            'index' => Pages\ListRentalServices::route('/'),
            'create' => Pages\CreateRentalService::route('/create'),
            'edit' => Pages\EditRentalService::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['creator', 'lastUpdater']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'short_description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Trạng thái' => $record->is_active ? 'Đang hoạt động' : 'Tạm dừng',
            'Người tạo' => $record->creator?->name ?? 'Hệ thống',
            'Ngày tạo' => $record->created_at->format('d/m/Y'),
        ];
    }
}