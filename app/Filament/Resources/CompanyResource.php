<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use TomatoPHP\FilamentEcommerce\Filament\Resources\CompanyResource as ResourcesCompanyResource;

class CompanyResource extends ResourcesCompanyResource
{
    protected static ?string $model = Company::class;

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->columns(array_merge(
                parent::table($table)->getColumns(),
                [
                    ToggleColumn::make('is_default')
                        ->label('Active')
                        ->getStateUsing(fn (Company $record) => $record->is_default)
                        ->disabled()
                ]
            ))
            ->actions([
                Action::make('test_modal')
                    ->label('Set Active')
                    ->visible(fn (Company $record) => !$record->is_default)
                    ->requiresConfirmation()
                    ->modalHeading('Activate Company')
                    ->modalDescription('This will deactivate the currently active company. Are you sure you want to proceed?')
                    ->modalSubmitActionLabel('Yes, activate it')
                    ->modalCancelActionLabel('Cancel')
                    ->action(function (Company $record) {
                        // Placeholder for any action you want to perform
                        $record->activate();
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
