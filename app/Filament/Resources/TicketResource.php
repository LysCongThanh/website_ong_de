<?php

namespace App\Filament\Resources;

use App\Filament\Components\TableFields\IsActiveColumn;
use App\Filament\Components\TableFields\TrackableColumn;
use App\Filament\Resources\TicketResource\Forms\TicketForm;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Forms\Form;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Section as InfolistsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Quản lí vé';

    protected static ?string $navigationGroup = 'Quản Lý Nội Dung';
    protected static ?string $modelLabel = 'Vé';

    protected static ?string $pluralModelLabel = 'Vé';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return TicketForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên vé')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->wrap()
                    ->weight(FontWeight::ExtraBold)
                    ->color('gray')
                    ->tooltip(fn($record) => $record->name),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Danh mục vé')
                    ->badge(),

                Tables\Columns\TextColumn::make('base_price_summary')
                    ->label('Giá cơ bản')
                    ->badge()
                    ->alignCenter()
                    ->getStateUsing(function (Ticket $record): string {
                        $basePrices = $record->basePrices()->with('priceType')->where('is_active', true)->get();
                        if ($basePrices->isEmpty()) {
                            return 'Chưa có giá';
                        }

                        return $basePrices->map(function ($basePrice) {
                            $basePriceName = $basePrice->priceType?->name ?? 'N/A';
                            $price = $basePrice->price ? number_format($basePrice->price) . ' VNĐ' : 'Miễn phí';
                            return $basePriceName . ': ' . $price;
                        })->join('123SeparatorKey ');
                    })
                    ->separator('123SeparatorKey')
                    ->color(fn(string $state): string => $state === 'Chưa có giá' ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('customer_segment')
                    ->label('Giá theo phân khúc')
                    ->alignCenter()
                    ->getStateUsing(function (Ticket $record) {
                        $segmentPrices = $record->segmentPrices()
                            ->where('is_active', true)
                            ->with('customerSegment')
                            ->get();

                        if ($segmentPrices->isEmpty()) {
                            return 'Chưa thiết lập';
                        }

                        return $segmentPrices->map(function ($segmentPrice) {
                            $segmentName = $segmentPrice->customerSegment?->name ?? 'N/A';
                            $price = $segmentPrice->price ? number_format($segmentPrice->price) . ' VNĐ' : 'Miễn phí';
                            return $segmentName . ': ' . $price;
                        })->join('abcxyzSeparatorKey ');
                    })
                    ->badge()
                    ->separator('abcxyzSeparatorKey')
                    ->color('info')
                    ->wrap(),

                IsActiveColumn::make(),

                ...TrackableColumn::make()
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái hoạt động')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Không hoạt động'),

                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Người tạo')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Từ ngày'),
                        DatePicker::make('created_until')
                            ->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Từ ngày: ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Đến ngày: ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ], layout: Tables\Enums\FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Xem')
                    ->slideOver(),
                Tables\Actions\EditAction::make()
                    ->label('Sửa'),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->requiresConfirmation(),
                Tables\Actions\RestoreAction::make()
                    ->label('Khôi phục'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Xóa vĩnh viễn')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa đã chọn'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi phục đã chọn'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn'),

                    Tables\Actions\BulkAction::make('toggle_status')
                        ->label('Thay đổi trạng thái')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->deferLoading()
            ->emptyStateHeading('Không có ticket nào')
            ->emptyStateDescription('Bắt đầu tạo ticket đầu tiên của bạn.')
            ->emptyStateIcon('heroicon-o-ticket');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header với thông tin chính
                InfolistsSection::make('Thông tin vé')
                    ->description('Thông tin cơ bản về vé và quyền lợi')
                    ->icon('heroicon-o-ticket')
                    ->schema([
                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Tên vé')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntrySize::Large)
                                    ->color('primary'),
                                IconEntry::make('is_active')
                                    ->label('Trạng thái')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                TextEntry::make('description')
                                    ->label('Mô tả')
                                    ->columnSpanFull()
                                    ->prose(),

                                TextEntry::make('includes')
                                    ->label('Quyền lợi bao gồm')
                                    ->badge()
                                    ->separator(',')
                                    ->color('info')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->collapsible()
                    ->persistCollapsed('ticket-info'),

                // Tab hoặc accordion cho các loại giá
                Tabs::make('Pricing Information')
                    ->tabs([
                        Tab::make('Giá cơ bản')
                            ->icon('heroicon-o-banknotes')
                            ->badge(fn($record) => $record->basePrices->count())
                            ->schema([
                                RepeatableEntry::make('basePrices')
                                    ->label(false)
                                    ->schema([
                                        Fieldset::make('')
                                            ->label(function ($record) {
                                                return 'Loại giá: '. $record->priceType->name;
                                            })
                                            ->schema([
                                                IconEntry::make('is_active')
                                                    ->label('Trạng thái')
                                                    ->boolean()
                                                    ->trueIcon('heroicon-o-check-circle')
                                                    ->falseIcon('heroicon-o-x-circle')
                                                    ->trueColor('success')
                                                    ->falseColor('danger'),
                                                TextEntry::make('price')
                                                    ->label('Giá')
                                                    ->money('VND')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextEntrySize::Large)
                                                    ->color('success'),
                                            ])
                                            ->columns(2),
                                    ])
                                    ->contained(false)
                                    ->grid(1),
                            ]),

                        Tab::make('Giá phân khúc')
                            ->icon('heroicon-o-users')
                            ->badge(fn($record) => $record->segmentPrices->count())
                            ->schema([
                                RepeatableEntry::make('segmentPrices')
                                    ->label(false)
                                    ->schema([
                                        Fieldset::make('')
                                            ->label(function ($record) {
                                                return 'Loại giá: '. $record->priceType->name;
                                            })
                                            ->schema([
                                                TextEntry::make('customerSegment.name')
                                                    ->label('Đối tượng')
                                                    ->badge()
                                                    ->color('primary'),
                                                IconEntry::make('is_active')
                                                    ->label('Trạng thái')
                                                    ->boolean()
                                                    ->trueIcon('heroicon-o-check-circle')
                                                    ->falseIcon('heroicon-o-x-circle')
                                                    ->trueColor('success')
                                                    ->falseColor('danger'),
                                                TextEntry::make('price')
                                                    ->label('Giá')
                                                    ->formatStateUsing(function($state) {
                                                        if ($state == 0) {
                                                            return 'Miễn phí';
                                                        }
                                                        return number_format($state) . 'đ';
                                                    })
                                                    ->badge()
                                                    ->color(fn($state) => $state == 0 ? 'success' : 'info')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextEntrySize::Large),
                                            ])
                                            ->columns(3),
                                    ])
                                    ->contained(false)
                                    ->grid(1),
                            ]),

                        Tab::make('Giá theo sức chứa')
                            ->icon('heroicon-o-user-group')
                            ->badge(fn($record) => $record->capacityPrices->count())
                            ->schema([
                                RepeatableEntry::make('capacityPrices')
                                    ->label(false)
                                    ->schema([
                                        Fieldset::make('')
                                            ->label(function ($record) {
                                                return 'Loại giá: '. $record->priceType->name . ' - Phân khúc: ' . $record->customerSegment->name;
                                            })
                                            ->schema([
                                                TextEntry::make('min_person')
                                                    ->label('Tối thiểu')
                                                    ->numeric()
                                                    ->badge()
                                                    ->color('gray'),
                                                TextEntry::make('max_person')
                                                    ->label('Tối đa')
                                                    ->formatStateUsing(fn($state) => $state ?? '∞')
                                                    ->badge()
                                                    ->color('gray'),
                                                IconEntry::make('is_active')
                                                    ->label('Trạng thái')
                                                    ->boolean()
                                                    ->trueIcon('heroicon-o-check-circle')
                                                    ->falseIcon('heroicon-o-x-circle')
                                                    ->trueColor('success')
                                                    ->falseColor('danger'),
                                                TextEntry::make('price')
                                                    ->label('Giá')
                                                    ->money('VND')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextEntrySize::Large)
                                                    ->color('success'),
                                            ])
                                            ->columns(4),
                                    ])
                                    ->contained(false)
                                    ->grid(1),
                            ]),
                    ])
                    ->contained()
                    ->persistTabInQueryString(),
            ])
            ->columns(1);
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