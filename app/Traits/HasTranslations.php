<?php

namespace App\Traits;

trait HasTranslations
{

    protected function getTranslation($field)
    {
        $locale = app()->getLocale();
        $defaultLocale = config('app.locale');

        // Prefer requested locale, but fallback to default locale when the field is empty.
        $currentTranslation = $this->translations->firstWhere('lang', $locale);
        $currentValue = $currentTranslation?->$field;

        if ($currentValue !== null && $currentValue !== '') {
            return $currentValue;
        }

        $defaultTranslation = $this->translations->firstWhere('lang', $defaultLocale);

        return $defaultTranslation?->$field;
    }
}