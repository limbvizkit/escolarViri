<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigrateDocumentsToPrivate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'documents:migrate-to-private
                            {--dry-run : Report what would be moved without touching any file}';

    /**
     * @var string
     */
    protected $description = 'Move alumno, documentacion and academic document files from the public disk to the private documents disk';

    /**
     * Table/column pairs that store file paths relative to the disk root.
     *
     * @var array<int, array{table: string, column: string}>
     */
    private const TARGETS = [
        ['table' => 'alumnos', 'column' => 'archivo'],
        ['table' => 'alumno_archivos', 'column' => 'archivo'],
        ['table' => 'documentos', 'column' => 'archivo'],
        ['table' => 'academic_documents', 'column' => 'file'],
    ];

    /**
     * Path prefixes that may contain PII and must live on the private disk
     * even when no database row references them.
     *
     * @var array<int, string>
     */
    private const SENSITIVE_PREFIXES = [
        'alumnos/',
        'documentos/',
        'academic-documents/',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $referencedMoved = 0;
        $orphanMoved = 0;
        $alreadyPrivate = 0;
        $missing = 0;

        /** @var array<string, true> $seen */
        $seen = [];

        foreach (self::TARGETS as $target) {
            $paths = DB::table($target['table'])
                ->whereNotNull($target['column'])
                ->where($target['column'], '!=', '')
                ->pluck($target['column']);

            foreach ($paths as $rawPath) {
                $path = (string) $rawPath;

                if (isset($seen[$path])) {
                    continue;
                }

                $seen[$path] = true;

                if (Storage::disk('documents')->exists($path)) {
                    $alreadyPrivate++;

                    continue;
                }

                if (! Storage::disk('public')->exists($path)) {
                    $missing++;

                    continue;
                }

                if (! $dryRun && ! $this->moveFile($path)) {
                    $missing++;

                    continue;
                }

                $referencedMoved++;

                $this->line($dryRun ? "[dry-run] would move {$path}" : "moved {$path}");
            }
        }

        foreach (Storage::disk('public')->allFiles() as $rawPath) {
            $path = (string) $rawPath;

            if (isset($seen[$path]) || ! $this->hasSensitivePrefix($path)) {
                continue;
            }

            if (Storage::disk('documents')->exists($path)) {
                $alreadyPrivate++;

                if (! $dryRun) {
                    Storage::disk('public')->delete($path);
                }

                continue;
            }

            if (! $dryRun && ! $this->moveFile($path)) {
                $missing++;

                continue;
            }

            $orphanMoved++;

            $this->line($dryRun ? "[dry-run] would move orphan {$path}" : "moved orphan {$path}");
        }

        $totalMoved = $referencedMoved + $orphanMoved;

        $this->newLine();
        $this->info($dryRun ? 'Dry run finished.' : 'Documents migration finished.');
        $this->line("Referenced moved: {$referencedMoved}");
        $this->line("Orphan moved: {$orphanMoved}");
        $this->line("Already private (skipped): {$alreadyPrivate}");
        $this->line("Missing (not found on either disk): {$missing}");
        $this->line("Total moved: {$totalMoved}");

        return self::SUCCESS;
    }

    private function hasSensitivePrefix(string $path): bool
    {
        foreach (self::SENSITIVE_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function moveFile(string $path): bool
    {
        $stream = Storage::disk('public')->readStream($path);

        if ($stream === false) {
            return false;
        }

        try {
            Storage::disk('documents')->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! Storage::disk('documents')->exists($path)) {
            return false;
        }

        Storage::disk('public')->delete($path);

        return true;
    }
}
