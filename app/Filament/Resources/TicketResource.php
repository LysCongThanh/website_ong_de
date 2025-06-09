<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action; // Import Action class
use Guava\FilamentIconPicker\Forms\IconPicker;
use Guava\FilamentIconPicker\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // Import the correct Model class
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Vé';

    protected static ?string $pluralLabel = 'Danh sách vé';

    protected static ?string $navigationGroup = 'Quản lý vé';

    protected static ?int $navigationSort = 1;

    /**
     * Get the form configuration for the resource
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            self::getTicketInfoSection(),
            self::getCategorySection(),
        ]);
    }

    /**
     * Get ticket information section
     */
    private static function getTicketInfoSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Thông tin vé')
            ->icon('heroicon-o-information-circle')
            ->description('Nhập thông tin cơ bản của vé')
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Tên vé')
                        ->placeholder('Nhập tên vé')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Tên vé nên ngắn gọn và mô tả rõ ràng nội dung vé.')
                        ->columnSpan(1),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Trạng thái hoạt động')
                        ->helperText('Bật để kích hoạt vé')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(1),
                ]),

                Forms\Components\Textarea::make('description')
                    ->label('Mô tả')
                    ->placeholder('Nhập mô tả chi tiết về vé')
                    ->rows(4)
                    ->maxLength(1000)
                    ->helperText('Mô tả chi tiết về vé, quyền lợi khi sở hữu vé (tối đa 1000 ký tự).')
                    ->nullable(),

                self::getIncludesRepeater(),
            ])
            ->collapsible()
            ->persistCollapsed();
    }

    /**
     * Get includes repeater field
     */
    private static function getIncludesRepeater(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('includes')
            ->label('Các dịch vụ bao gồm')
            ->helperText('Liệt kê các quyền lợi hoặc dịch vụ đi kèm với vé.')
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Tên mục')
                        ->placeholder('Nhập tên mục')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    IconPicker::make('icon')
                        ->layout(Layout::FLOATING)
                        ->label('Biểu tượng')
                        ->helperText('Chọn biểu tượng phù hợp')
                        ->nullable()
                        ->columns([
                            'default' => 1,
                            'lg' => 3,
                            '2xl' => 5,
                        ])
                ]),
            ])
            ->addActionLabel('Thêm dịch vụ')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Dịch vụ mới')
            ->nullable()
            ->maxItems(10);
    }

    /**
     * Get category section
     */
    private static function getCategorySection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Danh mục vé')
            ->icon('heroicon-o-tag')
            ->description('Gán vé vào các danh mục phù hợp')
            ->schema([
                Forms\Components\Select::make('categories')
                    ->label('Danh mục')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm(self::getCategoryCreateForm())
                    ->createOptionModalHeading('Tạo danh mục mới')
                    ->createOptionUsing(function (array $data): int {
                        return TicketCategory::create($data)->id;
                    })
                    ->helperText('Chọn danh mục hiện có hoặc tạo mới để gán cho vé.')
                    ->nullable(),
            ])
            ->collapsible()
            ->persistCollapsed();
    }

    /**
     * Get category create form
     */
    private static function getCategoryCreateForm(): array
    {
        return [
            Forms\Components\Section::make('Thông tin danh mục')
                ->icon('heroicon-o-tag')
                ->description('Nhập thông tin để tạo danh mục mới')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên danh mục')
                            ->placeholder('Nhập tên danh mục')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set, $state) =>
                            $set('slug', Str::slug($state))
                            )
                            ->helperText('Tên danh mục nên ngắn gọn, ví dụ: "VIP", "Thường".'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('slug-danh-muc')
                            ->required()
                            ->maxLength(255)
                            ->unique(TicketCategory::class, 'slug', ignoreRecord: true)
                            ->helperText('Slug được tự động tạo từ tên danh mục.'),
                    ]),

                    Forms\Components\Textarea::make('description')
                        ->label('Mô tả danh mục')
                        ->placeholder('Nhập mô tả chi tiết về danh mục')
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText('Mô tả chi tiết về danh mục (tối đa 500 ký tự).')
                        ->nullable(),
                ]),
        ];
    }

    /**
     * Get the table configuration for the resource
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->filters(self::getTableFilters())
            ->actions(self::getTableActions())
            ->bulkActions(self::getBulkActions())
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('30s');
    }

    /**
     * Get table columns
     */
    private static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Tên vé')
                ->searchable()
                ->sortable()
                ->limit(50)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();
                    return strlen($state) > 50 ? $state : null;
                }),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Trạng thái')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger')
                ->sortable(),

            Tables\Columns\TextColumn::make('categories.name')
                ->label('Danh mục')
                ->badge()
                ->separator(', ')
                ->limit(30)
                ->searchable(),

            Tables\Columns\TextColumn::make('includes')
                ->label('Số dịch vụ')
                ->state(fn (Ticket $record) => is_array($record->includes) ? count($record->includes) : 0)
                ->suffix(' dịch vụ')
                ->sortable(false),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->since(),

            Tables\Columns\TextColumn::make('creator.name')
                ->label('Người tạo')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Cập nhật lần cuối')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->since()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('deleted_at')
                ->label('Ngày xóa')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Get table filters
     */
    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\TrashedFilter::make()
                ->label('Trạng thái thùng rác')
                ->placeholder('Tất cả')
                ->trueLabel('Đã xóa')
                ->falseLabel('Chưa xóa'),

            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Trạng thái hoạt động')
                ->placeholder('Tất cả')
                ->trueLabel('Đang hoạt động')
                ->falseLabel('Không hoạt động'),

            Tables\Filters\SelectFilter::make('categories')
                ->label('Danh mục')
                ->relationship('categories', 'name')
                ->multiple()
                ->preload(),
        ];
    }

    /**
     * Get table actions
     */
    private static function getTableActions(): array
    {
        return [
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->label('Xem chi tiết'),

                Tables\Actions\EditAction::make()
                    ->label('Chỉnh sửa'),

                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận xóa vé')
                    ->modalDescription('Bạn có chắc chắn muốn xóa vé này? Hành động này có thể được hoàn tác.'),

                Tables\Actions\RestoreAction::make()
                    ->label('Khôi phục')
                    ->icon('heroicon-o-arrow-uturn-left'),

                Tables\Actions\ForceDeleteAction::make()
                    ->label('Xóa vĩnh viễn')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận xóa vĩnh viễn')
                    ->modalDescription('Bạn có chắc chắn muốn xóa vĩnh viễn vé này? Hành động này không thể hoàn tác.'),
            ])
                ->button()
                ->label('Thao Tác')
                ->color('primary')
                ->size('sm'),
        ];
    }

    /**
     * Get bulk actions
     */
    private static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Xóa đã chọn')
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận xóa các vé đã chọn')
                    ->modalDescription('Bạn có chắc chắn muốn xóa các vé đã chọn?'),

                Tables\Actions\RestoreBulkAction::make()
                    ->label('Khôi phục đã chọn')
                    ->icon('heroicon-o-arrow-uturn-left'),

                Tables\Actions\ForceDeleteBulkAction::make()
                    ->label('Xóa vĩnh viễn đã chọn')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận xóa vĩnh viễn')
                    ->modalDescription('Bạn có chắc chắn muốn xóa vĩnh viễn các vé đã chọn? Hành động này không thể hoàn tác.'),
            ]),
        ];
    }

    /**
     * Configure the Eloquent query for the resource
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['categories', 'creator']);
    }

    /**
     * Get the relations available on the resource
     */
    public static function getRelations(): array
    {
        return [
            // Add relation managers here if needed
        ];
    }

    /**
     * Get the pages available for the resource
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),

            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    /**
     * Get global search attributes
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['categories']);
    }

    /**
     * Get global search attributes
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'categories.name'];
    }

    /**
     * Get global search result details
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Danh mục' => $record->categories->pluck('name')->join(', '),
            'Trạng thái' => $record->is_active ? 'Hoạt động' : 'Không hoạt động',
        ];
    }

    /**
     * Get global search result actions
     */
    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->label('Chỉnh sửa')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }
}