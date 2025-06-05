<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Quản lý Tài Khoản';

    protected static ?string $modelLabel = 'Tài Khoản';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin tài khoản')
                    ->description('Nhập thông tin để tạo hoặc chỉnh sửa tài khoản.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên người dùng')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nhập tên người dùng'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nhập email'),
                        Forms\Components\TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->required(fn ($operation) => $operation === 'create')
                            ->minLength(8)
                            ->dehydrated(fn ($state) => !empty($state)) // Chỉ gửi dữ liệu nếu không trống
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->placeholder('Nhập mật khẩu'),

                        Forms\Components\Select::make('roles')
                            ->label('Vai trò')
                            ->multiple() // Cho phép chọn nhiều vai trò
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Vai trò')
                    ->searchable()
                    ->formatStateUsing(function ($record) {
                        $roles = $record->roles()->pluck('name')->toArray();
                        if (empty($roles)) {
                            return 'Không có vai trò';
                        }
                        return implode(', ', $roles);
                    })
                    ->color('warning')
                    ->extraAttributes(function ($record) {
                        return [];
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state->format('d/m/Y H:i')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Lọc theo vai trò')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
//            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}