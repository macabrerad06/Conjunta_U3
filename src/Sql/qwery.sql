-- Tabla: participantes
-- Almacena los atributos comunes de Estudiante y MentorTecnico.
CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'estudiante' o 'mentorTecnico'
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    nivel_habilidad VARCHAR(50) NOT NULL -- Usando snake_case para nombres de columnas
);

-- Tabla: estudiante
-- Almacena los atributos específicos de la entidad Estudiante.
CREATE TABLE IF NOT EXISTS estudiante (
    participante_id INT PRIMARY KEY,
    grado VARCHAR(50),
    instituto VARCHAR(255),
    tiempo_disponible_semanal INT, -- Usando snake_case
    habilidades TEXT, -- Se almacenará como JSON (ej. ["JavaScript", "UI/UX"])
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
);

-- Tabla: mentor_tecnico
-- Almacena los atributos específicos de la entidad MentorTecnico.
CREATE TABLE IF NOT EXISTS mentor_tecnico ( -- ¡CORREGIDO! Nombre de tabla a snake_case para consistencia con la entidad
    participante_id INT PRIMARY KEY,
    especialidad VARCHAR(255),
    experiencia INT,
    disponibilidad_horaria VARCHAR(255), -- Usando snake_case
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
);

-- Tabla: hackathons
-- Almacena información sobre los eventos de hackathon.
CREATE TABLE IF NOT EXISTS hackathons (
    id VARCHAR(50) PRIMARY KEY, -- Usamos VARCHAR para IDs como 'eduhack2025'
    nombre VARCHAR(255) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    lugar VARCHAR(255)
);

-- Tabla: retos
-- Almacena los atributos comunes de RetoReal y RetoExperimental.
CREATE TABLE IF NOT EXISTS retos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'retoReal' o 'retoExperimental'
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    dificultad VARCHAR(50),
    areas_conocimiento TEXT -- Usando snake_case, se almacenará como JSON
);

-- Tabla: reto_real
-- Almacena los atributos específicos de la entidad RetoReal.
CREATE TABLE IF NOT EXISTS reto_real (
    reto_id INT PRIMARY KEY,
    entidad_colaboradora VARCHAR(255), -- Usando snake_case
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- Tabla: reto_experimental
-- Almacena los atributos específicos de la entidad RetoExperimental.
CREATE TABLE IF NOT EXISTS reto_experimental (
    reto_id INT PRIMARY KEY,
    enfoque_pedagogico VARCHAR(255), -- Usando snake_case
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- Tabla: equipo
-- Almacena información sobre la entidad Equipos.
CREATE TABLE IF NOT EXISTS equipo ( -- ¡CORREGIDO! Nombre de tabla a singular
    id_equipo INT AUTO_INCREMENT PRIMARY KEY, -- Usando snake_case para la PK
    nombre VARCHAR(255) NOT NULL,
    hackathon_id VARCHAR(50), -- Relación con la tabla hackathons
    participantes TEXT, -- Almacenará un array JSON de IDs de participantes
    FOREIGN KEY (hackathon_id) REFERENCES hackathons(id) ON DELETE SET NULL
);

-- Tabla pivote: equipo_reto
-- Gestiona la relación muchos a muchos entre equipo y reto.
CREATE TABLE IF NOT EXISTS equipo_reto (
    equipo_id INT NOT NULL,
    reto_id INT NOT NULL,
    estado VARCHAR(50) DEFAULT 'en_progreso',
    PRIMARY KEY (equipo_id, reto_id),
    FOREIGN KEY (equipo_id) REFERENCES equipo(id_equipo) ON DELETE CASCADE, -- Referencia a 'equipo'
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);
