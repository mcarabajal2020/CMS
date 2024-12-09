<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\select;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

use function Laravel\Prompts\select;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('password')->required()->password()->hiddenOn('edit'),
                select::make('roles')->multiple()->relationship('roles','name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')->sortable()->toggleable()->searchable(),
                TextColumn::make('email')->sortable()->toggleable()->searchable(),
                TextColumn::make('email_verified_at')->sortable()->toggleable()->searchable(),
                TextColumn::make('roles.name')->searchable(),
            ])
            ->filters([
                //
                Tables\Filters\Filter::make('verified')
                ->query(fn (Builder $query): Builder => $query->whereNotNull ('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Verify')->icon('heroicon-m-check-badge')->action(function(user $user){
                    $user->email_verified_at = date('Y-m-d H:i:s');
                    $user->save();
                }),
                Tables\Actions\Action::make('Unverify')->icon('heroicon-m-x-circle')->action(function(user $user){
                    $user->email_verified_at = null;
                    $user->save();
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
