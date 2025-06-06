<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers\CategoriesRelationManager;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Guava\FilamentIconPicker\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Vé';

    protected static ?string $pluralLabel = 'Danh sách vé';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin vé')
                    ->icon('heroicon-o-information-circle')
                    ->description('Nhập thông tin cơ bản của vé')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên vé')
                            ->placeholder('Nhập tên vé')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Tên vé nên ngắn gọn và mô tả rõ ràng nội dung vé.'),

                        Forms\Components\Textarea::make('description')
                            ->label('Mô tả')
                            ->placeholder('Nhập mô tả chi tiết về vé')
                            ->rows(4)
                            ->helperText('Mô tả chi tiết về vé, ví dụ: quyền lợi khi sở hữu vé.')
                            ->nullable(),

                        Forms\Components\Repeater::make('includes')
                            ->label('Các dịch vụ bao gồm')
                            ->helperText('Liệt kê các quyền lợi hoặc dịch vụ đi kèm với vé.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên mục')
                                    ->placeholder('Nhập tên mục')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Tên của quyền lợi hoặc dịch vụ.'),

                                IconPicker::make('icon')
                                    ->layout(Layout::FLOATING)
                                    ->label('Biểu tượng')
                                    ->columns([
                                        'default' => 1,
                                        'lg' => 3,
                                        '2xl' => 5,
                                    ])
                                    ->helperText('Chọn biểu tượng từ danh sách hoặc nhập tên biểu tượng (ví dụ: heroicon-o-check).')
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->nullable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Trạng thái hoạt động')
                            ->helperText('Bật để kích hoạt vé, tắt để vô hiệu hóa.')
                            ->required()
                            ->default(true),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Danh mục vé')
                    ->icon('heroicon-o-tag')
                    ->description('Gán vé vào các danh mục phù hợp')
                    ->schema([
                        Forms\Components\Select::make('ticket_category_id')
                            ->label('Danh mục')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\Section::make('Thông tin danh mục')
                                    ->icon('heroicon-o-tag')
                                    ->description('Nhập thông tin để tạo danh mục mới')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Tên danh mục')
                                                    ->placeholder('Nhập tên danh mục')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function (callable $set, $state) {
                                                        $set('slug', Str::slug($state));
                                                    })
                                                    ->helperText('Tên danh mục nên ngắn gọn, ví dụ: "VIP", "Thường".'),

                                                Forms\Components\TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->placeholder('Nhập slug danh mục')
                                                    ->unique(table: \App\Models\TicketCategory::class)
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->helperText('Slug được tự động tạo từ tên danh mục nhưng bạn có thể chỉnh sửa, ví dụ: "vip", "thuong".'),
                                            ]),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Mô tả danh mục')
                                            ->placeholder('Nhập mô tả chi tiết về danh mục')
                                            ->rows(4)
                                            ->maxLength(65535)
                                            ->helperText('Mô tả chi tiết về danh mục, ví dụ: mục đích hoặc đặc điểm của danh mục.')
                                            ->nullable(),
                                    ])
                                    ->collapsible(),
                            ])
                            ->createOptionModalHeading('Tạo danh mục mới')
                            ->helperText('Chọn danh mục hiện có hoặc tạo mới để gán cho vé.')
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên vé')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Người tạo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('lastUpdater.name')
                    ->label('Người cập nhật')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Ngày xóa')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Sửa'),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa'),
                Tables\Actions\RestoreAction::make()
                    ->label('Khôi phục')
                    ->icon('heroicon-o-arrow-uturn-left'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Xóa vĩnh viễn')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa nhiều'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi phục nhiều')
                        ->icon('heroicon-o-arrow-uturn-left'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn nhiều')
                        ->icon('heroicon-o-trash'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getRelations(): array
    {
        return [
            // CategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}