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
    // No necesitamos una conexión PDO directa en el controlador si el repositorio maneja todo,
    // pero la mantenemos si hay casos de uso futuros para JOINs complejos fuera del repositorio.
    // private PDO $connection; 

    public function __construct()
    {
        $this->mentorTecnicoRepository = new MentorTecnicoRepository();
        // $this->connection = Database::getConnection(); // Si no se usa directamente, se puede omitir
    }

    /**
     * Maneja todas las solicitudes HTTP para el recurso de MentorTecnico.
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
            if (isset($_GET['id'])) {
                // Obtener un mentor técnico por ID: GET /participantes?id={id}
                $mentorTecnico = $this->mentorTecnicoRepository->findById((int)$_GET['id']);
                echo json_encode($mentorTecnico ? $this->mentorTecnicoToArray($mentorTecnico) : null);
                return;
            } else {
                // Listar todos los mentores técnicos: GET /participantes
                $list = array_map(
                    [$this, 'mentorTecnicoToArray'],
                    $this->mentorTecnicoRepository->findAll()
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
                    !isset($payload['tipo'], $payload['nombre'], $payload['email'], 
                           $payload['nivelHabilidad'], $payload['especialidad'], 
                           $payload['experiencia'], $payload['disponibilidadHoraria'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o incompletos para crear un mentor técnico.']);
                    return;
                }

                if ($payload['tipo'] !== 'mentorTecnico') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Este controlador solo maneja el tipo "mentorTecnico" para la creación.']);
                    return;
                }

                $mentorTecnico = new MentorTecnico(
                    0, // ID placeholder, será asignado por la DB si es AUTO_INCREMENT
                    $payload['tipo'],
                    $payload['nombre'],
                    $payload['email'],
                    $payload['nivelHabilidad'],
                    $payload['especialidad'],
                    $payload['experiencia'],
                    $payload['disponibilidadHoraria']
                );

                echo json_encode(['success' => $this->mentorTecnicoRepository->create($mentorTecnico)]);
            } catch (Exception $e) {
                http_response_code(400); // 400 Bad Request por problemas de datos o 500 para otros errores
                echo json_encode(['error' => 'Error al crear mentor técnico: ' . $e->getMessage()]);
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
                    echo json_encode(['error' => 'ID de mentor técnico no válido para la actualización.']);
                    return;
                }

                $existing = $this->mentorTecnicoRepository->findById($id);

                if (!$existing) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Mentor técnico no encontrado para actualizar.']);
                    return;
                }

                // Actualizar solo los campos proporcionados en el payload
                if (isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
                if (isset($payload['email'])) $existing->setEmail($payload['email']);
                if (isset($payload['nivelHabilidad'])) $existing->setNivelHabilidad($payload['nivelHabilidad']);
                if (isset($payload['especialidad'])) $existing->setEspecialidad($payload['especialidad']);
                if (isset($payload['experiencia'])) $existing->setExperiencia($payload['experiencia']);
                if (isset($payload['disponibilidadHoraria'])) $existing->setDisponibilidadHoraria($payload['disponibilidadHoraria']);

                echo json_encode(['success' => $this->mentorTecnicoRepository->update($existing)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al actualizar mentor técnico: ' . $e->getMessage()]);
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
                    echo json_encode(['error' => 'ID de mentor técnico no válido para la eliminación.']);
                    return;
                }
                echo json_encode(['success' => $this->mentorTecnicoRepository->delete($id)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al eliminar mentor técnico: ' . $e->getMessage()]);
            }
            return;
        }

        // Si el método HTTP no es manejado
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Método no permitido.']);
    }

    /**
     * Convierte un objeto MentorTecnico a un array asociativo para la respuesta JSON.
     * @param MentorTecnico $mentorTecnico
     * @return array
     */
    public function mentorTecnicoToArray(MentorTecnico $mentorTecnico): array
    {
        return [
            'participanteId' => $mentorTecnico->getParticipanteId(),
            'tipo' => $mentorTecnico->getTipo(),
            'nombre' => $mentorTecnico->getNombre(),
            'email' => $mentorTecnico->getEmail(),
            'nivelHabilidad' => $mentorTecnico->getNivelHabilidad(),
            'especialidad' => $mentorTecnico->getEspecialidad(),
            'experiencia' => $mentorTecnico->getExperiencia(),
            'disponibilidadHoraria' => $mentorTecnico->getDisponibilidadHoraria()
        ];
    }
}
