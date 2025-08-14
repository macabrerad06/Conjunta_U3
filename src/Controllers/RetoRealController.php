<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\RetoReal;
use App\Repositories\RetoRealRepository;
use App\config\Database; // Necesario para interactuar con tablas relacionadas (ej. equipo_reto)
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
     * Crea un nuevo reto real.
     * Endpoint: POST /retos
     * Body: { "tipo": "retoReal", "titulo": "...", "descripcion": "...", "dificultad": "...", "areasConocimiento": [...], "entidadColaboradora": "..." }
     */
    public function createReto(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        // Validación básica de campos requeridos para RetoReal
        $requiredFields = ['tipo', 'titulo', 'descripcion', 'dificultad', 'areasConocimiento', 'entidadColaboradora'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->sendResponse(400, ['message' => "Missing required field: {$field}"]);
                return;
            }
        }

        if ($input['tipo'] !== 'retoReal') {
            $this->sendResponse(400, ['message' => 'This controller only handles "retoReal" type.']);
            return;
        }

        try {
            // Se asume que el ID (retoId) es AUTO_INCREMENT en la DB y no se pasa al constructor inicialmente.
            // Si tu entidad RetoReal toma un ID en el constructor, ajusta esta línea.
            $reto = new RetoReal(
                0, // ID placeholder, será asignado por la DB
                $input['tipo'],
                $input['titulo'],
                $input['descripcion'],
                $input['dificultad'],
                $input['areasConocimiento'],
                $input['entidadColaboradora']
            );

            if ($this->retoRealRepository->create($reto)) {
                // Si el ID es auto_increment, el repositorio podría devolverlo o podrías recuperarlo con lastInsertId()
                $this->sendResponse(201, ['message' => 'Reto Real creado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al crear el Reto Real.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene un reto real por su ID.
     * Endpoint: GET /retos/{id}
     */
    public function getRetoById(int $id): void
    {
        try {
            $reto = $this->retoRealRepository->findById($id);
            if ($reto) {
                $this->sendResponse(200, $reto);
            } else {
                $this->sendResponse(404, ['message' => 'Reto Real no encontrado.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza un reto real existente.
     * Endpoint: PUT /retos/{id}
     * Body: { "titulo": "...", "descripcion": "...", "dificultad": "...", "areasConocimiento": [...], "entidadColaboradora": "..." }
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
            $reto = $this->retoRealRepository->findById($id);
            if (!$reto) {
                $this->sendResponse(404, ['message' => 'Reto Real no encontrado para actualizar.']);
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
            if (isset($input['entidadColaboradora'])) {
                $reto->setEntidadColaboradora($input['entidadColaboradora']);
            }
            // El 'tipo' no debería cambiar una vez que el reto es creado (retoReal vs retoExperimental)
            // El 'retoId' también es el mismo

            if ($this->retoRealRepository->update($reto)) {
                $this->sendResponse(200, ['message' => 'Reto Real actualizado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al actualizar el Reto Real.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina un reto real.
     * Endpoint: DELETE /retos/{id}
     */
    public function deleteReto(int $id): void
    {
        try {
            if ($this->retoRealRepository->delete($id)) {
                $this->sendResponse(200, ['message' => 'Reto Real eliminado exitosamente.']);
            } else {
                $this->sendResponse(404, ['message' => 'Reto Real no encontrado o error al eliminar.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene todos los retos (reales).
     * Endpoint: GET /retos
     * Nota: Este método listaría solo los retos reales si el repositorio solo maneja ese tipo.
     * Para listar ambos tipos (reales y experimentales), se necesitaría un 'RetoGeneralRepository' o similar.
     */
    public function getRetos(): void
    {
        try {
            $retos = $this->retoRealRepository->findAll();
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
            $reto = $this->retoRealRepository->findById($retoId);
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

            // Preparar el conteo de miembros
            // Nota: La forma de contar miembros usando JSON_CONTAINS es para MySQL 5.7+
            // Y asume que participante_ids en la tabla 'equipos' es un JSON array de IDs numéricos.
            // Si los IDs en participante_ids no son numéricos o el formato es diferente,
            // esta parte puede necesitar ajustes. Para MySQL 8+, se puede usar JSON_TABLE o JSON_OVERLAPS.

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
