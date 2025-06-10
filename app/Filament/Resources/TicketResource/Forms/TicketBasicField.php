<?php

namespace App\Filament\Resources\TicketResource\Forms;

use App\Models\TicketCategory;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class TicketBasicField
{
    public static function make(): Section {
        return Section::make('Chi tiết vé')
            ->description('Thông tin cơ bản về vé tham quan')
            ->icon('heroicon-o-ticket')
            ->schema([
                Grid::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên vé')
                            ->helperText('Tên hiển thị của loại vé sẽ được khách hàng thấy (tối đa 255 ký tự)')
                            ->placeholder('VD: Vé tham quan, Vé hồ bơi, Vé...')
                            ->prefixIcon('heroicon-o-tag')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->columnSpan(['md' => 2]),

                        Toggle::make('is_active')
                            ->label('Trạng thái hoạt động')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->helperText('Bật/tắt hiển thị thông tin vé cho khách hàng')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(['md' => 1]),
                    ])
                    ->columns(3),

                Select::make('categories')
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
                                TextInput::make('name')
                                    ->label('Tên danh mục')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ví dụ: VIP, Thường...')
                                    ->autofocus()
                                    ->live(onBlur: true)
                                    ->hint('Tên hiển thị của danh mục'),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(table: 'ticket_categories', column: 'slug')
                                    ->maxLength(255)
                                    ->placeholder('ví dụ: vip, thuong...')
                                    ->hint('Dùng để tạo URL thân thiện')
                                    ->rules(['regex:/^[a-z0-9-]+$/'])
                                    ->helperText('Chỉ dùng chữ thường, số và dấu gạch ngang.'),
                                Textarea::make('description')
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
                Textarea::make('description')
                    ->label('Mô tả chi tiết')
                    ->hintIcon('heroicon-o-pencil')
                    ->rows(4)
                    ->helperText('Mô tả đầy đủ về vé, trải nghiệm và thông tin khách hàng cần biết')
                    ->placeholder('Nhập mô tả chi tiết về vé, thời gian có hiệu lực, điều kiện sử dụng, lưu ý đặc biệt...')
                    ->columnSpanFull(),
                Textarea::make('includes')
                    ->label('Những gì bao gồm')
                    ->hintIcon('heroicon-o-pencil')
                    ->helperText('Format text của tất cả quyền lợi (tự động tạo từ danh sách trên hoặc nhập thủ công)')
                    ->placeholder('* Ví dụ: &#10; • Tham quan và check-in với nhiều tiểu cảnh B&#10;• Miễn phí đỗ xe&#10;• Hướng dẫn viên tiếng Việt&#10;• Tham gia 1 số hoạt động & trò chơi miễn phí&#10; ...')
                    ->rows(6)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}