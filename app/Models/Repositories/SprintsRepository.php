<?php
namespace App\Models\Repositories;
use PDO;
use PDOStatement;

class SprintsRepository
{
    private const COLUMNAS = ['nombre', 'fecha_inicio', 'fecha_fin'];

    public function __construct(private readonly PDO $db)
    {
    }

    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT * FROM sprints
            ORDER BY fecha_inicio DESC, id DESC
        SQL;
        return $this->ejecutarConsulta($sql)->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM sprints WHERE id = ?');
        $stmt->execute([$id]);
        $sprint = $stmt->fetch();
        return $sprint === false ? null : $sprint;
    }

    public function create(array $data): array
    {
        $sql = <<<'SQL'
            INSERT INTO sprints (nombre, fecha_inicio, fecha_fin)
            VALUES (?, ?, ?)
        SQL;
        $this->ejecutarConsulta($sql, $this->extraerValores($data));
        return $this->getById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $sql = <<<'SQL'
            UPDATE sprints
            SET nombre = ?, fecha_inicio = ?, fecha_fin = ?
            WHERE id = ?
        SQL;
        $valores = $this->extraerValores($data);
        $valores[] = $id;
        $this->ejecutarConsulta($sql, $valores);
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM sprints WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
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
        foreach (self::COLUMNAS as $columna) {
            $valores[] = $data[$columna] ?? null;
        }
        return $valores;
    }
}