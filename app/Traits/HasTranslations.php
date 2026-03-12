<?php

namespace App\Traits;

trait HasTranslations
{

    protected function getTranslation($field)
    {
        $locale = app()->getLocale();

        $translation = $this->translations->firstWhere('lang', $locale);

        return $translation?->$field;
    }
}