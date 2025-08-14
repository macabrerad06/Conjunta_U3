<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Equipos;
use App\Repositories\EquiposRepository;
use App\config\Database; // Necesario para interactuar directamente con la DB para la tabla pivote
use PDO;
use Exception;

class EquiposController
{
    private EquiposRepository $equiposRepository;
    private PDO $connection; // Para la tabla pivote equipo_reto

    public function __construct()
    {
        $this->equiposRepository = new EquiposRepository();
        $this->connection = Database::getConnection(); // Obtenemos la conexión a la DB
    }

    /**
     * Maneja todas las solicitudes HTTP para el recurso de Equipos.
     * Implementa un enrutamiento interno basado en el método HTTP y parámetros.
     */
    public function handle(): void
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        $payload = json_decode(file_get_contents('php://input'), true);

        // -- GET requests --
        if ($method === 'GET') {
            if (isset($_GET['action'])) {
                // Específicamente para /equipos/{id}/retos (simulado con ?action=get_retos_by_equipo&id={id})
                if ($_GET['action'] === 'get_retos_by_equipo' && isset($_GET['id'])) {
                    $equipoId = (int)$_GET['id'];
                    $this->getRetosByEquipoId($equipoId);
                    return;
                }
            } elseif (isset($_GET['id'])) {
                // Para /equipos/{id}
                $equipo = $this->equiposRepository->findById((int)$_GET['id']);
                echo json_encode($equipo ? $this->equipoToArray($equipo) : null);
                return;
            } else {
                // Para /equipos (listar todos)
                $list = array_map(
                    [$this, 'equipoToArray'],
                    $this->equiposRepository->findAll()
                );
                echo json_encode($list);
            }
            return;
        }

