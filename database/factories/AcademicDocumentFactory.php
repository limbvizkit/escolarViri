<?php

namespace Database\Factories;

use App\Models\AcademicDocument;
use App\Models\Alumno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicDocument>
 */
class AcademicDocumentFactory extends Factory
{
    protected $model = AcademicDocument::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'content' => fake()->optional()->paragraph(),
            'file' => null,
            'original_name' => null,
            'mime_type' => null,
        ];
    }

    public function asFile(string $path, string $originalName, string $mimeType): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => null,
            'file' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
        ]);
    }

    public function asText(string $content): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $content,
            'file' => null,
            'original_name' => null,
            'mime_type' => null,
        ]);
    }
}
