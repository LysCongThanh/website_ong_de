<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Hoạt động';

    protected static ?string $pluralLabel = 'Danh sách hoạt động';

    protected static ?string $navigationGroup = 'Quản lý sự kiện';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin hoạt động')
                    ->icon('heroicon-o-calendar')
                    ->description('Cung cấp thông tin chi tiết về hoạt động')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Fieldset::make('Thông tin cơ bản')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên hoạt động')
                                            ->placeholder('Ví dụ: Team Building 2025')
                                            ->required()
                                            ->maxLength(255)
                                            ->afterStateUpdated(function (callable $set, $state) {
                                                $set('slug', Str::slug($state));
                                            })
                                            ->live(onBlur: true)
                                            ->prefixIcon('heroicon-o-pencil')
                                            ->prefixIconColor('primary')
                                            ->helperText('Tên hoạt động nên ngắn gọn và hấp dẫn.'),

                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->placeholder('Ví dụ: team-building-2025')
                                            ->unique(ignoreRecord: true)
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-link')
                                            ->prefixIconColor('primary')
                                            ->helperText('URL thân thiện, tự động sinh từ tên.'),

                                        TextInput::make('location_area')
                                            ->label('Khu vực tổ chức')
                                            ->placeholder('Ví dụ: Hà Nội')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-map')
                                            ->prefixIconColor('info')
                                            ->helperText('Địa điểm hoặc khu vực tổ chức.')
                                            ->nullable(),
                                    ]),

                                Fieldset::make('Mô tả và chi tiết')
                                    ->schema([
                                        RichEditor::make('short_description')
                                            ->label('Mô tả ngắn')
                                            ->placeholder('Tóm tắt hoạt động trong 1-2 câu')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bullet',
                                            ])
                                            ->maxLength(200)
                                            ->helperText('Mô tả ngắn gọn để thu hút người xem.')
                                            ->columnSpanFull(),

                                        RichEditor::make('long_description')
                                            ->label('Mô tả chi tiết')
                                            ->placeholder('Mô tả chi tiết nội dung, mục tiêu')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bullet',
                                                'ordered',
                                                'link',
                                            ])
                                            ->helperText('Sử dụng định dạng để làm nổi bật thông tin.')
                                            ->columnSpanFull(),
                                    ]),

                                Fieldset::make('Yêu cầu tham gia')
                                    ->schema([
                                        Textarea::make('conditions')
                                            ->label('Điều kiện tham gia')
                                            ->placeholder('Ví dụ: Độ tuổi từ 18-35')
                                            ->rows(4)
                                            ->helperText('Liệt kê các yêu cầu cụ thể để tham gia.')
                                            ->nullable(),

                                        TextInput::make('min_participants')
                                            ->label('Số người tối thiểu')
                                            ->numeric()
                                            ->minValue(0)
                                            ->placeholder('Ví dụ: 10')
                                            ->prefixIcon('heroicon-o-user-group')
                                            ->prefixIconColor('success')
                                            ->helperText('Số người tối thiểu để tổ chức.')
                                            ->nullable(),

                                        TextInput::make('max_participants')
                                            ->label('Số người tối đa')
                                            ->numeric()
                                            ->minValue(0)
                                            ->placeholder('Ví dụ: 50')
                                            ->prefixIcon('heroicon-o-user-group')
                                            ->prefixIconColor('success')
                                            ->helperText('Số người tối đa cho phép.')
                                            ->nullable(),
                                    ]),
                            ]),

                        Toggle::make('is_active')
                            ->label('Trạng thái hoạt động')
                            ->helperText('Bật để hiển thị hoạt động trên hệ thống.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên hoạt động')
                    ->searchable(['name', 'short_description'])
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->wrap(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('short_description')
                    ->label('Mô tả ngắn')
                    ->limit(60)
                    ->tooltip(fn ($record): string => $record->short_description ?? '')
                    ->html()
                    ->default('-')
                    ->color('gray')
                    ->wrap(),

                Tables\Columns\TextColumn::make('location_area')
                    ->label('Khu vực')
                    ->sortable()
                    ->color('info')
                    ->default('-'),

                Tables\Columns\BadgeColumn::make('min_participants')
                    ->label('Số người tối thiểu')
                    ->sortable()
                    ->color('success')
                    ->default('-')
                    ->alignCenter(),

                Tables\Columns\BadgeColumn::make('max_participants')
                    ->label('Số người tối đa')
                    ->sortable()
                    ->color('success')
                    ->default('-')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
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
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Xóa nhiều')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ]);
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
}