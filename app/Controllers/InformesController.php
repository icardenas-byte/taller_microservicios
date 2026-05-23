<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Repositories\HistoriasRepository;
use App\Shared\Http\Request;
use App\Shared\Http\Response;

class InformesController
{
    public function __construct(
        private readonly HistoriasRepository $historiasRepository,
        private readonly Request $request
    ) {
    }

    public function index(): void
    {
        $sprintId = $this->obtenerSprintIdDesdeQuery();
        Response::json($this->historiasRepository->report($sprintId));
    }

    private function obtenerSprintIdDesdeQuery(): ?int
    {
        $sprintId = $this->request->query('sprint_id');
        if ($sprintId === null || $sprintId === '') {
            return null;
        }
        return (int) $sprintId;
    }
}
