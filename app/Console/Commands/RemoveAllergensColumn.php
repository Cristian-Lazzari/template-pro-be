<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAllergensColumn extends Command
{
    protected $signature = 'columns:remove-allergens';

    protected $description = 'Rimuove la colonna allergens da ingredients e products';

    public function handle(): int
    {
        $this->removeColumns('ingredients', ['allergens']);
        $this->removeColumns('products', ['allergens']);

        $this->info('Colonna allergens rimossa con successo.');

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
