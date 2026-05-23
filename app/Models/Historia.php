<?php
declare(strict_types=1);
namespace App\Models;
final class Historia
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $titulo,
        public readonly string $descripcion,
        public readonly string $responsable,
        public readonly string $estado,
        public readonly int $puntos,
        public readonly string $fechaCreacion,
        public readonly ?string $fechaFinalizacion,
        public readonly int $sprintId,
    ) {
    }

    public static function desdeArray(array $datos): self
    {
        return new self(
            id: isset($datos['id']) ? (int) $datos['id'] : null,
            titulo: (string) ($datos['titulo'] ?? ''),
            descripcion: (string) ($datos['descripcion'] ?? ''),
            responsable: (string) ($datos['responsable'] ?? ''),
            estado: (string) ($datos['estado'] ?? ''),
            puntos: (int) ($datos['puntos'] ?? 0),
            fechaCreacion: (string) ($datos['fecha_creacion'] ?? ''),
            fechaFinalizacion: !empty($datos['fecha_finalizacion']) ? (string) $datos['fecha_finalizacion'] : null,
            sprintId: (int) ($datos['sprint_id'] ?? 0),
        );
    }

    public function aArray(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'responsable' => $this->responsable,
            'estado' => $this->estado,
            'puntos' => $this->puntos,
            'fecha_creacion' => $this->fechaCreacion,
            'fecha_finalizacion' => $this->fechaFinalizacion,
            'sprint_id' => $this->sprintId,
        ];
    }

    public function conId(int $id): self
    {
        return new self(
            id: $id,
            titulo: $this->titulo,
            descripcion: $this->descripcion,
            responsable: $this->responsable,
            estado: $this->estado,
            puntos: $this->puntos,
            fechaCreacion: $this->fechaCreacion,
            fechaFinalizacion: $this->fechaFinalizacion,
            sprintId: $this->sprintId,
        );
    }
}