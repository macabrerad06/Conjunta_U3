<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\MentorTecnico;
use App\Repositories\MentorTecnicoRepository;
use App\config\Database; // Necesario para la conexión a la base de datos
use PDO;
use Exception;

class MentorTecnicoController
{
    private MentorTecnicoRepository $mentorTecnicoRepository;
    private PDO $connection; // Usamos la conexión PDO si necesitamos operaciones directas o JOINs

    public function __construct()
    {
        $this->mentorTecnicoRepository = new MentorTecnicoRepository();
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
                if ($path === '/participantes') { // Mismo endpoint que Estudiante para registrar participantes
                    $this->createMentorTecnico();
                }
                break;
            case 'GET':
                if ($path === '/participantes') {
                    $this->getMentoresTecnicos();
                } elseif (preg_match('/^\/participantes\/(\d+)$/', $path, $matches)) {
                    $participanteId = (int) $matches[1];
                    $this->getMentorTecnicoById($participanteId);
                }
                break;
            case 'PUT':
                if (preg_match('/^\/participantes\/(\d+)$/', $path, $matches)) {
                    $participanteId = (int) $matches[1];
                    $this->updateMentorTecnico($participanteId);
                }
                break;
            case 'DELETE':
                if (preg_match('/^\/participantes\/(\d+)$/', $path, $matches)) {
                    $participanteId = (int) $matches[1];
                    $this->deleteMentorTecnico($participanteId);
                }
                break;
            default:
                $this->sendResponse(405, ['message' => 'Method Not Allowed']);
                break;
        }
    }

    /**
     * Crea un nuevo mentor técnico.
     * Endpoint: POST /participantes
     * Body: { "tipo": "mentorTecnico", "nombre": "...", "email": "...", "nivelHabilidad": "...", "especialidad": "...", "experiencia": ..., "disponibilidadHoraria": "..." }
     */
    public function createMentorTecnico(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        // Validación básica de campos requeridos para MentorTecnico
        $requiredFields = ['tipo', 'nombre', 'email', 'nivelHabilidad', 'especialidad', 'experiencia', 'disponibilidadHoraria'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->sendResponse(400, ['message' => "Missing required field: {$field}"]);
                return;
            }
        }

        if ($input['tipo'] !== 'mentorTecnico') {
            $this->sendResponse(400, ['message' => 'This controller only handles "mentorTecnico" type for creation.']);
            return;
        }

        try {
            // El ID (participanteId) es AUTO_INCREMENT en la tabla `participantes`.
            // Asumimos que el repositorio se encarga de la inserción en `participantes`
            // y luego en `mentores_tecnicos_detalles` utilizando el ID generado.
            $mentorTecnico = new MentorTecnico(
                0, // ID placeholder, será asignado por la DB
                $input['tipo'],
                $input['nombre'],
                $input['email'],
                $input['nivelHabilidad'],
                $input['especialidad'],
                $input['experiencia'],
                $input['disponibilidadHoraria']
            );

            if ($this->mentorTecnicoRepository->create($mentorTecnico)) {
                $this->sendResponse(201, ['message' => 'Mentor Técnico creado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al crear el mentor técnico.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene un mentor técnico por su ID.
     * Endpoint: GET /participantes/{id}
     */
    public function getMentorTecnicoById(int $id): void
    {
        try {
            $mentorTecnico = $this->mentorTecnicoRepository->findById($id);
            if ($mentorTecnico) {
                $this->sendResponse(200, $mentorTecnico);
            } else {
                $this->sendResponse(404, ['message' => 'Mentor Técnico no encontrado.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza un mentor técnico existente.
     * Endpoint: PUT /participantes/{id}
     * Body: { "nombre": "...", "email": "...", "nivelHabilidad": "...", "especialidad": "...", "experiencia": ..., "disponibilidadHoraria": "..." }
     */
    public function updateMentorTecnico(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['message' => 'Invalid JSON input.']);
            return;
        }

        try {
            $mentorTecnico = $this->mentorTecnicoRepository->findById($id);
            if (!$mentorTecnico) {
                $this->sendResponse(404, ['message' => 'Mentor Técnico no encontrado para actualizar.']);
                return;
            }

            // Actualizar solo los campos proporcionados en el input
            if (isset($input['nombre'])) {
                $mentorTecnico->setNombre($input['nombre']);
            }
            if (isset($input['email'])) {
                $mentorTecnico->setEmail($input['email']);
            }
            if (isset($input['nivelHabilidad'])) {
                $mentorTecnico->setNivelHabilidad($input['nivelHabilidad']);
            }
            if (isset($input['especialidad'])) {
                $mentorTecnico->setEspecialidad($input['especialidad']);
            }
            if (isset($input['experiencia'])) {
                $mentorTecnico->setExperiencia($input['experiencia']);
            }
            if (isset($input['disponibilidadHoraria'])) {
                $mentorTecnico->setDisponibilidadHoraria($input['disponibilidadHoraria']);
            }

            if ($this->mentorTecnicoRepository->update($mentorTecnico)) {
                $this->sendResponse(200, ['message' => 'Mentor Técnico actualizado exitosamente.']);
            } else {
                $this->sendResponse(500, ['message' => 'Error al actualizar el mentor técnico.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina un mentor técnico.
     * Endpoint: DELETE /participantes/{id}
     */
    public function deleteMentorTecnico(int $id): void
    {
        try {
            if ($this->mentorTecnicoRepository->delete($id)) {
                $this->sendResponse(200, ['message' => 'Mentor Técnico eliminado exitosamente.']);
            } else {
                $this->sendResponse(404, ['message' => 'Mentor Técnico no encontrado o error al eliminar.']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene todos los mentores técnicos.
     * Endpoint: GET /participantes
     */
    public function getMentoresTecnicos(): void
    {
        try {
            $mentores = $this->mentorTecnicoRepository->findAll();
            $this->sendResponse(200, $mentores);
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
