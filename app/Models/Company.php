<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class Company extends \TomatoPHP\FilamentEcommerce\Models\Company
{
    //Add is_default to the parent fillable array
    protected $extraFillable = [
        'is_default',
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable = array_merge($this->fillable, $this->extraFillable);

    }

    /**
     * Set this company as the active company, deactivating others.
     */
    public function activate()
    {
        $activeCompany = self::where('is_default', true)->where('id', '!=', $this->id)->first();

        if($activeCompany) {
            Notification::make()
                ->title(trans('filament-ecommerce::messages.company.notification.fail.notification_activate_fail'))
                ->danger()
                ->send();
            return false;
        }
        // $this->update(['is_default' => true]);
        Notification::make()
            ->title(trans('filament-ecommerce::messages.company.notification.success.notification_activate_success', ['company' => $this->name]))
            ->success()
            ->send();
    }

    public function deactivate(){
        $activeCompany = self::where('is_default', true)->where('id', '!=', $this->id)->first();
        if(!$activeCompany) {
            Notification::make()
            ->title(trans('filament-ecommerce::messages.company.notification.fail.notification_deactivate_fail'))
            ->danger()
            ->send();
            return false;
        }
        // $this->update(['is_default' => false]);
        Notification::make()
            ->title(trans('filament-ecommerce::messages.company.notification.success.notification_deactivate_success', ['company' => $this->name]))
            ->success()
            ->send();
    }
}
