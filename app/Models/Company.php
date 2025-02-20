<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends \TomatoPHP\FilamentEcommerce\Models\Company
{
    protected $fillable = [
        'name',
        'is_default',
    ];

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
    }
}
