<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Facades\Filament;
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
                        ->label(trans('filament-ecommerce::messages.company.columns.active'))
                        ->getStateUsing(fn (Company $record) => $record->is_default)
                        ->disabled()
                ]
            ))
            ->actions([
                Action::make('test_modal')
                    ->label(trans('filament-ecommerce::messages.company.action.action_lable'))
                    ->visible(fn (Company $record) => !$record->is_default)
                    ->requiresConfirmation()
                    ->modalHeading(trans('filament-ecommerce::messages.company.action.modal_heading'))
                    ->modalDescription(trans('filament-ecommerce::messages.company.action.modal_description'))
                    ->modalSubmitActionLabel(trans('filament-ecommerce::messages.company.action.modal_submit_action_label'))
                    ->modalCancelActionLabel(trans('filament-ecommerce::messages.company.action.modal_cancel_action_label'))
                    ->action(function (Company $record) {
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
