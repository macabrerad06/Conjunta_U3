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
    // No necesitamos una conexión PDO directa en el controlador si el repositorio maneja todo,
    // pero la mantenemos si hay casos de uso futuros para JOINs complejos fuera del repositorio.
    // private PDO $connection; 

    public function __construct()
    {
        $this->estudianteRepository = new EstudianteRepository();
        // $this->connection = Database::getConnection(); // Si no se usa directamente, se puede omitir
    }

    /**
     * Maneja todas las solicitudes HTTP para el recurso de Estudiantes.
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
                // Obtener un estudiante por ID: GET /participantes?id={id}
                $estudiante = $this->estudianteRepository->findById((int)$_GET['id']);
                echo json_encode($estudiante ? $this->estudianteToArray($estudiante) : null);
                return;
            } else {
                // Listar todos los estudiantes: GET /participantes
                $list = array_map(
                    [$this, 'estudianteToArray'],
                    $this->estudianteRepository->findAll()
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
                           $payload['nivelHabilidad'], $payload['grado'], $payload['institucion'], 
                           $payload['tiempoDisponibleSemanal'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o incompletos para crear un estudiante.']);
                    return;
                }

                if ($payload['tipo'] !== 'estudiante') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Este controlador solo maneja el tipo "estudiante" para la creación.']);
                    return;
                }

                $estudiante = new Estudiante(
                    0, // ID placeholder, será asignado por la DB si es AUTO_INCREMENT
                    $payload['tipo'],
                    $payload['nombre'],
                    $payload['email'],
                    $payload['nivelHabilidad'],
                    $payload['grado'],
                    $payload['institucion'],
                    $payload['tiempoDisponibleSemanal']
                    // Si tu entidad Estudiante tiene 'habilidades' en el constructor y esperas que venga
                    // , $payload['habilidades'] ?? []
                );

                echo json_encode(['success' => $this->estudianteRepository->create($estudiante)]);
            } catch (Exception $e) {
                http_response_code(400); // 400 Bad Request por problemas de datos o 500 para otros errores
                echo json_encode(['error' => 'Error al crear estudiante: ' . $e->getMessage()]);
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
                    echo json_encode(['error' => 'ID de estudiante no válido para la actualización.']);
                    return;
                }

                $existing = $this->estudianteRepository->findById($id);

                if (!$existing) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Estudiante no encontrado para actualizar.']);
                    return;
                }

                // Actualizar solo los campos proporcionados en el payload
                if (isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
                if (isset($payload['email'])) $existing->setEmail($payload['email']);
                if (isset($payload['nivelHabilidad'])) $existing->setNivelHabilidad($payload['nivelHabilidad']);
                if (isset($payload['grado'])) $existing->setGrado($payload['grado']);
                if (isset($payload['institucion'])) $existing->setInstituto($payload['institucion']); // Asumiendo 'instituto' en la entidad
                if (isset($payload['tiempoDisponibleSemanal'])) $existing->setTiempoDisponibleSemanal($payload['tiempoDisponibleSemanal']);
                // Si la entidad Estudiante tiene 'habilidades' y un setter para ello:
                // if (isset($payload['habilidades'])) $existing->setHabilidades($payload['habilidades']);


                echo json_encode(['success' => $this->estudianteRepository->update($existing)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al actualizar estudiante: ' . $e->getMessage()]);
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
                    echo json_encode(['error' => 'ID de estudiante no válido para la eliminación.']);
                    return;
                }
                echo json_encode(['success' => $this->estudianteRepository->delete($id)]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al eliminar estudiante: ' . $e->getMessage()]);
            }
            return;
        }

        // Si el método HTTP no es manejado
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Método no permitido.']);
    }

    /**
     * Convierte un objeto Estudiante a un array asociativo para la respuesta JSON.
     * @param Estudiante $estudiante
     * @return array
     */
    public function estudianteToArray(Estudiante $estudiante): array
    {
        return [
            'participanteId' => $estudiante->getParticipanteId(),
            'tipo' => $estudiante->getTipo(),
            'nombre' => $estudiante->getNombre(),
            'email' => $estudiante->getEmail(),
            'nivelHabilidad' => $estudiante->getNivelHabilidad(),
            'grado' => $estudiante->getGrado(),
            'institucion' => $estudiante->getInstituto(), // Asegúrate que sea 'institucion' en tu JSON si es 'instituto' en la entidad
            'tiempoDisponibleSemanal' => $estudiante->getTiempoDisponibleSemanal(),
            // 'habilidades' => $estudiante->getHabilidades() // Si tu entidad tiene este getter
        ];
    }
}
