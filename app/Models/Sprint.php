<?php
declare(strict_types=1);
namespace App\Models;
final class Sprint
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre,
        public readonly string $fechaInicio,
        public readonly string $fechaFin,
    ) {
    }

    public static function desdeArray(array $datos): self
    {
        return new self(
            id: isset($datos['id']) ? (int) $datos['id'] : null,
            nombre: (string) ($datos['nombre'] ?? ''),
            fechaInicio: (string) ($datos['fecha_inicio'] ?? ''),
            fechaFin: (string) ($datos['fecha_fin'] ?? ''),
        );
    }

    public function aArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
        ];
    }

    public function conId(int $id): self
    {
        return new self(
            id: $id,
            nombre: $this->nombre,
            fechaInicio: $this->fechaInicio,
            fechaFin: $this->fechaFin,
        );
    }
}
