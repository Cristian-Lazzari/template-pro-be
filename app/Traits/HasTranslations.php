<?php

namespace App\Traits;

trait HasTranslations
{
    protected function getDefaultLocale()
    {
        return config('configurazione.default_lang');
    }

    protected function getTranslation($field)
    {
        $locale = $this->getDefaultLocale();

        $translation = $this->translations->firstWhere('lang', $locale);

        return $translation?->$field;
    }
}