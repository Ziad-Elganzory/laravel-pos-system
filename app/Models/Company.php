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
     * Check if another company is active.
     *
     * @return Company|null Returns the active company if one exists, null otherwise.
     */
    public static function getActiveCompany(): ?Company
    {
        return self::where('is_default', true)->first();
    }

    /**
     * Set this company as the active company, deactivating others.
     */
    public function activate(): void
    {
        // Deactivate the currently active company
        static::where('is_default', true)->update(['is_default' => false]);

        // Set this company as active
        $this->update(['is_default' => true]);
        Notification::make()
            ->title(trans('filament-ecommerce::messages.company.notification.message', ['company' => $this->name]))
            ->success()
            ->send();
    }
}
