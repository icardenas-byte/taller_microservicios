<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Repositories\SprintsRepository;
use App\Shared\Http\Request;
use App\Shared\Http\Response;

class SprintsController
{
    private const CAMPOS_OBLIGATORIOS = ['nombre', 'fecha_inicio', 'fecha_fin'];
    public function __construct(
        private readonly SprintsRepository $sprintsRepository,
        private readonly Request $request
    ) {
    }

    public function index(): void
    {
        Response::json($this->sprintsRepository->getAll());
    }

    public function show(int $id): void
    {
        $sprint = $this->sprintsRepository->getById($id);
        if ($sprint === null) {
            Response::json(['error' => 'Sprint no encontrado.'], 404);
            return;
        }

        Response::json($sprint);
    }

    public function store(): void
    {
        $data = $this->request->json();
        $errores = $this->validarDatos($data);
        if (!empty($errores)) {
            Response::json(['errors' => $errores], 422);
            return;
        }
        $sprint = $this->sprintsRepository->create($this->sanitizarDatos($data));
        Response::json($sprint, 201);
    }

    public function update(int $id): void
    {
        if (!$this->existeSprint($id)) {
            Response::json(['error' => 'Sprint no encontrado.'], 404);
            return;
        }
        $data = $this->request->json();
        $errores = $this->validarDatos($data);
        if (!empty($errores)) {
            Response::json(['errors' => $errores], 422);
            return;
        }
        $sprint = $this->sprintsRepository->update($id, $this->sanitizarDatos($data));
        Response::json($sprint);
    }

    public function destroy(int $id): void
    {
        if (!$this->existeSprint($id)) {
            Response::json(['error' => 'Sprint no encontrado.'], 404);
            return;
        }
        $eliminado = $this->sprintsRepository->delete($id);
        Response::json(['eliminado' => $eliminado]);
    }

    private function existeSprint(int $id): bool
    {
        return $this->sprintsRepository->getById($id) !== null;
    }

    /**
     * @return array<string, string>
     */
    private function validarDatos(array $data): array
    {
        $errores = [];
        foreach (self::CAMPOS_OBLIGATORIOS as $campo) {
            if (!isset($data[$campo]) || $this->estaVacio($data[$campo])) {
                $errores[$campo] = "El campo {$campo} es obligatorio.";
            }
        }
        if (isset($data['fecha_inicio'], $data['fecha_fin']) && $data['fecha_inicio'] > $data['fecha_fin']) {
            $errores['fecha_inicio'] = 'La fecha de inicio no puede ser mayor que la fecha de fin.';
        }
        return $errores;
    }

    private function estaVacio(mixed $valor): bool
    {
        return $valor === '' || $valor === null || $valor === [];
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitizarDatos(array $data): array
    {
        return [
            'nombre' => trim((string) ($data['nombre'] ?? '')),
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
        ];
    }
}