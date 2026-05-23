<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Repositories\HistoriasRepository;
use App\Models\Repositories\SprintsRepository;
use App\Shared\Http\Request;
use App\Shared\Http\Response;

class HistoriasController
{
    private const ESTADOS_PERMITIDOS = ['nueva', 'activa', 'finalizada', 'impedimento'];
    public function __construct(
        private readonly HistoriasRepository $historiasRepository,
        private readonly SprintsRepository $sprintsRepository,
        private readonly Request $request
    ) {
    }

    
    public function index(): void
    {
        Response::json($this->historiasRepository->getAll());
    }

    public function grouped(): void
    {
        Response::json($this->historiasRepository->getBySprint());
    }

    public function show(int $id): void
    {
        $historia = $this->historiasRepository->getById($id);

        if ($historia === null) {
            Response::json(['error' => 'Historia no encontrada.'], 404);
            return;
        }

        Response::json($historia);
    }

    public function store(): void
    {
        $data = $this->request->json();

        $errores = $this->validarDatos($data);
        if (!empty($errores)) {
            Response::json(['errors' => $errores], 422);
            return;
        }

        $historia = $this->historiasRepository->create($this->sanitizarDatos($data));
        Response::json($historia, 201);
    }

    public function update(int $id): void
    {
        if (!$this->existeHistoria($id)) {
            Response::json(['error' => 'Historia no encontrada.'], 404);
            return;
        }

        $data = $this->request->json();

        $errores = $this->validarDatos($data);
        if (!empty($errores)) {
            Response::json(['errors' => $errores], 422);
            return;
        }

        $historia = $this->historiasRepository->update($id, $this->sanitizarDatos($data));
        Response::json($historia);
    }

    public function destroy(int $id): void
    {
        if (!$this->existeHistoria($id)) {
            Response::json(['error' => 'Historia no encontrada.'], 404);
            return;
        }

        $eliminado = $this->historiasRepository->delete($id);
        Response::json(['eliminado' => $eliminado]);
    }

    private function existeHistoria(int $id): bool
    {
        return $this->historiasRepository->getById($id) !== null;
    }

    /**
     * @return array<string, string>
     */
    private function validarDatos(array $data): array
    {
        $errores = [];

        $camposObligatorios = ['titulo', 'descripcion', 'responsable', 'estado', 'puntos', 'fecha_creacion', 'sprint_id'];
        foreach ($camposObligatorios as $campo) {
            if (!isset($data[$campo]) || $this->estaVacio($data[$campo])) {
                $errores[$campo] = "El campo {$campo} es obligatorio.";
            }
        }

        if (isset($data['estado']) && !in_array($data['estado'], self::ESTADOS_PERMITIDOS, true)) {
            $errores['estado'] = 'Estado no válido.';
        }

        if (isset($data['puntos']) && (int) $data['puntos'] <= 0) {
            $errores['puntos'] = 'Los puntos deben ser mayores que cero.';
        }

        if (isset($data['sprint_id']) && !$this->sprintsRepository->getById((int) $data['sprint_id'])) {
            $errores['sprint_id'] = 'El sprint seleccionado no existe.';
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
            'titulo' => trim((string) ($data['titulo'] ?? '')),
            'descripcion' => trim((string) ($data['descripcion'] ?? '')),
            'responsable' => trim((string) ($data['responsable'] ?? '')),
            'estado' => $data['estado'],
            'puntos' => (int) $data['puntos'],
            'fecha_creacion' => $data['fecha_creacion'],
            'fecha_finalizacion' => !empty($data['fecha_finalizacion']) ? $data['fecha_finalizacion'] : null,
            'sprint_id' => (int) $data['sprint_id'],
        ];
    }
}