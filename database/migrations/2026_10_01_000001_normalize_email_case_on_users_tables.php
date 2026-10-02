<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Row-level failures are expected when two existing emails only differ by
     * case and collapse onto the same unique index. Disabling the wrapping
     * transaction keeps the migration from aborting on those collisions.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        $this->normalizeEmails('users');
        $this->normalizeEmails('portal_users');
    }

    public function down(): void
    {
        // Intentionally empty: lowercasing is irreversible and restoring the
        // original casing would be arbitrary. This migration never destroys data.
    }

    /**
     * Lowercase (and trim) every stored email. Rows whose normalized value
     * would collide with an existing unique email are left untouched and
     * reported through a warning instead of aborting the migration.
     */
    private function normalizeEmails(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $rows = DB::table($table)
            ->select('id', 'email')
            ->whereRaw('email <> lower(trim(email))')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $normalized = strtolower(trim((string) $row->email));

            try {
                DB::table($table)->where('id', $row->id)->update(['email' => $normalized]);
            } catch (QueryException) {
                Log::warning('No se pudo normalizar el correo por conflicto de unicidad.', [
                    'table' => $table,
                    'id' => $row->id,
                    'email' => $row->email,
                ]);
            }
        }
    }
};
