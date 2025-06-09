<?php

namespace App\Filament\Resources\FaqResource\Widgets;

namespace App\Filament\Resources\FaqResource\Widgets;

use App\Models\Faq;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FaqStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Tổng số FAQ', Faq::count())
                ->description('Tổng câu hỏi thường gặp')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('primary'),

            Stat::make('FAQ đang hoạt động', Faq::where('is_active', true)->count())
                ->description('FAQ đang được sử dụng')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Hiển thị trên Website', Faq::where('show_on_website', true)->count())
                ->description('FAQ công khai')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Sử dụng cho AI', Faq::where('use_for_ai', true)->count())
                ->description('FAQ cho chatbot')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('warning'),
        ];
    }
}