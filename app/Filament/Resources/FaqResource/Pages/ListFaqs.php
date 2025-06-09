<?php

// App/Filament/Resources/FaqResource/Pages/ListFaqs.php
namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tạo FAQ mới')
                ->icon('heroicon-m-plus')
                ->color('success'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả')
                ->icon('heroicon-m-rectangle-stack')
                ->badge($this->getModel()::count()),

            'active' => Tab::make('Đang hoạt động')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge($this->getModel()::where('is_active', true)->count()),

            'website' => Tab::make('Hiển thị Website')
                ->icon('heroicon-m-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('show_on_website', true))
                ->badge($this->getModel()::where('show_on_website', true)->count()),

            'ai_enabled' => Tab::make('Sử dụng AI')
                ->icon('heroicon-m-cpu-chip')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('use_for_ai', true))
                ->badge($this->getModel()::where('use_for_ai', true)->count()),

            'inactive' => Tab::make('Không hoạt động')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge($this->getModel()::where('is_active', false)->count()),
        ];
    }
}