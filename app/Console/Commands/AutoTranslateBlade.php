<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AutoTranslateBlade extends Command
{
    protected $signature = 'translations:auto';

    protected $description = 'Sostituisce stringhe statiche nelle blade con __() e le salva nel file admin.php';

    public function handle()
    {
        $views = File::allFiles(resource_path('views'));

        $langFile = lang_path('it/admin.php');

        $translations = File::exists($langFile)
            ? include $langFile
            : [];

        foreach ($views as $file) {

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getRealPath();
            $content = File::get($path);

            preg_match_all('/>([^<>{}@\n]+)</', $content, $matches);

            foreach ($matches[1] as $text) {

                $text = trim($text);

                if (
                    strlen($text) < 3 ||
                    str_contains($text, '{{') ||
                    str_contains($text, '__(') ||
                    is_numeric($text)
                ) {
                    continue;
                }

                $key = str_replace(' ', '_', $text);
                $key = preg_replace('/[^A-Za-z0-9_]/', '', $key);

                if (!isset($translations[$key])) {
                    $translations[$key] = $text;
                }

                $content = str_replace(
                    ">$text<",
                    ">{{ __('admin.$key') }}<",
                    $content
                );
            }

            File::put($path, $content);
        }

        $export = "<?php\n\nreturn " . var_export($translations, true) . ";\n";

        File::put($langFile, $export);

        $this->info('Traduzioni generate automaticamente!');
    }
}