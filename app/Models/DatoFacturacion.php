<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatoFacturacion extends Model
{
    use HasFactory;

    protected $table = 'datos_facturacion';

    protected $fillable = [
        'alumno_id',
        'archivo',
        'nombre_original',
        'mime_type',
        'mensaje',
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

    public function fileUrl(): string
    {
        return route('datos-facturacion.descargar', $this);
    }

    public function fileExtension(): string
    {
        return strtolower(pathinfo($this->nombre_original ?: $this->archivo, PATHINFO_EXTENSION));
    }

    public function isImage(): bool
    {
        return in_array($this->fileExtension(), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public function isPdf(): bool
    {
        return $this->fileExtension() === 'pdf';
    }

    public function fileIcon(): string
    {
        return match ($this->fileExtension()) {
            'pdf' => 'bi-file-earmark-pdf',
            'xml' => 'bi-file-earmark-code',
            'jpg', 'jpeg', 'png', 'webp', 'gif' => 'bi-file-earmark-image',
            'doc', 'docx' => 'bi-file-earmark-word',
            'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel',
            'zip', 'rar', '7z' => 'bi-file-earmark-zip',
            default => 'bi-file-earmark-text',
        };
    }
}
