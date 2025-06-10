<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tạo ticket mới')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả')
                ->icon('heroicon-o-queue-list')
                ->badge(Ticket::count())
                ->modifyQueryUsing(fn ($query) => $query->whereNull('deleted_at'))
                ->badgeColor('primary'),

            'active' => Tab::make('Đang hoạt động')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(Ticket::where('is_active', true)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('Không hoạt động')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(Ticket::where('is_active', false)->count())
                ->badgeColor('warning'),

            'trashed' => Tab::make('Đã xóa')
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(Ticket::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return match ($this->activeTab) {
            'active' => 'Tất cả ticket hiện tại đều không hoạt động hoặc đã bị xóa.',
            'inactive' => 'Tất cả ticket hiện tại đều đang hoạt động.',
            'trashed' => 'Tất cả ticket đã xóa đều đã được xóa vĩnh viễn hoặc khôi phục.',
            default => 'Bắt đầu tạo ticket đầu tiên của bạn.',
        };
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return match ($this->activeTab) {
            'active' => 'heroicon-o-check-circle',
            'inactive' => 'heroicon-o-x-circle',
            'trashed' => 'heroicon-o-trash',
            default => 'heroicon-o-ticket',
        };
    }

    public function getDefaultActiveTab(): string
    {
        return 'all';
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
