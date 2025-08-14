<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Estudiante;
use App\Repositories\EstudianteRepository;
use App\config\Database; // Necesario para la conexión a la base de datos
use PDO;
use Exception;

class EstudianteController
{
    private EstudianteRepository $estudianteRepository;
    private PDO $connection; // Usamos la conexión PDO si necesitamos operaciones directas o JOINs

    public function __construct()
    {
        $this->estudianteRepository = new EstudianteRepository();
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
                if ($path === '/participantes') {
                    $this->createEstudiante();
                }
                break;
            case 'GET':
                if ($path === '/participantes') {
                    $this->getEstudiantes();
                } elseif (preg_match('/^\/participantes\/(\d+)$/', $path, $matches)) {
                    $participanteId = (int) $matches[1];
                    $this->getEstudianteById($participanteId);
                }
                break;
            case 'PUT':
                if (preg_match('/^\/participantes\/(\d+)$/', $path, $matches)) {
                    $participanteId = (int) $matches[1];
                    $this->updateEstudiante($participanteId);
                }
                break;
            case 'DELETE':
                if (preg_match('/^\/participantes\/(\d+)$/', $path, $matches)) {
                    $participanteId = (int) $matches[1];
                    $this->deleteEstudiante($participanteId);
                }
                break;
            default:
                $this->sendResponse(405, ['message' => 'Method Not Allowed']);
                break;
        }
    }

    /**
     * Crea un nuevo estudiante.
     * Endpoint: POST /participantes
     * Body: { "tipo": "estudiante", "nombre": "...", "email": "...", "nivelHabilidad": "...", "grado": "...", "institucion": "...", "tiempoDisponibleSemanal": ..., "habilidades": [...] }
     */
    public function createEstudiante(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        // Validación básica de campos requeridos para Estudiante
        $requiredFields = ['tipo', 'nombre', 'email', 'nivelHabilidad', 'grado', 'institucion', 'tiempoDisponibleSemanal'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->sendResponse(400, ['message' => "Missing required field: {$field}"]);
                return;
            }
        }

        if ($input['tipo'] !== 'estudiante') {
            $this->sendResponse(400, ['message' => 'This controller only handles "estudiante" type for creation.']);
            return;
        }

        try {
            // El ID (participanteId) es AUTO_INCREMENT en la tabla `participantes`.
            // Asumimos que el repositorio se encarga de la inserción en `participantes`
            // y luego en `estudiantes_detalles` utilizando el ID generado.
            $estudiante = new Estudiante(
                0, // ID placeholder, será asignado por la DB
                $input['tipo'],
                $input['nombre'],
                $input['email'],
                $input['nivelHabilidad'],
                $input['grado'],
                $input['institucion'],
                $input['tiempoDisponibleSemanal']
                // Si la entidad Estudiante toma 'habilidades' en el constructor, ajusta aquí:
                // , $input['habilidades'] ?? []
            );

            if ($this->estudianteRepository->create($estudiante)) {
                $this->sendResponse(201, ['message' => 'Estudiante creado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al crear el estudiante.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene un estudiante por su ID.
     * Endpoint: GET /participantes/{id}
     */
    public function getEstudianteById(int $id): void
    {
        try {
            $estudiante = $this->estudianteRepository->findById($id);
            if ($estudiante) {
                $this->sendResponse(200, $estudiante);
            } else {
                $this->sendResponse(404, ['message' => 'Estudiante no encontrado.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza un estudiante existente.
     * Endpoint: PUT /participantes/{id}
     * Body: { "nombre": "...", "email": "...", "nivelHabilidad": "...", "grado": "...", "institucion": "...", "tiempoDisponibleSemanal": ..., "habilidades": [...] }
     */
    public function updateEstudiante(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        try {
            $estudiante = $this->estudianteRepository->findById($id);
            if (!$estudiante) {
                $this->sendResponse(404, ['message' => 'Estudiante no encontrado para actualizar.']);
                return;
            }

            // Actualizar solo los campos proporcionados en el input
            if (isset($input['nombre'])) {
                $estudiante->setNombre($input['nombre']);
            }
            if (isset($input['email'])) {
                $estudiante->setEmail($input['email']);
            }
            if (isset($input['nivelHabilidad'])) {
                $estudiante->setNivelHabilidad($input['nivelHabilidad']);
            }
            if (isset($input['grado'])) {
                $estudiante->setGrado($input['grado']);
            }
            if (isset($input['instituto'])) { // Asegúrate que sea 'instituto' y no 'institucion' si así está en tu entidad/DB
                $estudiante->setInstituto($input['instituto']);
            }
            if (isset($input['tiempoDisponibleSemanal'])) {
                $estudiante->setTiempoDisponibleSemanal($input['tiempoDisponibleSemanal']);
            }
            // Si la entidad Estudiante tiene 'habilidades' y un setter para ello, actualiza aquí:
            // if (isset($input['habilidades'])) {
            //     $estudiante->setHabilidades($input['habilidades']);
            // }

            if ($this->estudianteRepository->update($estudiante)) {
                $this->sendResponse(200, ['message' => 'Estudiante actualizado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al actualizar el estudiante.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina un estudiante.
     * Endpoint: DELETE /participantes/{id}
     */
    public function deleteEstudiante(int $id): void
    {
        try {
            if ($this->estudianteRepository->delete($id)) {
                $this->sendResponse(200, ['message' => 'Estudiante eliminado exitosamente.']);
            } else {
                $this->sendResponse(404, ['message' => 'Estudiante no encontrado o error al eliminar.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene todos los estudiantes.
     * Endpoint: GET /participantes
     */
    public function getEstudiantes(): void
    {
        try {
            $estudiantes = $this->estudianteRepository->findAll();
            $this->sendResponse(200, $estudiantes);
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
