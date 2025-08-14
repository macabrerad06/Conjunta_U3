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
     * Maneja las solicitudes HTTP entrantes y las dirige al método apropiado.
     * Esto simula un enrutador básico para la API.
     */
    public function handleRequest(string $method, string $path, array $params = []): void
    {
        // Simple enrutamiento basado en método y parte de la URL
        switch ($method) {
            case 'POST':
                if ($path === '/equipos') {
                    $this->createEquipo();
                }
                break;
            case 'GET':
                if ($path === '/equipos') {
                    $this->getEquipos();
                } elseif (preg_match('/^\/equipos\/(\d+)\/retos$/', $path, $matches)) {
                    $equipoId = (int) $matches[1];
                    $this->getRetosByEquipoId($equipoId);
                } elseif (preg_match('/^\/equipos\/(\d+)$/', $path, $matches)) {
                    $equipoId = (int) $matches[1];
                    $this->getEquipoById($equipoId);
                }
                break;
            case 'PUT':
                if (preg_match('/^\/equipos\/(\d+)\/asignar-reto$/', $path, $matches)) {
                    $equipoId = (int) $matches[1];
                    $this->assignRetoToEquipo($equipoId);
                }
                break;
            case 'DELETE':
                if (preg_match('/^\/equipos\/(\d+)$/', $path, $matches)) {
                    $equipoId = (int) $matches[1];
                    $this->deleteEquipo($equipoId);
                }
                break;
            default:
                $this->sendResponse(405, ['message' => 'Method Not Allowed']);
                break;
        }
    }

    /**
     * Crea un nuevo equipo.
     * Endpoint: POST /equipos
     * Body: { "nombre": "...", "hackathonId": "...", "participanteIds": [...] }
     */
    public function createEquipo(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        $requiredFields = ['nombre', 'hackathonId', 'participanteIds'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->sendResponse(400, ['message' => "Missing required field: {$field}"]);
                return;
            }
        }

        try {
            // Generar un ID de equipo (asumiendo que es AUTO_INCREMENT en la DB)
            // Para este ejemplo, usaremos un ID ficticio o dejaremos que la DB lo genere.
            // Si tu ID de equipo es auto-incremental, no lo necesitas en el constructor.
            // Ajustamos el constructor según la entidad `Equipos` que proporcionaste.
            // La entidad `Equipos` toma `idEquipo` en el constructor.
            // Si es auto-incremental, el ID se asignará después de la inserción.
            // Por ahora, asumimos que el ID se asigna en la creación o es un placeholder.
            // Si 'idEquipo' es auto-increment, se puede pasar 0 y actualizar después de la inserción
            // o tu entidad/repositorio podría manejarlo internamente.
            // Aquí, lo creamos con 0 y el repositorio lo manejaría si está configurado para auto-incremento.
            $equipo = new Equipos(
                0, // El ID se establecerá al insertar en la DB si es auto-incremental
                $input['nombre'],
                $input['hackathonId'],
                $input['participanteIds']
            );

            if ($this->equiposRepository->create($equipo)) {
                // Si el idEquipo es auto_increment, podrías recuperarlo aquí:
                // $equipoId = $this->connection->lastInsertId();
                $this->sendResponse(201, ['message' => 'Equipo creado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al crear el equipo.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }


    public function getEquipos(): void
    {
        try {
            $equipos = $this->equiposRepository->findAll();
            $this->sendResponse(200, $equipos);
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }


    public function getEquipoById(int $id): void
    {
        try {
            $equipo = $this->equiposRepository->findById($id);
            if ($equipo) {
                $this->sendResponse(200, $equipo);
            } else {
                $this->sendResponse(404, ['message' => 'Equipo no encontrado.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }


    public function assignRetoToEquipo(int $equipoId): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        if (!isset($input['retoIds']) || !is_array($input['retoIds'])) {
            $this->sendResponse(400, ['message' => 'Missing or invalid "retoIds" array.']);
            return;
        }

        try {
            $equipo = $this->equiposRepository->findById($equipoId);
            if (!$equipo) {
                $this->sendResponse(404, ['message' => 'Equipo no encontrado.']);
                return;
            }

            $this->connection->beginTransaction(); 

            $success = true;
            foreach ($input['retoIds'] as $retoId) {
                $checkSql = "SELECT COUNT(*) FROM equipo_reto WHERE equipo_id = :equipoId AND reto_id = :retoId";
                $checkStmt = $this->connection->prepare($checkSql);
                $checkStmt->execute([':equipoId' => $equipoId, ':retoId' => $retoId]);
                if ($checkStmt->fetchColumn() > 0) {
                    continue; 
                }


                $sql = "INSERT INTO equipo_reto (equipo_id, reto_id, estado) VALUES (:equipoId, :retoId, :estado)";
                $stmt = $this->connection->prepare($sql);
                if (!$stmt->execute([
                    ':equipoId' => $equipoId,
                    ':retoId' => $retoId,
                    ':estado' => 'en_progreso' 
                ])) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                $this->connection->commit();
                $this->sendResponse(200, ['message' => 'Retos asignados exitosamente al equipo.']);
            } else {
                $this->connection->rollBack();
                $this->sendResponse(500, ['message' => 'Error al asignar retos al equipo.']);
            }

        } catch (Exception $e) {
            $this->connection->rollBack(); 
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }


    public function getRetosByEquipoId(int $equipoId): void
    {
        try {
            $equipo = $this->equiposRepository->findById($equipoId);
            if (!$equipo) {
                $this->sendResponse(404, ['message' => 'Equipo no encontrado.']);
                return;
            }

            $sql = "SELECT r.id, r.titulo, r.tipo, r.descripcion, r.dificultad, er.estado
                    FROM equipo_reto er
                    JOIN retos r ON er.reto_id = r.id
                    WHERE er.equipo_id = :equipoId";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':equipoId', $equipoId, PDO::PARAM_INT);
            $stmt->execute();
            $retosAsignados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendResponse(200, [
                'equipoId' => $equipoId,
                'nombre' => $equipo->getNombre(),
                'retosAsignados' => $retosAsignados
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    public function deleteEquipo(int $id): void
    {
        try {
            if ($this->equiposRepository->delete($id)) {
                $this->sendResponse(200, ['message' => 'Equipo eliminado exitosamente.']);
            } else {
                $this->sendResponse(404, ['message' => 'Equipo no encontrado o error al eliminar.']);
            }
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

// Ejemplo de uso (simulando una solicitud HTTP)
// if (basename($_SERVER['PHP_SELF']) === 'index.php') { // Asegura que solo se ejecute al acceder directamente
//     $controller = new EquiposController();
//
//     // Simular una solicitud POST para crear un equipo
//     // $_SERVER['REQUEST_METHOD'] = 'POST';
//     // $_SERVER['REQUEST_URI'] = '/equipos';
//     // file_put_contents('php://input', json_encode([
//     //     'nombre' => 'EcoHackers',
//     //     'hackathonId' => 'eduhack2025',
//     //     'participanteIds' => [1, 2, 5] // IDs numéricos de participantes
//     // ]));
//     // $controller->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
//
//     // Simular una solicitud GET para listar equipos
//     // $_SERVER['REQUEST_METHOD'] = 'GET';
//     // $_SERVER['REQUEST_URI'] = '/equipos';
//     // $controller->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
//
//     // Simular una solicitud PUT para asignar retos
//     // $_SERVER['REQUEST_METHOD'] = 'PUT';
//     // $_SERVER['REQUEST_URI'] = '/equipos/1/asignar-reto'; // Suponiendo ID de equipo 1
//     // file_put_contents('php://input', json_encode([
//     //     'retoIds' => [101, 102] // IDs numéricos de retos
//     // ]));
//     // $controller->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
//
//     // Simular una solicitud GET para obtener retos de un equipo
//     // $_SERVER['REQUEST_METHOD'] = 'GET';
//     // $_SERVER['REQUEST_URI'] = '/equipos/1/retos'; // Suponiendo ID de equipo 1
//     // $controller->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
//
//     // Simular una solicitud DELETE para eliminar un equipo
//     // $_SERVER['REQUEST_METHOD'] = 'DELETE';
//     // $_SERVER['REQUEST_URI'] = '/equipos/1'; // Suponiendo ID de equipo 1
//     // $controller->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
// }
