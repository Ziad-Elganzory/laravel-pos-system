<?php

namespace App\Filament\Resources;

// use TomatoPHP\FilamentEcommerce\Filament\Resources\CompanyResource\Pages;
use TomatoPHP\FilamentEcommerce\Filament\Resources\CompanyResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentLocations\Models\Country;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Closure;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use TomatoPHP\FilamentEcommerce\Filament\Resources\CompanyResource as ResourcesCompanyResource;

class CompanyResource extends ResourcesCompanyResource
{
    protected static ?string $model = Company::class;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('logo')
                        ->label(trans('filament-ecommerce::messages.company.columns.logo'))
                        ->collection('logo')
                        ->columnSpanFull()
                        ->image(),
                    Forms\Components\TextInput::make('name')
                        ->columnSpanFull()
                        ->label(trans('filament-ecommerce::messages.company.columns.name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label(trans('filament-ecommerce::messages.company.columns.email'))
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label(trans('filament-ecommerce::messages.company.columns.phone'))
                        ->tel()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('website')
                        ->url()
                        ->label(trans('filament-ecommerce::messages.company.columns.website'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('ceo')
                        ->label(trans('filament-ecommerce::messages.company.columns.ceo'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('registration_number')
                        ->label(trans('filament-ecommerce::messages.company.columns.registration_number'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('tax_number')
                        ->label(trans('filament-ecommerce::messages.company.columns.tax_number'))
                        ->maxLength(255),
                    Forms\Components\Textarea::make('notes')
                        ->label(trans('filament-ecommerce::messages.company.columns.notes'))
                        ->columnSpanFull(),
                ])->columns(2),
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Textarea::make('address')
                        ->columnSpanFull()
                        ->label(trans('filament-ecommerce::messages.company.columns.address'))
                        ->maxLength(255),
                    Forms\Components\Select::make('country_id')
                        ->searchable()
                        ->options(Country::query()->get()->pluck('name', 'id')->toArray())
                        ->label(trans('filament-ecommerce::messages.company.columns.country_id')),
                    Forms\Components\TextInput::make('city')
                        ->label(trans('filament-ecommerce::messages.company.columns.city'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('zip')
                        ->columnSpanFull()
                        ->label(trans('filament-ecommerce::messages.company.columns.zip'))
                        ->maxLength(255),

                ])->columns(2),
                Toggle::make('is_default')
                ->label(trans('filament-ecommerce::messages.company.columns.active'))
                ->default(fn ($record) => $record ? $record->is_default : false)
                ->live()
                ->afterStateUpdated(function ($state, callable $set, $get, $record) {
                    if ($state) {
                        $activeCompany = Company::where('is_default', true)->first();

                        if($activeCompany) {
                            $set('is_default', !$state);
                            Notification::make()
                                ->title(trans('filament-ecommerce::messages.company.notification.fail.notification_activate_fail'))
                                ->danger()
                                ->send();
                            return;
                        }
                        Notification::make()
                            ->title(trans('filament-ecommerce::messages.company.notification.success.notification_activate_success', ['company' => $record->name]))
                            ->success()
                            ->send();

                        } else {
                            $activeCompany = $record->where('is_default', true)->where('id', '!=', $record->id)->first();
                            if(!$activeCompany) {
                                $set('is_default', !$state);
                                Notification::make()
                                    ->title(trans('filament-ecommerce::messages.company.notification.fail.notification_deactivate_fail'))
                                    ->danger()
                                    ->send();
                                return;
                            }
                            Notification::make()
                                ->title(trans('filament-ecommerce::messages.company.notification.success.notification_deactivate_success', ['company' => $record->name]))
                                ->success()
                                ->send();
                    }
                })
            ]);
    }
    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->columns(array_merge(
                parent::table($table)->getColumns(),
                [
                    TextColumn::make('is_default')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => $state === '1' ? 'Active' : 'Inactive')
                        ->color(fn (string $state): string => match ($state) {
                            '1' => 'success',
                            '0' => 'danger',
                        })
                ]
            ))
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('activate')
                        ->label(trans('filament-ecommerce::messages.company.action.activate.action_lable'))
                        ->visible(fn (Company $record) => !$record->is_default)
                        ->requiresConfirmation()
                        ->modalHeading(trans('filament-ecommerce::messages.company.action.activate.modal_heading'))
                        ->modalDescription(trans('filament-ecommerce::messages.company.action.activate.modal_description'))
                        ->modalSubmitActionLabel(trans('filament-ecommerce::messages.company.action.activate.modal_submit_action_label'))
                        ->modalCancelActionLabel(trans('filament-ecommerce::messages.company.action.activate.modal_cancel_action_label'))
                        ->color('primary')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Company $record) {
                            $record->activate();
                        }),
                    Action::make('deactivate')
                        ->label(trans('filament-ecommerce::messages.company.action.deactivate.action_lable'))
                        ->visible(fn (Company $record) => $record->is_default)
                        ->requiresConfirmation()
                        ->modalHeading(trans('filament-ecommerce::messages.company.action.deactivate.modal_heading'))
                        ->modalDescription(trans('filament-ecommerce::messages.company.action.deactivate.modal_description'))
                        ->modalCancelActionLabel(trans('filament-ecommerce::messages.company.action.deactivate.modal_cancel_action_label'))
                        ->modalSubmitAction(false)
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                    ]),

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
