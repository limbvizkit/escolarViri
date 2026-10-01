<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DeletesStoredFiles
{
    /**
     * Stream a file download from the private documents disk, falling back to
     * the legacy public disk for records that have not been migrated yet.
     *
     * Aborts with a 404 when the path is empty or the file is absent from both
     * disks, so a missing physical file never surfaces as a 500 error.
     */
    protected function downloadStoredFile(?string $path, ?string $name = null): StreamedResponse
    {
        if ($path === null || $path === '') {
            abort(404);
        }

        $downloadName = $name ?? basename($path);

        if (Storage::disk('documents')->exists($path)) {
            return Storage::disk('documents')->download($path, $downloadName);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path, $downloadName);
        }

        abort(404);
    }

    /**
     * Delete a file from the private documents disk, falling back to the
     * legacy public disk for records that have not been migrated yet.
     *
     * Never throws when the file is missing on either disk.
     */
    protected function deleteStoredFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (Storage::disk('documents')->exists($path)) {
            Storage::disk('documents')->delete($path);

            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
