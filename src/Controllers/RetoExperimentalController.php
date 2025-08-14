<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\RetoExperimental;
use App\Repositories\RetoExperimentalRepository;
use App\config\Database; // Necesario para interactuar con tablas relacionadas (ej. equipo_reto)
use PDO;
use Exception;

class RetoExperimentalController
{
    private RetoExperimentalRepository $retoExperimentalRepository;
    private PDO $connection; // Para consultas que involucren JOINs con otras tablas

    public function __construct()
    {
        $this->retoExperimentalRepository = new RetoExperimentalRepository();
        $this->connection = Database::getConnection(); // Obtenemos la conexión a la DB
    }

    /**
     * Maneja las solicitudes HTTP entrantes y las dirige al método apropiado.
     * Simula un enrutador básico para la API.
     */
    public function handleRequest(string $method, string $path, array $params = []): void
    {
        switch ($method) {
            case 'POST':
                if ($path === '/retos') {
                    $this->createReto();
                }
                break;
            case 'GET':
                if ($path === '/retos') {
                    $this->getRetos();
                } elseif (preg_match('/^\/retos\/(\d+)$/', $path, $matches)) {
                    $retoId = (int) $matches[1];
                    $this->getRetoById($retoId);
                } elseif (preg_match('/^\/retos\/(\d+)\/equipos$/', $path, $matches)) {
                    $retoId = (int) $matches[1];
                    $this->getEquiposByRetoId($retoId);
                }
                break;
            case 'PUT':
                if (preg_match('/^\/retos\/(\d+)$/', $path, $matches)) {
                    $retoId = (int) $matches[1];
                    $this->updateReto($retoId);
                }
                break;
            case 'DELETE':
                if (preg_match('/^\/retos\/(\d+)$/', $path, $matches)) {
                    $retoId = (int) $matches[1];
                    $this->deleteReto($retoId);
                }
                break;
            default:
                $this->sendResponse(405, ['message' => 'Method Not Allowed']);
                break;
        }
    }

    /**
     * Crea un nuevo reto experimental.
     * Endpoint: POST /retos
     * Body: { "tipo": "retoExperimental", "titulo": "...", "descripcion": "...", "dificultad": "...", "areasConocimiento": [...], "enfoquePedagogico": "..." }
     */
    public function createReto(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        // Validación básica de campos requeridos para RetoExperimental
        $requiredFields = ['tipo', 'titulo', 'descripcion', 'dificultad', 'areasConocimiento', 'enfoquePedagogico'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->sendResponse(400, ['message' => "Missing required field: {$field}"]);
                return;
            }
        }

        if ($input['tipo'] !== 'retoExperimental') {
            $this->sendResponse(400, ['message' => 'This controller only handles "retoExperimental" type.']);
            return;
        }

        try {
            // Se asume que el ID (retoId) es AUTO_INCREMENT en la DB y no se pasa al constructor inicialmente.
            $reto = new RetoExperimental(
                0, // ID placeholder, será asignado por la DB
                $input['tipo'],
                $input['titulo'],
                $input['descripcion'],
                $input['dificultad'],
                $input['areasConocimiento'],
                $input['enfoquePedagogico']
            );

            if ($this->retoExperimentalRepository->create($reto)) {
                $this->sendResponse(201, ['message' => 'Reto Experimental creado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al crear el Reto Experimental.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene un reto experimental por su ID.
     * Endpoint: GET /retos/{id}
     */
    public function getRetoById(int $id): void
    {
        try {
            $reto = $this->retoExperimentalRepository->findById($id);
            if ($reto) {
                $this->sendResponse(200, $reto);
            } else {
                $this->sendResponse(404, ['message' => 'Reto Experimental no encontrado.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza un reto experimental existente.
     * Endpoint: PUT /retos/{id}
     * Body: { "titulo": "...", "descripcion": "...", "dificultad": "...", "areasConocimiento": [...], "enfoquePedagogico": "..." }
     */
    public function updateReto(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        try {
            // Recuperar el reto existente para actualizar sus propiedades
            $reto = $this->retoExperimentalRepository->findById($id);
            if (!$reto) {
                $this->sendResponse(404, ['message' => 'Reto Experimental no encontrado para actualizar.']);
                return;
            }

            // Actualizar solo los campos proporcionados en el input
            if (isset($input['titulo'])) {
                $reto->setTitulo($input['titulo']);
            }
            if (isset($input['descripcion'])) {
                $reto->setDescripcion($input['descripcion']);
            }
            if (isset($input['dificultad'])) {
                $reto->setDificultad($input['dificultad']);
            }
            if (isset($input['areasConocimiento'])) {
                $reto->setAreasConocimiento($input['areasConocimiento']);
            }
            if (isset($input['enfoquePedagogico'])) {
                $reto->setEnfoquePedagogico($input['enfoquePedagogico']);
            }

            if ($this->retoExperimentalRepository->update($reto)) {
                $this->sendResponse(200, ['message' => 'Reto Experimental actualizado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al actualizar el Reto Experimental.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina un reto experimental.
     * Endpoint: DELETE /retos/{id}
     */
    public function deleteReto(int $id): void
    {
        try {
            if ($this->retoExperimentalRepository->delete($id)) {
                $this->sendResponse(200, ['message' => 'Reto Experimental eliminado exitosamente.']);
            } else {
                $this->sendResponse(404, ['message' => 'Reto Experimental no encontrado o error al eliminar.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene todos los retos (experimentales).
     * Endpoint: GET /retos
     * Nota: Este método listaría solo los retos experimentales si el repositorio solo maneja ese tipo.
     */
    public function getRetos(): void
    {
        try {
            $retos = $this->retoExperimentalRepository->findAll();
            $this->sendResponse(200, $retos);
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Consulta los equipos que trabajan en un reto específico.
     * Endpoint: GET /retos/{id}/equipos
     */
    public function getEquiposByRetoId(int $retoId): void
    {
        try {
            // Primero, verificar si el reto existe
            $reto = $this->retoExperimentalRepository->findById($retoId);
            if (!$reto) {
                $this->sendResponse(404, ['message' => 'Reto no encontrado.']);
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

            $this->sendResponse(200, [
                'retoId' => $retoId,
                'titulo' => $reto->getTitulo(),
                'equiposAsignados' => $equiposAsignados
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Envía una respuesta JSON.
     */
    private function sendResponse(int $statusCode, array $data): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
