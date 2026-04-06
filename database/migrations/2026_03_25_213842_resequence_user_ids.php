<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $userIds = DB::table('users')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($userIds === []) {
            $this->resetAutoIncrement(1);

            return;
        }

        $idMap = [];

        foreach ($userIds as $index => $oldId) {
            $newId = $index + 1;

            if ($oldId !== $newId) {
                $idMap[$oldId] = $newId;
            }
        }

        $temporaryOffset = max($userIds) + count($userIds) + 1000;
        $referenceColumns = $this->referenceColumns();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::transaction(function () use ($idMap, $temporaryOffset, $referenceColumns) {
                foreach ($idMap as $oldId => $newId) {
                    $temporaryId = $oldId + $temporaryOffset;
                    $this->updateReferences($referenceColumns, $oldId, $temporaryId);
                }

                foreach ($idMap as $oldId => $newId) {
                    $temporaryId = $oldId + $temporaryOffset;
                    DB::table('users')
                        ->where('id', $oldId)
                        ->update(['id' => $temporaryId]);
                }

                foreach ($idMap as $oldId => $newId) {
                    $temporaryId = $oldId + $temporaryOffset;
                    DB::table('users')
                        ->where('id', $temporaryId)
                        ->update(['id' => $newId]);
                    $this->updateReferences($referenceColumns, $temporaryId, $newId);
                }
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->resetAutoIncrement(count($userIds) + 1);
    }

    public function down(): void
    {
        // Irreversible data correction.
    }

    private function updateReferences(array $referenceColumns, int $from, int $to): void
    {
        foreach ($referenceColumns as $reference) {
            DB::table($reference['table'])
                ->where($reference['column'], $from)
                ->update([$reference['column'] => $to]);
        }
    }

    private function resetAutoIncrement(int $nextId): void
    {
        DB::statement(sprintf('ALTER TABLE users AUTO_INCREMENT = %d', $nextId));
    }

    private function referenceColumns(): array
    {
        $database = DB::getDatabaseName();

        $references = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select(['TABLE_NAME as table_name', 'COLUMN_NAME as column_name'])
            ->where('TABLE_SCHEMA', $database)
            ->where('REFERENCED_TABLE_NAME', 'users')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get()
            ->map(fn ($row) => [
                'table' => $row->table_name,
                'column' => $row->column_name,
            ])
            ->all();

        $knownReferences = [
            ['table' => 'sessions', 'column' => 'user_id'],
            ['table' => 'role_user', 'column' => 'user_id'],
            ['table' => 'permission_user', 'column' => 'user_id'],
            ['table' => 'model_has_roles', 'column' => 'model_id'],
            ['table' => 'model_has_permissions', 'column' => 'model_id'],
        ];

        foreach ($knownReferences as $reference) {
            if (! Schema::hasTable($reference['table']) || ! Schema::hasColumn($reference['table'], $reference['column'])) {
                continue;
            }

            $alreadyIncluded = collect($references)->contains(
                fn ($item) => $item['table'] === $reference['table'] && $item['column'] === $reference['column']
            );

            if (! $alreadyIncluded) {
                $references[] = $reference;
            }
        }

        return $references;
    }
};
