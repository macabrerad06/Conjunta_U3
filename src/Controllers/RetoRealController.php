<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\RetoReal;
use App\Repositories\RetoRealRepository;
use App\config\Database; // Necesario para la conexión a la base de datos para la tabla pivote
use PDO;
use Exception;

class RetoRealController
{
    private RetoRealRepository $retoRealRepository;
    private PDO $connection; // Para consultas que involucren JOINs con otras tablas

    public function __construct()
    {
        $this->retoRealRepository = new RetoRealRepository();
        $this->connection = Database::getConnection(); // Obtenemos la conexión a la DB
    }

    /**
     * Maneja todas las solicitudes HTTP para el recurso de RetoReal.
     * Implementa un enrutamiento interno basado en el método HTTP y parámetros.
     */
    public function handle(): void
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        // Para POST, PUT, DELETE, el payload se lee del cuerpo de la solicitud
        $payload = json_decode(file_get_contents('php://input'), true);

        // -- Solicitudes GET --
        if ($method === 'GET') {
            if (isset($_GET['action'])) {
                // Específicamente para /retos/{id}/equipos (simulado con ?action=get_equipos_by_reto&id={id})
                if ($_GET['action'] === 'get_equipos_by_reto' && isset($_GET['id'])) {
                    $retoId = (int)$_GET['id'];
                    $this->getEquiposByRetoId($retoId);
                    return;
                }
            } elseif (isset($_GET['id'])) {
                // Obtener un reto real por ID: GET /retos?id={id}
                $reto = $this->retoRealRepository->findById((int)$_GET['id']);
                echo json_encode($reto ? $this->retoRealToArray($reto) : null);
                return;
            } else {
                // Listar todos los retos reales: GET /retos
                $list = array_map(
                    [$this, 'retoRealToArray'],
                    $this->retoRealRepository->findAll()
                );
                echo json_encode($list);
            }
            return;
        }

        // -- Solicitudes POST --
        if ($method === 'POST') {
            try {
                // Validar que el payload no sea nulo y contenga los campos esperados
                if (json_last_error() !== JSON_ERROR_NONE ||
                    !isset($payload['tipo'], $payload['titulo'], $payload['descripcion'],
                           $payload['dificultad'], $payload['areasConocimiento'], $payload['entidadColaboradora'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o incompletos para crear un reto real.']);
                    return;
                }

                if ($payload['tipo'] !== 'retoReal') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Este controlador solo maneja el tipo "retoReal" para la creación.']);
                    return;
                }

                $retoReal = new RetoReal(
                    0, // ID placeholder, será asignado por la DB si es AUTO_INCREMENT
                    $payload['tipo'],
                    $payload['titulo'],
                    $payload['descripcion'],
                    $payload['dificultad'],
                    $payload['areasConocimiento'],
                    $payload['entidadColaboradora']
                );

                echo json_encode(['success' => $this->retoRealRepository->create($retoReal)]);
            } catch (Exception $e) {
                http_response_code(400); // 400 Bad Request por problemas de datos o 500 para otros errores
                echo json_encode(['error' => 'Error al crear reto real: ' . $e->getMessage()]);
            }
            return;
        }

        // -- Solicitudes PUT --
        if ($method === 'PUT') {
            try {
                // Validar que el payload no sea nulo y contenga el ID
                if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o ID no proporcionado para la actualización.']);
                    return;
                }
                
                $id = (int)$payload['id'];
                if ($id === 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de reto real no válido para la actualización.']);
                    return;
                }

                $existing = $this->retoRealRepository->findById($id);

                if (!$existing) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Reto real no encontrado para actualizar.']);
                    return;
                }

                // Actualizar solo los campos proporcionados en el payload
                if (isset($payload['titulo'])) $existing->setTitulo($payload['titulo']);
                if (isset($payload['descripcion'])) $existing->setDescripcion($payload['descripcion']);
                if (isset($payload['dificultad'])) $existing->setDificultad($payload['dificultad']);
                if (isset($payload['areasConocimiento'])) $existing->setAreasConocimiento($payload['areasConocimiento']);
                if (isset($payload['entidadColaboradora'])) $existing->setEntidadColaboradora($payload['entidadColaboradora']);

                echo json_encode(['success' => $this->retoRealRepository->update($existing)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al actualizar reto real: ' . $e->getMessage()]);
            }
            return;
        }

        // -- Solicitudes DELETE --
        if ($method === 'DELETE') {
            try {
                 // Validar que el payload no sea nulo y contenga el ID
                if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o ID no proporcionado para la eliminación.']);
                    return;
                }

                $id = (int)$payload['id'];
                if ($id === 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de reto real no válido para la eliminación.']);
                    return;
                }
                echo json_encode(['success' => $this->retoRealRepository->delete($id)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al eliminar reto real: ' . $e->getMessage()]);
            }
            return;
        }

        // Si el método HTTP no es manejado
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Método no permitido.']);
    }

    /**
     * Convierte un objeto RetoReal a un array asociativo para la respuesta JSON.
     * @param RetoReal $retoReal
     * @return array
     */
    public function retoRealToArray(RetoReal $retoReal): array
    {
        return [
            'retoId' => $retoReal->getRetoId(),
            'tipo' => $retoReal->getTipo(),
            'titulo' => $retoReal->getTitulo(),
            'descripcion' => $retoReal->getDescripcion(),
            'dificultad' => $retoReal->getDificultad(),
            'areasConocimiento' => $retoReal->getAreasConocimiento(),
            'entidadColaboradora' => $retoReal->getEntidadColaboradora()
        ];
    }

    /**
     * Consulta los equipos que trabajan en un reto real específico.
     * Este método es llamado internamente desde handle() para la acción GET.
     * Endpoint conceptual: GET /retos?action=get_equipos_by_reto&id={retoId}
     * @param int $retoId
     */
    private function getEquiposByRetoId(int $retoId): void
    {
        try {
            // Primero, verificar si el reto existe
            $reto = $this->retoRealRepository->findById($retoId);
            if (!$reto) {
                http_response_code(404);
                echo json_encode(['message' => 'Reto Real no encontrado.']);
                return;
            }

            // Realizar JOIN para obtener los equipos asignados a este reto
            $sql = "SELECT e.idEquipo AS id, e.nombre, COUNT(DISTINCT p.id) AS miembros
                    FROM equipo_reto er
                    JOIN equipos e ON er.equipo_id = e.idEquipo
                    LEFT JOIN participantes p ON JSON_CONTAINS(e.participante_ids, CONCAT('\"', p.id, '\"'))
                    WHERE er.reto_id = :retoId
                    GROUP BY e.idEquipo, e.nombre";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':retoId', $retoId, PDO::PARAM_INT);
            $stmt->execute();
            $equiposAsignados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'retoId' => $retoId,
                'titulo' => $reto->getTitulo(),
                'equiposAsignados' => $equiposAsignados
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
}
