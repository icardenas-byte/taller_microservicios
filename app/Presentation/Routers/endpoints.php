<?php
declare(strict_types=1);
namespace App\Presentation\Routers;

use App\Controllers\HistoriasController;
use App\Controllers\InformesController;
use App\Controllers\SprintsController;
use App\Shared\Http\Router;

final class Endpoints
{
    private const RUTA_SPRINTS = '/api/sprints';
    private const RUTA_HISTORIAS = '/api/historias';
    private const RUTA_INFORMES = '/api/informes';

    public function __construct(
        private readonly Router $router,
        private readonly SprintsController $sprintsController,
        private readonly HistoriasController $historiasController,
        private readonly InformesController $informesController,
    ) {
    }

    public function register(): void
    {
        $this->registrarRutasSprints();
        $this->registrarRutasHistorias();
        $this->registrarRutasInformes();
    }

    private function registrarRutasSprints(): void
    {
        $controlador = $this->sprintsController;
        $ruta = self::RUTA_SPRINTS;
        $this->router->get($ruta, [$controlador, 'index']);
        $this->router->get("{$ruta}/{id}", [$controlador, 'show']);
        $this->router->post($ruta, [$controlador, 'store']);
        $this->router->put("{$ruta}/{id}", [$controlador, 'update']);
        $this->router->delete("{$ruta}/{id}", [$controlador, 'destroy']);
    }

    private function registrarRutasHistorias(): void
    {
        $controlador = $this->historiasController;
        $ruta = self::RUTA_HISTORIAS;
        $this->router->get($ruta, [$controlador, 'index']);
        $this->router->get("{$ruta}/agrupadas", [$controlador, 'grouped']);
        $this->router->get("{$ruta}/{id}", [$controlador, 'show']);
        $this->router->post($ruta, [$controlador, 'store']);
        $this->router->put("{$ruta}/{id}", [$controlador, 'update']);
        $this->router->delete("{$ruta}/{id}", [$controlador, 'destroy']);
    }

    private function registrarRutasInformes(): void
    {
        $this->router->get(self::RUTA_INFORMES, [$this->informesController, 'index']);
    }
}
