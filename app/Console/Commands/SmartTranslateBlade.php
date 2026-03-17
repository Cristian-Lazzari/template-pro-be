<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SmartTranslateBlade extends Command
{
    protected $signature = 'translations:smart';

    protected $description = 'Sostituisce automaticamente le stringhe statiche Blade con __() ignorando commenti e codice';

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

            $comments = [];

            // Protegge tutti i commenti
            $content = preg_replace_callback(
                '/{{--.*?--}}|<!--.*?-->|\/\/.*?$|\/\*.*?\*\//ms',
                function ($match) use (&$comments) {

                    $key = '__COMMENT_' . count($comments) . '__';

                    $comments[$key] = $match[0];

                    return $key;
                },
                $content
            );

            preg_match_all('/>([^<]+)</', $content, $matches);

            foreach ($matches[1] as $text) {

                $original = $text;

                $text = trim($text);

                if (
                    strlen($text) < 3 ||
                    str_contains($text, '{{') ||
                    str_contains($text, '$') ||
                    str_contains($text, '__(') ||
                    is_numeric($text) ||
                    preg_match('/^[€$%0-9]+$/', $text)
                ) {
                    continue;
                }

                $key = str_replace(' ', '_', $text);
                $key = preg_replace('/[^A-Za-z0-9_]/', '', $key);

                if (!$key || strlen($key) < 3) {
                    continue;
                }

                if (!isset($translations[$key])) {
                    $translations[$key] = $text;
                }

                $content = str_replace(
                    ">$original<",
                    ">{{ __('admin.$key') }}<",
                    $content
                );
            }

            // placeholder=""
            $content = preg_replace_callback(
                '/placeholder="([^"]+)"/',
                function ($match) use (&$translations) {

                    $text = trim($match[1]);

                    if (strlen($text) < 3) {
                        return $match[0];
                    }

                    $key = str_replace(' ', '_', $text);
                    $key = preg_replace('/[^A-Za-z0-9_]/', '', $key);

                    if (!$key) {
                        return $match[0];
                    }

                    if (!isset($translations[$key])) {
                        $translations[$key] = $text;
                    }

                    return 'placeholder="{{__(\'admin.' . $key . '\')}}"';
                },
                $content
            );

            // title=""
            $content = preg_replace_callback(
                '/title="([^"]+)"/',
                function ($match) use (&$translations) {

                    $text = trim($match[1]);

                    if (strlen($text) < 3) {
                        return $match[0];
                    }

                    $key = str_replace(' ', '_', $text);
                    $key = preg_replace('/[^A-Za-z0-9_]/', '', $key);

                    if (!$key) {
                        return $match[0];
                    }

                    if (!isset($translations[$key])) {
                        $translations[$key] = $text;
                    }

                    return 'title="{{__(\'admin.' . $key . '\')}}"';
                },
                $content
            );

            // ripristina commenti
            foreach ($comments as $key => $comment) {
                $content = str_replace($key, $comment, $content);
            }

            File::put($path, $content);
        }

        $export = "<?php\n\nreturn " . var_export($translations, true) . ";\n";

        File::put($langFile, $export);

        $this->info("✔ Traduzioni generate senza toccare commenti.");
    }
}