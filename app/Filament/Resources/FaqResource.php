<?php

namespace App\Filament\Resources;

use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FaqResource\Pages;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\Grid as InfoGrid;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Câu hỏi thường gặp';

    protected static ?string $modelLabel = 'Câu hỏi thường gặp';

    protected static ?string $pluralModelLabel = 'Câu hỏi thường gặp';

    protected static ?string $navigationGroup = 'Quản lý nội dung';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin câu hỏi')
                    ->description('Nhập thông tin chi tiết về câu hỏi thường gặp')
                    ->icon('heroicon-m-question-mark-circle')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('question')
                                    ->label('Câu hỏi chính')
                                    ->placeholder('Nhập câu hỏi chính mà khách hàng thường đặt...')
                                    ->required()
                                    ->maxLength(1000)
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->helperText('Câu hỏi này sẽ hiển thị làm tiêu đề chính cho FAQ'),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Section::make('Nội dung trả lời')
                    ->description('Soạn nội dung trả lời cho câu hỏi')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Textarea::make('answer_plain')
                                    ->label('Trả lời (Văn bản thuần)')
                                    ->placeholder('Nhập nội dung trả lời dưới dạng văn bản thuần túy...')
                                    ->required()
                                    ->rows(4)
                                    ->maxLength(5000)
                                    ->helperText('Nội dung này sẽ được sử dụng cho tìm kiếm và API'),

                                RichEditor::make('answer_html')
                                    ->label('Trả lời (HTML)')
                                    ->placeholder('Soạn nội dung trả lời với định dạng HTML...')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'link',
                                        'bulletList',
                                        'orderedList',
                                        'h2',
                                        'h3',
                                        'blockquote',
                                        'codeBlock',
                                    ])
                                    ->helperText('Nội dung này sẽ hiển thị trên website với định dạng đẹp'),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Section::make('Câu hỏi liên quan')
                    ->description('Thêm các câu hỏi tương tự để tăng khả năng tìm kiếm')
                    ->icon('heroicon-m-magnifying-glass')
                    ->schema([
                        TagsInput::make('example_questions')
                            ->label('Câu hỏi ví dụ')
                            ->placeholder('Nhập câu hỏi tương tự và nhấn Enter để thêm...')
                            ->helperText('Thêm các câu hỏi tương tự để người dùng dễ tìm thấy FAQ này hơn')
                            ->suggestions([
                                'Làm thế nào để...',
                                'Tại sao...',
                                'Khi nào...',
                                'Ở đâu...',
                                'Có thể...',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Section::make('Cài đặt hiển thị')
                    ->description('Điều chỉnh cách hiển thị và sử dụng FAQ')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Kích hoạt')
                                    ->helperText('FAQ có được sử dụng không')
                                    ->default(true)
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->onColor('success')
                                    ->offColor('danger'),

                                Toggle::make('show_on_website')
                                    ->label('Hiển thị trên Website')
                                    ->helperText('Hiển thị FAQ này trên trang web công khai')
                                    ->default(true)
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('info')
                                    ->offColor('gray'),

                                Toggle::make('use_for_ai')
                                    ->label('Sử dụng cho AI')
                                    ->helperText('AI chatbot có thể sử dụng FAQ này')
                                    ->default(true)
                                    ->onIcon('heroicon-m-cpu-chip')
                                    ->offIcon('heroicon-m-no-symbol')
                                    ->onColor('warning')
                                    ->offColor('gray'),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('Câu hỏi')
                    ->searchable()
                    ->sortable()
                    ->limit(80)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 80) {
                            return null;
                        }
                        return $state;
                    })
                    ->weight('medium'),

                IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                IconColumn::make('show_on_website')
                    ->label('Website')
                    ->boolean()
                    ->trueIcon('heroicon-m-eye')
                    ->falseIcon('heroicon-m-eye-slash')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->sortable(),

                IconColumn::make('use_for_ai')
                    ->label('AI')
                    ->boolean()
                    ->trueIcon('heroicon-m-cpu-chip')
                    ->falseIcon('heroicon-m-no-symbol')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Người tạo')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updater.name')
                    ->label('Cập nhật bởi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lần cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->color('gray'),

                TextColumn::make('deleted_at')
                    ->label('Ngày xóa')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('active_only')
                    ->label('Chỉ FAQ đang hoạt động')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->default(),

                Filter::make('website_visible')
                    ->label('Hiển thị trên Website')
                    ->query(fn (Builder $query): Builder => $query->where('show_on_website', true)),

                Filter::make('ai_enabled')
                    ->label('Sử dụng cho AI')
                    ->query(fn (Builder $query): Builder => $query->where('use_for_ai', true)),

                TrashedFilter::make()
                    ->label('Trạng thái xóa'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem chi tiết')
                        ->color('info')
                        ->slideOver(),
                    EditAction::make()
                        ->label('Chỉnh sửa')
                        ->color('warning'),
                    DeleteAction::make()
                        ->label('Xóa')
                        ->requiresConfirmation()
                        ->modalHeading('Xóa FAQ')
                        ->modalDescription('Bạn có chắc chắn muốn xóa FAQ này? Dữ liệu có thể được khôi phục sau.')
                        ->modalSubmitActionLabel('Xóa FAQ'),
                    RestoreAction::make()
                        ->label('Khôi phục')
                        ->color('success'),
                    ForceDeleteAction::make()
                        ->label('Xóa vĩnh viễn')
                        ->requiresConfirmation()
                        ->modalHeading('Xóa vĩnh viễn FAQ')
                        ->modalDescription('Bạn có chắc chắn muốn xóa vĩnh viễn FAQ này? Hành động này không thể hoàn tác.')
                        ->modalSubmitActionLabel('Xóa vĩnh viễn'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Thao tác')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa đã chọn')
                        ->requiresConfirmation()
                        ->modalHeading('Xóa các FAQ đã chọn')
                        ->modalDescription('Bạn có chắc chắn muốn xóa các FAQ đã chọn?')
                        ->modalSubmitActionLabel('Xóa tất cả'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi phục đã chọn'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn đã chọn')
                        ->requiresConfirmation()
                        ->modalHeading('Xóa vĩnh viễn các FAQ đã chọn')
                        ->modalDescription('Bạn có chắc chắn muốn xóa vĩnh viễn các FAQ đã chọn? Hành động này không thể hoàn tác.')
                        ->modalSubmitActionLabel('Xóa vĩnh viễn tất cả'),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make('Thông tin câu hỏi')
                    ->icon('heroicon-m-question-mark-circle')
                    ->schema([
                        TextEntry::make('question')
                            ->label('Câu hỏi chính')
                            ->columnSpanFull(),
                    ]),

                InfoSection::make('Nội dung trả lời')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->schema([
                        TextEntry::make('answer_plain')
                            ->label('Trả lời (Văn bản thuần)')
                            ->columnSpanFull(),
                        TextEntry::make('answer_html')
                            ->label('Trả lời (HTML)')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                InfoSection::make('Câu hỏi liên quan')
                    ->icon('heroicon-m-magnifying-glass')
                    ->schema([
                        TextEntry::make('example_questions')
                            ->label('Câu hỏi ví dụ')
                            ->badge()
                            ->columnSpanFull(),
                    ]),

                InfoGrid::make(3)
                    ->schema([
                        InfoSection::make('Trạng thái')
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label('Kích hoạt')
                                    ->boolean()
                                    ->trueIcon('heroicon-m-check-circle')
                                    ->falseIcon('heroicon-m-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                                IconEntry::make('show_on_website')
                                    ->label('Hiển thị Website')
                                    ->boolean()
                                    ->trueIcon('heroicon-m-eye')
                                    ->falseIcon('heroicon-m-eye-slash')
                                    ->trueColor('info')
                                    ->falseColor('gray'),
                                IconEntry::make('use_for_ai')
                                    ->label('Sử dụng AI')
                                    ->boolean()
                                    ->trueIcon('heroicon-m-cpu-chip')
                                    ->falseIcon('heroicon-m-no-symbol')
                                    ->trueColor('warning')
                                    ->falseColor('gray'),
                            ]),

                        InfoSection::make('Thông tin quản lý')
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Người tạo'),
                                TextEntry::make('updater.name')
                                    ->label('Cập nhật bởi'),
                            ]),

                        InfoSection::make('Thời gian')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Ngày tạo')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Cập nhật lần cuối')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
//            'view' => Pages\ViewFaq::route('/{record}'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}