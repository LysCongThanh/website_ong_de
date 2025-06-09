<?php

namespace App\Filament\Resources;

use App\Enums\PolicyTypes\TicketPolicyType;
use App\Filament\Components\FIelds\BasePricingField;
use App\Filament\Components\FIelds\CustomerCapacityPricingField;
use App\Filament\Components\FIelds\CustomerSegmentPricingField;
use App\Filament\Components\FIelds\PolicyField;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Models\PriceType;
use App\Models\CustomerSegment;
use App\Models\TicketCategory;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Section as InfolistsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Support\HtmlString;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Vé tham quan';

    protected static ?string $modelLabel = 'Vé';

    protected static ?string $pluralModelLabel = 'Vé tham quan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Quản lý vé')
                    ->tabs([
                        // Tab 1: Thông tin cơ bản
                        Tabs\Tab::make('Thông tin cơ bản')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Chi tiết vé')
                                    ->description('Thông tin cơ bản về vé tham quan')
                                    ->icon('heroicon-o-ticket')
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Tên vé')
                                                    ->helperText('Tên hiển thị của loại vé sẽ được khách hàng thấy (tối đa 255 ký tự)')
                                                    ->placeholder('VD: Vé tham quan, Vé hồ bơi, Vé...')
                                                    ->prefixIcon('heroicon-o-tag')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->columnSpan(['md' => 2]),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('Trạng thái hoạt động')
                                                    ->onIcon('heroicon-o-check')
                                                    ->offIcon('heroicon-o-x-mark')
                                                    ->helperText('Bật/tắt hiển thị thông tin vé cho khách hàng')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->columnSpan(['md' => 1]),
                                            ])
                                            ->columns(3),

                                        Forms\Components\Select::make('categories')
                                            ->label('Danh mục vé')
                                            ->helperText('Chọn một hoặc nhiều danh mục để phân loại vé. Danh mục giúp tổ chức và tìm kiếm vé hiệu quả.')
                                            ->placeholder('Chọn danh mục cho vé...')
                                            ->prefixIcon('heroicon-o-tag')
                                            ->relationship('categories', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->searchPrompt('Nhập tên danh mục để tìm kiếm...')
                                            ->noSearchResultsMessage('Không tìm thấy danh mục nào phù hợp.')
                                            ->loadingMessage('Đang tải danh mục...')
                                            ->createOptionForm([
                                                Section::make('Tạo danh mục mới')
                                                    ->description('Nhập thông tin để tạo danh mục mới cho vé.')
                                                    ->icon('heroicon-o-plus-circle')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Tên danh mục')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->placeholder('Ví dụ: VIP, Thường...')
                                                            ->autofocus()
                                                            ->live(onBlur: true)
                                                            ->hint('Tên hiển thị của danh mục'),
                                                        Forms\Components\TextInput::make('slug')
                                                            ->label('Slug')
                                                            ->required()
                                                            ->unique(table: 'ticket_categories', column: 'slug')
                                                            ->maxLength(255)
                                                            ->placeholder('ví dụ: vip, thuong...')
                                                            ->hint('Dùng để tạo URL thân thiện')
                                                            ->rules(['regex:/^[a-z0-9-]+$/'])
                                                            ->helperText('Chỉ dùng chữ thường, số và dấu gạch ngang.'),
                                                        Forms\Components\Textarea::make('description')
                                                            ->label('Mô tả danh mục')
                                                            ->rows(4)
                                                            ->placeholder('Mô tả ngắn về danh mục này...')
                                                            ->maxLength(500)
                                                            ->hint('Tối đa 500 ký tự'),
                                                    ])
                                                    ->columns(1), // Sắp xếp 2 cột cho form tạo danh mục
                                            ])
                                            ->getSearchResultsUsing(function (string $search) {
                                                return TicketCategory::where('name', 'like', "%{$search}%")
                                                    ->limit(5)
                                                    ->pluck('name', 'id');
                                            })
                                            ->getOptionLabelUsing(function ($value) {
                                                return TicketCategory::find($value)?->name;
                                            })
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Mô tả chi tiết')
                                            ->hintIcon('heroicon-o-pencil')
                                            ->rows(4)
                                            ->helperText('Mô tả đầy đủ về vé, trải nghiệm và thông tin khách hàng cần biết')
                                            ->placeholder('Nhập mô tả chi tiết về vé, thời gian có hiệu lực, điều kiện sử dụng, lưu ý đặc biệt...')
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('includes')
                                            ->label('Những gì bao gồm')
                                            ->hintIcon('heroicon-o-pencil')
                                            ->helperText('Format text của tất cả quyền lợi (tự động tạo từ danh sách trên hoặc nhập thủ công)')
                                            ->placeholder('* Ví dụ: &#10; • Tham quan và check-in với nhiều tiểu cảnh B&#10;• Miễn phí đỗ xe&#10;• Hướng dẫn viên tiếng Việt&#10;• Tham gia 1 số hoạt động & trò chơi miễn phí&#10; ...')
                                            ->rows(6)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        PolicyField::make(),

                        BasePricingField::make(),

                        CustomerCapacityPricingField::make(),

                        CustomerSegmentPricingField::make()
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên vé')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    }),

                Tables\Columns\BadgeColumn::make('base_price_summary')
                    ->label('Giá cơ bản')
                    ->getStateUsing(function (Ticket $record): string {
                        $basePrices = $record->basePrices()->with('priceType')->where('is_active', true)->get();
                        if ($basePrices->isEmpty()) {
                            return 'Chưa có giá';
                        }

                        $minPrice = $basePrices->min('price');
                        $maxPrice = $basePrices->max('price');

                        if ($minPrice == $maxPrice) {
                            return number_format($minPrice) . 'đ';
                        }

                        return number_format($minPrice) . 'đ - ' . number_format($maxPrice) . 'đ';
                    })
                    ->color(fn (string $state): string => $state === 'Chưa có giá' ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('segments_count')
                    ->label('Đối tượng')
                    ->getStateUsing(fn (Ticket $record): int => $record->segmentPrices()->distinct('customer_segment_id')->count())
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('capacity_tiers_count')
                    ->label('Mức giá nhóm')
                    ->getStateUsing(fn (Ticket $record): int => $record->capacityPrices()->count())
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Người tạo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Tạm dừng')
                    ->native(false),

                Tables\Filters\Filter::make('has_base_price')
                    ->label('Có giá cơ bản')
                    ->query(fn (Builder $query): Builder =>
                    $query->whereHas('basePrices', fn (Builder $q) => $q->where('is_active', true))
                    ),

                Tables\Filters\Filter::make('has_segment_price')
                    ->label('Có giá đối tượng')
                    ->query(fn (Builder $query): Builder =>
                    $query->whereHas('segmentPrices', fn (Builder $q) => $q->where('is_active', true))
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistsSection::make('Thông tin vé')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Tên vé'),
                        TextEntry::make('description')
                            ->label('Mô tả'),
                        TextEntry::make('includes')
                            ->label('Quyền lợi bao gồm')
                            ->badge(),
                        IconEntry::make('is_active')
                            ->label('Trạng thái')
                            ->boolean(),
                    ])
                    ->columns(2),

                InfolistsSection::make('Thông tin giá')
                    ->schema([
                        RepeatableEntry::make('basePrices')
                            ->label('Giá cơ bản')
                            ->schema([
                                TextEntry::make('priceType.name')
                                    ->label('Loại thời điểm'),
                                TextEntry::make('price')
                                    ->label('Giá')
                                    ->money('VND'),
                                IconEntry::make('is_active')
                                    ->label('Trạng thái')
                                    ->boolean(),
                            ])
                            ->columns(2),

                        RepeatableEntry::make('segmentPrices')
                            ->label('Giá theo phân khúc khách hàng')
                            ->schema([
                                TextEntry::make('customerSegment.name')
                                    ->label('Đối tượng khách hàng'),
                                TextEntry::make('priceType.name')
                                    ->label('Loại thời điểm'),
                                TextEntry::make('price')
                                    ->label('Giá')
                                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Miễn phí' : number_format($state) . 'đ'),
                                IconEntry::make('is_active')
                                    ->label('Trạng thái')
                                    ->boolean(),
                            ])
                            ->columns(2),

                        RepeatableEntry::make('capacityPrices')
                            ->label('Giá theo sức chứa')
                            ->schema([
                                TextEntry::make('min_person')
                                    ->label('Số người tối thiểu'),
                                TextEntry::make('max_person')
                                    ->label('Số người tối đa')
                                    ->formatStateUsing(fn ($state) => $state ?? 'Không giới hạn'),
                                TextEntry::make('priceType.name')
                                    ->label('Loại thời điểm'),
                                TextEntry::make('price')
                                    ->label('Giá')
                                    ->money('VND'),
                                IconEntry::make('is_active')
                                    ->label('Trạng thái')
                                    ->boolean(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
//            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
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
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}