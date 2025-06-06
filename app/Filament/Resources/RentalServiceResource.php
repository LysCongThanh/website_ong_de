<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalServiceResource\Pages;
use App\Filament\Resources\RentalServiceResource\RelationManagers;
use App\Models\RentalService;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RentalServiceResource extends Resource
{
    protected static ?string $model = RentalService::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Dịch vụ cho thuê';

    protected static ?string $pluralLabel = 'Danh sách dịch vụ cho thuê';

    protected static ?string $navigationGroup = 'Quản lý dịch vụ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin dịch vụ')
                    ->icon('heroicon-o-briefcase')
                    ->description('Cung cấp thông tin chi tiết về dịch vụ cho thuê')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Fieldset::make('Thông tin cơ bản')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên dịch vụ')
                                            ->placeholder('Ví dụ: Thuê Xe Du Lịch 16 Chỗ')
                                            ->required()
                                            ->maxLength(255)
                                            ->autofocus()
                                            ->prefixIcon('heroicon-o-briefcase')
                                            ->prefixIconColor('primary')
                                            ->helperText('Tên dịch vụ nên ngắn gọn, dễ nhớ và hấp dẫn.'),

                                        Textarea::make('short_description')
                                            ->label('Mô tả ngắn')
                                            ->placeholder('Mô tả dịch vụ trong 1-2 câu')
                                            ->rows(3)
                                            ->maxLength(200)
                                            ->helperText('Mô tả ngắn gọn để thu hút sự chú ý của khách hàng.')
                                            ->columnSpanFull(),

                                        RichEditor::make('long_description')
                                            ->label('Mô tả chi tiết')
                                            ->placeholder('Mô tả chi tiết về dịch vụ, bao gồm quyền lợi và đặc điểm nổi bật')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bullet',
                                                'ordered',
                                                'link',
                                            ])
                                            ->helperText('Sử dụng định dạng để làm nổi bật thông tin quan trọng.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),

                                Fieldset::make('Cấu hình dịch vụ')
                                    ->schema([
                                        Textarea::make('conditions')
                                            ->label('Điều kiện sử dụng')
                                            ->placeholder('Ví dụ: Đặt cọc 30% trước khi sử dụng dịch vụ')
                                            ->rows(4)
                                            ->helperText('Liệt kê các điều kiện và yêu cầu cụ thể.'),

                                        Toggle::make('is_active')
                                            ->label('Trạng thái dịch vụ')
                                            ->helperText('Bật để hiển thị dịch vụ trên hệ thống.')
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline(false),
                                    ])
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên dịch vụ')
                    ->searchable(['name', 'short_description'])
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->wrap(),

                Tables\Columns\TextColumn::make('short_description')
                    ->label('Mô tả ngắn')
                    ->limit(60)
                    ->tooltip(fn ($record): string => $record->short_description ?? '')
                    ->html()
                    ->default('-')
                    ->color('gray')
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\BadgeColumn::make('creator.name')
                    ->label('Người tạo')
                    ->sortable()
                    ->default('-')
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('lastUpdater.name')
                    ->label('Người cập nhật')
                    ->sortable()
                    ->default('-')
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Ngày xóa')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Sửa')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Xóa')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Khôi phục')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Xóa vĩnh viễn')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                    ->button()
                    ->label('Thao tác')
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa nhiều')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi phục nhiều')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn nhiều')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
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
}