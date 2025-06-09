<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFaq extends EditRecord
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Xem chi tiết')
                ->icon('heroicon-m-eye')
                ->color('info'),

            Actions\DeleteAction::make()
                ->label('Xóa')
                ->icon('heroicon-m-trash')
                ->requiresConfirmation()
                ->modalHeading('Xóa FAQ')
                ->modalDescription('Bạn có chắc chắn muốn xóa FAQ này? Dữ liệu có thể được khôi phục sau.')
                ->modalSubmitActionLabel('Xóa FAQ'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('FAQ đã được cập nhật')
            ->body('Thông tin FAQ đã được lưu thành công.')
            ->send();
    }
}