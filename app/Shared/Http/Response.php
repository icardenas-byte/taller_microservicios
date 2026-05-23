<?php
declare(strict_types=1);
namespace App\Shared\Http;

final class Response
{
    private const TIPO_CONTENIDO_JSON = 'application/json; charset=utf-8';
    private const CODIFICACION_JSON = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: ' . self::TIPO_CONTENIDO_JSON);
        try {
            $json = json_encode($data, self::CODIFICACION_JSON);
        } catch (\JsonException $e) {
            http_response_code(500);
            $json = json_encode(['error' => 'Error al codificar la respuesta.'], JSON_UNESCAPED_UNICODE);
        }
        echo $json;
        exit;
    }

    public static function noContent(): never
    {
        http_response_code(204);
        exit;
    }

    public static function error(string $mensaje, int $status = 500): never
    {
        self::json(['error' => $mensaje], $status);
    }
}