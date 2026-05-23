<?php
declare(strict_types=1);
namespace App\Shared\Http;

final class Request
{
    private const METODO_POR_DEFECTO = 'GET';
    private const RUTA_POR_DEFECTO = '/';

    public function method(): string
    {
        return $this->obtenerDeServidor('REQUEST_METHOD', self::METODO_POR_DEFECTO);
    }

    public function path(): string
    {
        $uri = $this->obtenerDeServidor('REQUEST_URI', self::RUTA_POR_DEFECTO);
        $ruta = parse_url($uri, PHP_URL_PATH);
        if (!is_string($ruta)) {
            return self::RUTA_POR_DEFECTO;
        }
        return rtrim($ruta, '/') ?: self::RUTA_POR_DEFECTO;
    }

    public function json(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $this->estaVacio($raw)) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function query(string $clave, mixed $porDefecto = null): mixed
    {
        return $_GET[$clave] ?? $porDefecto;
    }

    private function obtenerDeServidor(string $clave, string $porDefecto): string
    {
        $valor = $_SERVER[$clave] ?? $porDefecto;
        return is_string($valor) ? $valor : $porDefecto;
    }

    private function estaVacio(mixed $valor): bool
    {
        return $valor === '' || $valor === null || $valor === [];
    }
}