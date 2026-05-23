<?php
declare(strict_types=1);
namespace App\Shared\Http;

final class Router
{
    private const EXPRESION_PARAMETRO = '#\{[a-zA-Z_][a-zA-Z0-9_]*\}#';
    private const EXPRESION_NUMERICA = '([0-9]+)';
    /** @var array<int, array<string, mixed>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();
        foreach ($this->routes as $route) {
            if (!$this->coincideMetodo($route, $method)) {
                continue;
            }
            if (!$this->coincideRuta($route, $path, $matches)) {
                continue;
            }
            $this->ejecutarHandler($route['handler'], $matches);
            return;
        }
        Response::json(['error' => 'Ruta no encontrada.'], 404);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $pattern = $this->construirPatron($path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    private function construirPatron(string $path): string
    {
        $escaped = preg_replace(self::EXPRESION_PARAMETRO, self::EXPRESION_NUMERICA, $path);
        return '#^' . $escaped . '$#';
    }

    /**
     * @param array<string, mixed> $route
     */
    private function coincideMetodo(array $route, string $method): bool
    {
        return $route['method'] === $method;
    }

    /**
     * @param array<string, mixed> $route
     * @param array<int, string> $matches
     */
    private function coincideRuta(array $route, string $path, array &$matches): bool
    {
        return preg_match($route['pattern'], $path, $matches) === 1;
    }

    /**
     * @param array<int, string> $matches
     */
    private function ejecutarHandler(callable $handler, array $matches): void
    {
        array_shift($matches);
        $params = array_map('intval', $matches);
        call_user_func_array($handler, $params);
    }
}