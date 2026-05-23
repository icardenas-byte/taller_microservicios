<?php
declare(strict_types=1);

use App\Config\Database;
use App\Controllers\HistoriasController;
use App\Controllers\InformesController;
use App\Controllers\SprintsController;
use App\Presentation\Middlewares\CorsMiddleware;
use App\Models\Repositories\HistoriasRepository;
use App\Models\Repositories\SprintsRepository;
use App\Presentation\Routers\Endpoints;
use App\Shared\Http\Request;
use App\Shared\Http\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$request = new Request();
$servidorAssets = new ServidorAssets($request);
if ($servidorAssets->intentarServir()) {
    exit;
}

final class ServidorAssets
{
    private const PARAMETRO_ASSET = 'asset';
    private const ASSETS_DISPONIBLES = [
        'css' => [
            'ruta' => __DIR__ . '/../app/Views/css/base.css',
            'tipo' => 'text/css; charset=utf-8',
        ],
        'js' => [
            'ruta' => __DIR__ . '/../app/Views/js/historias.js',
            'tipo' => 'application/javascript; charset=utf-8',
        ],
    ];

    public function __construct(private readonly Request $request)
    {
    }

    public function intentarServir(): bool
    {
        $asset = $this->request->query(self::PARAMETRO_ASSET);
        if ($asset === null) {
            return false;
        }
        if (!isset(self::ASSETS_DISPONIBLES[$asset])) {
            return false;
        }
        $this->servirAsset(self::ASSETS_DISPONIBLES[$asset]);
        return true;
    }

    /**
     * @param array{ruta: string, tipo: string} $configuracion
     */
    private function servirAsset(array $configuracion): void
    {
        header('Content-Type: ' . $configuracion['tipo']);
        readfile($configuracion['ruta']);
    }
}