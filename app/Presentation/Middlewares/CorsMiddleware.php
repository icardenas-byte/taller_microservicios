<?php
declare(strict_types=1);
namespace App\Presentation\Middlewares;
use App\Shared\Http\Response;

final class CorsMiddleware
{
    private const ORIGEN_PERMITIDO = '*';
    private const METODOS_PERMITIDOS = 'GET, POST, PUT, DELETE, OPTIONS';
    private const CABECERAS_PERMITIDAS = 'Content-Type, Authorization';

    public static function handle(string $method): void
    {
        self::establecerCabeceras();
        if ($method === 'OPTIONS') {
            self::responderPreflight();
        }
    }

    private static function establecerCabeceras(): void
    {
        header('Access-Control-Allow-Origin: ' . self::ORIGEN_PERMITIDO);
        header('Access-Control-Allow-Methods: ' . self::METODOS_PERMITIDOS);
        header('Access-Control-Allow-Headers: ' . self::CABECERAS_PERMITIDAS);
    }

    private static function responderPreflight(): void
    {
        Response::json(['ok' => true]);
        exit;
    }
}
