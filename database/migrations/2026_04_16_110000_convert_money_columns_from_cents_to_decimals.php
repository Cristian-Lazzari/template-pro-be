<?php

use App\Support\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $convertedLegacyColumns = false;

        foreach ($this->moneyColumns() as $table => $columns) {
            foreach ($columns as $column => $definition) {
                $type = $this->columnType($table, $column);

                if ($type === null || $this->isDecimalType($type)) {
                    continue;
                }

                if (!$this->isIntegerType($type)) {
                    continue;
                }

                $convertedLegacyColumns = true;
                $this->promoteColumnToDecimal($table, $column, $definition);
                $this->convertColumnValuesToAmounts($table, $column, $definition['nullable'] ?? false);
            }
        }

        if ($convertedLegacyColumns) {
            $this->convertLegacySettingsMoneyValuesToAmounts();
        }
    }

    public function down(): void
    {
        $revertedColumns = false;

        foreach ($this->moneyColumns() as $table => $columns) {
            foreach ($columns as $column => $definition) {
                $type = $this->columnType($table, $column);

                if ($type === null || $this->isIntegerType($type)) {
                    continue;
                }

                if (!$this->isDecimalType($type)) {
                    continue;
                }

                $revertedColumns = true;
                $this->convertColumnValuesToMinorUnits($table, $column, $definition['nullable'] ?? false);
                $this->demoteColumnToInteger($table, $column, $definition);
            }
        }

        if ($revertedColumns) {
            $this->convertSettingsMoneyValuesBackToMinorUnits();
        }
    }

    protected function moneyColumns(): array
    {
        return [
            'products' => [
                'price' => ['decimal' => 'DECIMAL(12,2) NOT NULL DEFAULT 0', 'integer' => 'BIGINT NOT NULL DEFAULT 0', 'nullable' => false],
                'old_price' => ['decimal' => 'DECIMAL(12,2) NULL DEFAULT NULL', 'integer' => 'BIGINT NULL DEFAULT NULL', 'nullable' => true],
            ],
            'menus' => [
                'price' => ['decimal' => 'DECIMAL(12,2) NOT NULL', 'integer' => 'BIGINT NOT NULL', 'nullable' => false],
                'old_price' => ['decimal' => 'DECIMAL(12,2) NOT NULL DEFAULT 0', 'integer' => 'BIGINT NOT NULL DEFAULT 0', 'nullable' => false],
            ],
            'ingredients' => [
                'price' => ['decimal' => 'DECIMAL(12,2) NOT NULL', 'integer' => 'BIGINT NOT NULL', 'nullable' => false],
            ],
            'orders' => [
                'tot_price' => ['decimal' => 'DECIMAL(12,2) NOT NULL', 'integer' => 'BIGINT NOT NULL', 'nullable' => false],
            ],
            'menu_product' => [
                'extra_price' => ['decimal' => 'DECIMAL(12,2) NOT NULL DEFAULT 0', 'integer' => 'INT NOT NULL DEFAULT 0', 'nullable' => false],
            ],
        ];
    }

    protected function promoteColumnToDecimal(string $table, string $column, array $definition): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` %s',
                $table,
                $column,
                $definition['decimal']
            ));

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement(sprintf(
                'ALTER TABLE "%s" ALTER COLUMN "%s" TYPE NUMERIC(12,2) USING "%s"::numeric',
                $table,
                $column,
                $column
            ));

            $this->applyPgsqlNullabilityAndDefault($table, $column, $definition['decimal'], $definition['nullable'] ?? false);
        }
    }

    protected function demoteColumnToInteger(string $table, string $column, array $definition): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` %s',
                $table,
                $column,
                $definition['integer']
            ));

            return;
        }

        if ($driver === 'pgsql') {
            $pgsqlIntegerType = str_contains(strtoupper($definition['integer']), 'BIGINT')
                ? 'bigint'
                : 'integer';

            DB::statement(sprintf(
                'ALTER TABLE "%s" ALTER COLUMN "%s" TYPE %s USING ROUND("%s")::%s',
                $table,
                $column,
                strtoupper($pgsqlIntegerType),
                $column,
                $pgsqlIntegerType
            ));

            $this->applyPgsqlNullabilityAndDefault($table, $column, $definition['integer'], $definition['nullable'] ?? false);
        }
    }

    protected function convertColumnValuesToAmounts(string $table, string $column, bool $nullable): void
    {
        $expression = $nullable
            ? sprintf('CASE WHEN %s IS NULL THEN NULL ELSE ROUND(%s / 100, 2) END', $column, $column)
            : sprintf('ROUND(%s / 100, 2)', $column);

        DB::table($table)->update([
            $column => DB::raw($expression),
        ]);
    }

    protected function convertColumnValuesToMinorUnits(string $table, string $column, bool $nullable): void
    {
        $expression = $nullable
            ? sprintf('CASE WHEN %s IS NULL THEN NULL ELSE ROUND(%s * 100, 0) END', $column, $column)
            : sprintf('ROUND(%s * 100, 0)', $column);

        DB::table($table)->update([
            $column => DB::raw($expression),
        ]);
    }

    protected function convertLegacySettingsMoneyValuesToAmounts(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $this->updateJsonSetting('Prenotazione Asporti', function (array $property): array {
            foreach (['min_price'] as $field) {
                if (array_key_exists($field, $property)) {
                    $property[$field] = Currency::fromMinorUnits($property[$field], 2);
                }
            }

            return $property;
        });

        $this->updateJsonSetting('Possibilità di consegna a domicilio', function (array $property): array {
            foreach (['min_price', 'delivery_cost'] as $field) {
                if (array_key_exists($field, $property)) {
                    $property[$field] = Currency::fromMinorUnits($property[$field], 2);
                }
            }

            return $property;
        });

        $this->updateJsonSetting('Comuni per il domicilio', function (array $property): array {
            foreach ($property as &$row) {
                if (is_array($row) && array_key_exists('price', $row)) {
                    $row['price'] = Currency::fromMinorUnits($row['price'], 2);
                }
            }

            return $property;
        });
    }

    protected function convertSettingsMoneyValuesBackToMinorUnits(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $this->updateJsonSetting('Prenotazione Asporti', function (array $property): array {
            foreach (['min_price'] as $field) {
                if (array_key_exists($field, $property)) {
                    $property[$field] = (int) round(((float) $property[$field]) * 100);
                }
            }

            return $property;
        });

        $this->updateJsonSetting('Possibilità di consegna a domicilio', function (array $property): array {
            foreach (['min_price', 'delivery_cost'] as $field) {
                if (array_key_exists($field, $property)) {
                    $property[$field] = (int) round(((float) $property[$field]) * 100);
                }
            }

            return $property;
        });

        $this->updateJsonSetting('Comuni per il domicilio', function (array $property): array {
            foreach ($property as &$row) {
                if (is_array($row) && array_key_exists('price', $row)) {
                    $row['price'] = (int) round(((float) $row['price']) * 100);
                }
            }

            return $property;
        });
    }

    protected function updateJsonSetting(string $name, callable $callback): void
    {
        $setting = DB::table('settings')->where('name', $name)->first();

        if (!$setting) {
            return;
        }

        $property = json_decode($setting->property ?? '[]', true);

        if (!is_array($property)) {
            return;
        }

        $updated = $callback($property);

        DB::table('settings')
            ->where('id', $setting->id)
            ->update([
                'property' => json_encode($updated),
                'updated_at' => now(),
            ]);
    }

    protected function columnType(string $table, string $column): ?string
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return null;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $row = DB::table('information_schema.columns')
                ->select(['data_type', 'column_type'])
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('column_name', $column)
                ->first();

            if (!$row) {
                return null;
            }

            return strtolower((string) ($row->column_type ?? $row->data_type ?? ''));
        }

        if ($driver === 'sqlite') {
            $columns = DB::select(sprintf("PRAGMA table_info('%s')", $table));

            foreach ($columns as $info) {
                if (($info->name ?? null) === $column) {
                    return strtolower((string) ($info->type ?? ''));
                }
            }

            return null;
        }

        if ($driver === 'pgsql') {
            $row = DB::table('information_schema.columns')
                ->select(['data_type', 'numeric_precision', 'numeric_scale'])
                ->where('table_schema', 'public')
                ->where('table_name', $table)
                ->where('column_name', $column)
                ->first();

            if (!$row) {
                return null;
            }

            return strtolower(trim(implode(' ', array_filter([
                $row->data_type ?? null,
                isset($row->numeric_precision, $row->numeric_scale)
                    ? sprintf('(%s,%s)', $row->numeric_precision, $row->numeric_scale)
                    : null,
            ]))));
        }

        return null;
    }

    protected function isDecimalType(string $type): bool
    {
        return str_contains($type, 'decimal')
            || str_contains($type, 'numeric')
            || str_contains($type, 'real');
    }

    protected function isIntegerType(string $type): bool
    {
        return str_contains($type, 'int');
    }

    protected function applyPgsqlNullabilityAndDefault(string $table, string $column, string $definition, bool $nullable): void
    {
        if ($nullable) {
            DB::statement(sprintf('ALTER TABLE "%s" ALTER COLUMN "%s" DROP NOT NULL', $table, $column));
        } else {
            DB::statement(sprintf('ALTER TABLE "%s" ALTER COLUMN "%s" SET NOT NULL', $table, $column));
        }

        $default = null;

        if (preg_match('/default\s+([^\s]+)/i', $definition, $matches) === 1) {
            $default = $matches[1];
        }

        if ($default === null) {
            DB::statement(sprintf('ALTER TABLE "%s" ALTER COLUMN "%s" DROP DEFAULT', $table, $column));

            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE "%s" ALTER COLUMN "%s" SET DEFAULT %s',
            $table,
            $column,
            $default
        ));
    }
};