        // -- POST requests --
        if ($method === 'POST') {
            try {
                // Validar que el payload no sea nulo y contenga los campos esperados
                if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['nombre'], $payload['hackathonId'], $payload['participanteIds'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid or incomplete data provided.']);
                    return;
                }

                $equipo = new Equipos(
                    0, // ID placeholder, será asignado por la DB si es AUTO_INCREMENT
                    $payload['nombre'],
                    $payload['hackathonId'],
                    $payload['participanteIds']
                );
                echo json_encode(['success' => $this->equiposRepository->create($equipo)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error creating team: ' . $e->getMessage()]);
            }
            return;
        }

        // -- PUT requests --
        if ($method === 'PUT') {
            try {
                // Validar que el payload no sea nulo
                if (json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid JSON input.']);
                    return;
                }

                if (isset($_GET['action'])) {
                    // Específicamente para /equipos/{id}/asignar-reto (simulado con ?action=assign_reto)
                    if ($_GET['action'] === 'assign_reto') {
                        $equipoId = (int)($payload['id'] ?? 0); // Equipo ID del payload
                        $retoIds = $payload['retoIds'] ?? [];   // Reto IDs del payload
                        $this->assignRetoToEquipo($equipoId, $retoIds);
                        return;
                    }
                }

                // Para /equipos/{id} (actualizar equipo)
                $id = (int)($payload['id'] ?? 0);
                if ($id === 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID not provided for update.']);
                    return;
                }

                $existing = $this->equiposRepository->findById($id);

                if (!$existing) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Team not found.']);
                    return;
                }

                if (isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
                if (isset($payload['hackathonId'])) $existing->setHackathon($payload['hackathonId']);
                // Recuerda que 'participantes' es un array que se serializa/deserializa
                if (isset($payload['participanteIds']) && is_array($payload['participanteIds'])) {
                    $existing->setParticipantes($payload['participanteIds']);
                }

                echo json_encode(['success' => $this->equiposRepository->update($existing)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error updating team: ' . $e->getMessage()]);
            }
            return;
        }

        // -- DELETE requests --
        if ($method === 'DELETE') {
            try {
                // Validar que el payload no sea nulo
                if (json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid JSON input.']);
                    return;
                }
                
                $id = (int)($payload['id'] ?? 0);
                if ($id === 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID not provided for deletion.']);
                    return;
                }
                echo json_encode(['success' => $this->equiposRepository->delete($id)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error deleting team: ' . $e->getMessage()]);
            }
            return;
        }

        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method not allowed.']);
    }

    /**
     * Convierte un objeto Equipo a un array asociativo.
     * @param Equipos $equipo
     * @return array
     */
    public function equipoToArray(Equipos $equipo): array
    {
        return [
            'idEquipo' => $equipo->getIdEquipo(),
            'nombre' => $equipo->getNombre(),
            'hackathon' => $equipo->getHackathon(),
            'participantes' => $equipo->getParticipantes() // Ya debería ser un array deserializado
        ];
    }

    /**
     * Asigna uno o más retos a un equipo.
     * Este método es llamado internamente desde handle() para la acción PUT.
     * Endpoint conceptual: PUT /equipos?action=assign_reto (con id y retoIds en el payload)
     * @param int $equipoId
     * @param array $retoIds
     */
    private function assignRetoToEquipo(int $equipoId, array $retoIds): void
    {
        try {
            // Verificar si el equipo existe
            $equipo = $this->equiposRepository->findById($equipoId);
            if (!$equipo) {
                http_response_code(404);
                echo json_encode(['message' => 'Equipo no encontrado.']);
                return;
            }

            $this->connection->beginTransaction(); // Iniciar transacción

            $success = true;
            foreach ($retoIds as $retoId) {
                // Verificar si la asignación ya existe para evitar duplicados
                $checkSql = "SELECT COUNT(*) FROM equipo_reto WHERE equipo_id = :equipoId AND reto_id = :retoId";
                $checkStmt = $this->connection->prepare($checkSql);
                $checkStmt->execute([':equipoId' => $equipoId, ':retoId' => $retoId]);
                if ($checkStmt->fetchColumn() > 0) {
                    continue; // La asignación ya existe, saltar
                }

                // Insertar en la tabla pivote equipo_reto
                $sql = "INSERT INTO equipo_reto (equipo_id, reto_id, estado) VALUES (:equipoId, :retoId, :estado)";
                $stmt = $this->connection->prepare($sql);
                if (!$stmt->execute([
                    ':equipoId' => $equipoId,
                    ':retoId' => $retoId,
                    ':estado' => 'en_progreso' // Estado inicial
                ])) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                $this->connection->commit();
                echo json_encode(['success' => true, 'message' => 'Retos asignados exitosamente al equipo.']);
            } else {
                $this->connection->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al asignar retos al equipo.']);
            }

        } catch (Exception $e) {
            $this->connection->rollBack(); // Revertir en caso de error
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Consulta los retos asignados a un equipo específico.
     * Este método es llamado internamente desde handle() para la acción GET.
     * Endpoint conceptual: GET /equipos?action=get_retos_by_equipo&id={equipoId}
     * @param int $equipoId
     */
    private function getRetosByEquipoId(int $equipoId): void
    {
        try {
            // Verificar si el equipo existe
            $equipo = $this->equiposRepository->findById($equipoId);
            if (!$equipo) {
                http_response_code(404);
                echo json_encode(['message' => 'Equipo no encontrado.']);
                return;
            }

            // Realizar JOIN para obtener los retos asignados
            $sql = "SELECT r.id, r.titulo, r.tipo, r.descripcion, r.dificultad, er.estado
                    FROM equipo_reto er
                    JOIN retos r ON er.reto_id = r.id
                    WHERE er.equipo_id = :equipoId";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':equipoId', $equipoId, PDO::PARAM_INT);
            $stmt->execute();
            $retosAsignados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'equipoId' => $equipoId,
                'nombre' => $equipo->getNombre(),
                'retosAsignados' => $retosAsignados
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
}
