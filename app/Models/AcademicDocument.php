<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AcademicDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'title',
        'description',
        'content',
        'file',
        'original_name',
        'mime_type',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function isFile(): bool
    {
        return $this->file !== null && $this->file !== '';
    }

    public function isText(): bool
    {
        return ! $this->isFile();
    }

    public function isImage(): bool
    {
        if (! $this->isFile()) {
            return false;
        }

        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png'], true);
    }

    public function fileUrl(): ?string
    {
        if (! $this->isFile()) {
            return null;
        }

        return Storage::url($this->file);
    }

    public function fileExtension(): ?string
    {
        if (! $this->isFile()) {
            return null;
        }

        return strtolower(pathinfo($this->file, PATHINFO_EXTENSION));
    }

    public function fileIcon(): string
    {
        return match ($this->fileExtension()) {
            'pdf' => 'bi-file-earmark-pdf',
            'doc', 'docx' => 'bi-file-earmark-word',
            'xls', 'xlsx' => 'bi-file-earmark-excel',
            'ppt', 'pptx' => 'bi-file-earmark-ppt',
            'zip', 'rar', '7z' => 'bi-file-earmark-zip',
            'txt', 'md' => 'bi-file-earmark-text',
            default => 'bi-file-earmark',
        };
    }

    public function typeLabel(): string
    {
        if ($this->isText()) {
            return 'Texto';
        }

        return strtoupper($this->fileExtension() ?? 'Archivo');
    }
}
