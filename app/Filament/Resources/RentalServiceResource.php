<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalServiceResource\Forms\RentalServiceForm;
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
use Filament\Support\Enums\FontWeight;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Dịch Vụ Cho Thuê';

    protected static ?string $pluralLabel = 'Danh Sách Dịch Vụ Cho Thuê';

    protected static ?string $modelLabel = 'Dịch Vụ Cho Thuê';

    protected static ?string $navigationGroup = 'Quản Lý Nội Dung';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return RentalServiceForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('main_image')
                    ->label('Hình ảnh chính')
                    ->collection('main_image')
                    ->width(50)
                    ->defaultImageUrl('/images/image-default.png')
                    ->extraImgAttributes(['loading' => 'lazy', 'style' => 'border-radius: 12px;'])
                    ->height(50),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên dịch vụ')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('short_description')
                    ->label('Mô tả ngắn')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->wrap()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Người tạo')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updater.name')
                    ->label('Người cập nhật')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->size('sm')
                    ->since(),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Ngày xóa')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('danger')
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái hoạt động')
                    ->boolean()
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Không hoạt động')
                    ->native(false),

                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Người tạo')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->label('Ngày tạo')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Từ ngày')
                            ->placeholder('dd/mm/yyyy'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Đến ngày')
                            ->placeholder('dd/mm/yyyy'),
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
                    }),

                Tables\Filters\TrashedFilter::make()
                    ->label('Bản ghi đã xóa'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Xem')
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->label('Sửa')
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->color('danger'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Xóa vĩnh viễn'),
                Tables\Actions\RestoreAction::make()
                    ->label('Khôi phục'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa đã chọn'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi phục đã chọn'),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Kích hoạt đã chọn')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Kích hoạt dịch vụ')
                        ->modalDescription('Bạn có chắc chắn muốn kích hoạt các dịch vụ đã chọn?'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Vô hiệu hóa đã chọn')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Vô hiệu hóa dịch vụ')
                        ->modalDescription('Bạn có chắc chắn muốn vô hiệu hóa các dịch vụ đã chọn?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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