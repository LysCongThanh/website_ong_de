<?php

namespace App\Filament\Resources\PackageResource\Forms;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Toggle;

class PackageInformationField
{
    public static function make(): array
    {
        return [
            Forms\Components\Grid::make(12)
                ->schema([
                    Forms\Components\Section::make()
                        ->columnSpan('8')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('Tiêu đề gói')
                                        ->placeholder('VD: Tour Hà Nội 3 ngày 2 đêm')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->helperText('Tiêu đề sẽ hiển thị trên card danh sách và trang chi tiết')
                                        ->suffixIcon('heroicon-m-pencil-square')
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('code')
                                        ->label('Mã gói')
                                        ->placeholder('VD: HN3N2D001')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(50)
                                        ->helperText('Mã định danh duy nhất cho gói dịch vụ')
                                        ->suffixIcon('heroicon-m-hashtag')
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('type')
                                        ->label('Loại gói')
                                        ->options([
                                            'Nhóm khách lẻ' => 'Nhóm khách lẻ',
                                            'Lữ hành' => 'Lữ hành',
                                        ])
                                        ->required()
                                        ->native(false)
                                        ->helperText('Chọn loại gói phù hợp với hình thức tổ chức')
                                        ->suffixIcon('heroicon-m-user-group')
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('duration')
                                        ->label('Thời lượng')
                                        ->placeholder('VD: 3 ngày 2 đêm, trong ngày')
                                        ->maxLength(100)
                                        ->helperText('Mô tả thời gian diễn ra gói dịch vụ')
                                        ->suffixIcon('heroicon-m-clock')
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('min_quantity')
                                        ->label('Số lượng tối thiểu')
                                        ->numeric()
                                        ->placeholder('Nhập số lượng tối thiểu...')
                                        ->minValue(1)
                                        ->helperText('Số người tối thiểu để có thể đặt')
                                        ->suffixIcon('heroicon-m-users')
                                        ->columnSpan(1),
                                ]),

                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Textarea::make('summary')
                                        ->label('Mô tả ngắn')
                                        ->hintIcon('heroicon-o-pencil')
                                        ->placeholder('Mô tả ngắn gọn hiển thị trên danh sách')
                                        ->rows(3)
                                        ->maxLength(500),

                                    TinyEditor::make('content')
                                        ->label('Mô tả chi tiết')
                                        ->hintIcon('heroicon-o-pencil')
                                        ->height('400')
                                        ->placeholder('Mô tả lịch trình, dịch vụ...'),

                                    Forms\Components\Textarea::make('conditions')
                                        ->hintIcon('heroicon-o-pencil')
                                        ->label('Điều kiện đặt gói')
                                        ->placeholder('VD: Đặt trước 24h, tối thiểu 4 người...')
                                        ->rows(3),
                                ])
                        ]),

                    Forms\Components\Section::make()
                        ->columnSpan('4')
                        ->schema([
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Toggle::make('is_active')
                                        ->label('Trạng thái hoạt động')
                                        ->onIcon('heroicon-o-check')
                                        ->offIcon('heroicon-o-x-mark')
                                        ->helperText('Bật/tắt hiển thị thông tin vé cho khách hàng')
                                        ->default(true),
                                    Forms\Components\Toggle::make('is_featured')
                                        ->label('Gói nổi bật')
                                        ->helperText('Đánh dấu là gói được ưu tiên hiển thị')
                                        ->default(false)
                                        ->onIcon('heroicon-m-star')
                                        ->offIcon('heroicon-m-star')
                                        ->onColor('warning')
                                ]),

                            SpatieMediaLibraryFileUpload::make('main_image')
                                ->label('Hình ảnh chính')
                                ->collection('main_image')
                                ->image()
                                ->maxFiles(1)
                                ->downloadable()
                                ->hintIcon('heroicon-m-camera')
                                ->previewable(true)
                                ->helperText('Chọn hình ảnh đại diện chính cho hoạt động (tối đa 1 ảnh)'),

                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\DateTimePicker::make('available_start')
                                        ->label('Thời gian bắt đầu')
                                        ->helperText('Thời điểm bắt đầu có thể bán gói này')
                                        ->placeholder('Chọn thời gian bắt đầu...')
                                        ->displayFormat('d/m/Y H:i')
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-clock')
                                        ->suffixIcon('heroicon-m-play')
                                        ->columnSpan(1),

                                    Forms\Components\DateTimePicker::make('available_end')
                                        ->label('Thời gian kết thúc')
                                        ->placeholder('Chọn thời gian kết thúc...')
                                        ->helperText('Thời điểm ngừng bán gói này')
                                        ->displayFormat('d/m/Y H:i')
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-clock')
                                        ->suffixIcon('heroicon-m-stop')
                                        ->columnSpan(1),
                                ]),

                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Select::make('categories')
                                        ->label('Danh mục')
                                        ->relationship('categories', 'name')
                                        ->multiple()
                                        ->preload()
                                        ->searchable()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')
                                                ->label('Tên danh mục')
                                                ->required()
                                                ->suffixIcon('heroicon-m-folder'),
                                            Forms\Components\TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->suffixIcon('heroicon-m-link'),
                                            Forms\Components\Textarea::make('description')
                                                ->label('Mô tả')
                                                ->rows(2),
                                            Forms\Components\Toggle::make('is_active')
                                                ->label('Kích hoạt')
                                                ->default(true),
                                        ])
                                        ->helperText('Chọn hoặc tạo mới danh mục cho gói này')
                                        ->suffixIcon('heroicon-m-folder-open')
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('audiences')
                                        ->label('Đối tượng')
                                        ->relationship('audiences', 'name')
                                        ->multiple()
                                        ->preload()
                                        ->searchable()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')
                                                ->label('Tên đối tượng')
                                                ->required()
                                                ->suffixIcon('heroicon-m-user'),
                                            Forms\Components\Textarea::make('description')
                                                ->label('Mô tả')
                                                ->rows(2),
                                            Forms\Components\TextInput::make('icon')
                                                ->label('Icon')
                                                ->suffixIcon('heroicon-m-photo'),
                                        ])
                                        ->helperText('Chọn các đối tượng phù hợp (VD: Gia đình, Couple, Nhóm bạn)')
                                        ->suffixIcon('heroicon-m-user-group')
                                        ->columnSpan(1),
                                ]),
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\TagsInput::make('keywords')
                                        ->label('Từ khóa SEO')
                                        ->placeholder('Nhấn Enter để thêm từ khóa')
                                        ->helperText('Các từ khóa giúp tìm kiếm và SEO (VD: tour tiết kiệm, vui chơi)')
                                        ->suggestions([
                                            'tour tiết kiệm',
                                            'tour cao cấp',
                                            'du lịch gia đình',
                                            'tour khám phá',
                                            'nghỉ dưỡng',
                                            'vui chơi giải trí',
                                        ])
                                        ->separator(',')
                                        ->splitKeys(['Tab', ','])
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('meta_description')
                                        ->label('Meta Description')
                                        ->placeholder('Mô tả ngắn gọn cho SEO, hiển thị trên kết quả tìm kiếm Google')
                                        ->rows(3)
                                        ->maxLength(160)
                                        ->helperText('Tối đa 160 ký tự, hiển thị trên Google Search')
                                        ->columnSpanFull(),
                                ])
                        ])
                ]),
        ];
    }
}