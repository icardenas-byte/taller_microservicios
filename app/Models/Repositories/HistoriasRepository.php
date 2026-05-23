<?php
declare(strict_types=1);
namespace App\Models\Repositories;
use PDO;
use PDOStatement;
class HistoriasRepository
{
    private const ESTADOS = ['nueva', 'activa', 'finalizada', 'impedimento'];
    private const COLUMNAS_HISTORIA = [
        'titulo',
        'descripcion',
        'responsable',
        'estado',
        'puntos',
        'fecha_creacion',
        'fecha_finalizacion',
        'sprint_id',
    ];
    public function __construct(private readonly PDO $db)
    {
    }

    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT h.*, s.nombre AS sprint_nombre
            FROM historias h
            INNER JOIN sprints s ON s.id = h.sprint_id
            ORDER BY s.fecha_inicio DESC, h.id DESC
        SQL;
        return $this->ejecutarConsulta($sql)->fetchAll();
    }

    public function getBySprint(): array
    {
        $sprints = $this->ejecutarConsulta(
            'SELECT * FROM sprints ORDER BY fecha_inicio DESC, id DESC'
        )->fetchAll();

        $stmt = $this->db->prepare(
            'SELECT * FROM historias WHERE sprint_id = ? ORDER BY id DESC'
        );
        foreach ($sprints as &$sprint) {
            $stmt->execute([$sprint['id']]);
            $sprint['historias'] = $stmt->fetchAll();
        }
        return $sprints;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM historias WHERE id = ?');
        $stmt->execute([$id]);
        $historia = $stmt->fetch();
        return $historia === false ? null : $historia;
    }

    public function create(array $data): array
    {
        $sql = <<<'SQL'
            INSERT INTO historias (titulo, descripcion, responsable, estado, puntos, fecha_creacion, fecha_finalizacion, sprint_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->extraerValores($data));
        return $this->getById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $sql = <<<'SQL'
            UPDATE historias
            SET titulo = ?, descripcion = ?, responsable = ?, estado = ?, puntos = ?,
                fecha_creacion = ?, fecha_finalizacion = ?, sprint_id = ?
            WHERE id = ?
        SQL;
        $valores = $this->extraerValores($data);
        $valores[] = $id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($valores);
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM historias WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function report(?int $sprintId = null): array
    {
        $condicion = $sprintId !== null ? 'WHERE h.sprint_id = ?' : '';
        $parametros = $sprintId !== null ? [$sprintId] : [];
        return [
            'general' => $this->obtenerResumenGeneral($condicion, $parametros),
            'responsables' => $this->obtenerResumenPorResponsable($condicion, $parametros),
        ];
    }

    private function obtenerResumenGeneral(string $condicion, array $parametros): array
    {
        $sql = <<<SQL
            SELECT h.estado, COUNT(*) AS total, COALESCE(SUM(h.puntos), 0) AS puntos
            FROM historias h {$condicion}
            GROUP BY h.estado
        SQL;
        $filas = $this->ejecutarConsulta($sql, $parametros)->fetchAll();
        return $this->normalizarResumenGeneral($filas);
    }

    private function obtenerResumenPorResponsable(string $condicion, array $parametros): array
    {
        $sql = <<<SQL
            SELECT h.responsable, h.estado, COUNT(*) AS total, COALESCE(SUM(h.puntos), 0) AS puntos
            FROM historias h {$condicion}
            GROUP BY h.responsable, h.estado
            ORDER BY h.responsable ASC
        SQL;
        $filas = $this->ejecutarConsulta($sql, $parametros)->fetchAll();
        return $this->normalizarResumenPorResponsable($filas);
    }

    private function ejecutarConsulta(string $sql, array $parametros = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    }

    /**
     * @return array<int, mixed>
     */
    private function extraerValores(array $data): array
    {
        $valores = [];
        foreach (self::COLUMNAS_HISTORIA as $columna) {
            $valores[] = $data[$columna] ?? null;
        }

        return $valores;
    }

    /**
     * @return array<string, array<string, int>>
     */
    private function normalizarResumenGeneral(array $filas): array
    {
        $resultado = array_fill_keys(self::ESTADOS, ['historias' => 0, 'puntos' => 0]);
        foreach ($filas as $fila) {
            $estado = (string) $fila['estado'];
            if (!isset($resultado[$estado])) {
                continue;
            }
            $resultado[$estado] = [
                'historias' => (int) $fila['total'],
                'puntos' => (int) $fila['puntos'],
            ];
        }

        return $resultado;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizarResumenPorResponsable(array $filas): array
    {
        $resultado = [];
        foreach ($filas as $fila) {
            $responsable = (string) $fila['responsable'];
            if (!isset($resultado[$responsable])) {
                $resultado[$responsable] = $this->inicializarRegistroResponsable($responsable);
            }
            $estado = (string) $fila['estado'];
            if (isset($resultado[$responsable][$estado])) {
                $resultado[$responsable][$estado] = (int) $fila['total'];
            }
            $resultado[$responsable]['puntos'] += (int) $fila['puntos'];
        }

        return array_values($resultado);
    }

    /**
     * @return array<string, mixed>
     */
    private function inicializarRegistroResponsable(string $responsable): array
    {
        $registro = [
            'responsable' => $responsable,
            'puntos' => 0,
        ];
        foreach (self::ESTADOS as $estado) {
            $registro[$estado] = 0;
        }
        return $registro;
    }
}