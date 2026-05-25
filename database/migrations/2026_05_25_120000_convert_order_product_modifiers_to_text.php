<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeModifierColumns('TEXT');
    }

    public function down(): void
    {
        $this->changeModifierColumns('VARCHAR(255)');
    }

    private function changeModifierColumns(string $type): void
    {
        if (! Schema::hasTable('order_product')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (['add', 'remove', 'option'] as $column) {
            if (! Schema::hasColumn('order_product', $column)) {
                continue;
            }

            $columnInfo = DB::selectOne(
                'SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                ['order_product', $column]
            );

            $nullableSql = strtoupper($columnInfo->IS_NULLABLE ?? 'NO') === 'YES' ? 'NULL' : 'NOT NULL';

            DB::statement("ALTER TABLE `order_product` MODIFY `{$column}` {$type} {$nullableSql}");
        }
    }
};
