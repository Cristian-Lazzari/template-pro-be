<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOldColumns extends Command
{
    protected $signature = 'columns:remove-old';

    protected $description = 'Rimuove le vecchie colonne name e description da categories, products, ingredients e menus';

    public function handle(): int
    {
        $this->removeColumns('categories', ['name', 'description']);
        $this->removeColumns('products', ['name', 'description']);
        $this->removeColumns('ingredients', ['name']);
        $this->removeColumns('menus', ['name', 'description']);

        $this->info('Colonne vecchie rimosse con successo.');

        return self::SUCCESS;
    }

    private function removeColumns(string $tableName, array $columns): void
    {
        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($tableName, $column)
        ));

        if ($existingColumns === []) {
            $this->line("Nessuna colonna da rimuovere in {$tableName}.");

            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });

        $this->info('Rimosse da ' . $tableName . ': ' . implode(', ', $existingColumns));
    }
}
