-- Tabla: participantes
-- Almacena los atributos comunes de Estudiantes y Mentores Técnicos.
CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'estudiante' o 'mentor_tecnico'
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    nivel_habilidad VARCHAR(50) NOT NULL -- Cambiado a snake_case
);

-- Tabla: estudiante
-- Almacena los atributos específicos de los estudiantes, extendiendo la tabla participantes.
CREATE TABLE IF NOT EXISTS estudiante ( -- ¡CORREGIDO! Nombre de tabla en singular
    participante_id INT PRIMARY KEY,
    grado VARCHAR(50),
    instituto VARCHAR(255),
    tiempo_disponible_semanal INT, -- Cambiado a snake_case
    -- 'habilidades' se puede almacenar como JSON si es un array de strings
    habilidades TEXT, -- Se asume que se almacenará como JSON (ej. ["JavaScript", "UI/UX"])
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
);

-- Tabla: mentor_tecnico
-- Almacena los atributos específicos de los mentores técnicos, extendiendo la tabla participantes.
CREATE TABLE IF NOT EXISTS mentor_tecnico ( -- ¡CORREGIDO! Nombre de tabla en singular
    participante_id INT PRIMARY KEY,
    especialidad VARCHAR(255),
    experiencia INT,
    disponibilidad_horaria VARCHAR(255), -- Cambiado a snake_case
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
-- Almacena los atributos comunes de Retos Reales y Retos Experimentales.
CREATE TABLE IF NOT EXISTS retos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'reto_real' o 'reto_experimental' (cambiado para consistencia con nombres de tabla)
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    dificultad VARCHAR(50),
    -- 'areas_conocimiento' se puede almacenar como JSON si es un array de strings
    areas_conocimiento TEXT -- Cambiado a snake_case, se asume que se almacenará como JSON (ej. ["Geolocalización", "Mobile"])
);

-- Tabla: reto_real
-- Almacena los atributos específicos de los retos reales, extendiendo la tabla retos.
CREATE TABLE IF NOT EXISTS reto_real ( -- ¡CORREGIDO! Nombre de tabla en singular
    reto_id INT PRIMARY KEY,
    entidad_colaboradora VARCHAR(255), -- Cambiado a snake_case
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- Tabla: reto_experimental
-- Almacena los atributos específicos de los retos experimentales, extendiendo la tabla retos.
CREATE TABLE IF NOT EXISTS reto_experimental ( -- ¡CORREGIDO! Nombre de tabla en singular
    reto_id INT PRIMARY KEY,
    enfoque_pedagogico VARCHAR(255), -- Cambiado a snake_case
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- Tabla: equipo
-- Almacena información sobre los equipos.
CREATE TABLE IF NOT EXISTS equipo ( -- ¡CORREGIDO! Nombre de tabla en singular
    id_equipo INT AUTO_INCREMENT PRIMARY KEY, -- ¡CORREGIDO! Nombre de columna a snake_case
    nombre VARCHAR(255) NOT NULL,
    hackathon_id VARCHAR(50), -- Relación con la tabla hackathons
    -- 'participante_ids' se almacenará como JSON si es un array de IDs de participantes (ej. [1, 5, 8])
    participante_ids TEXT, -- Almacena un array JSON de IDs de participantes
    FOREIGN KEY (hackathon_id) REFERENCES hackathons(id) ON DELETE SET NULL
);

-- Tabla pivote: equipo_reto
-- Gestiona la relación muchos a muchos entre equipo y reto.
CREATE TABLE IF NOT EXISTS equipo_reto (
    equipo_id INT NOT NULL,
    reto_id INT NOT NULL,
    estado VARCHAR(50) DEFAULT 'en_progreso', -- Añadido según el ejemplo del documento
    PRIMARY KEY (equipo_id, reto_id),
    FOREIGN KEY (equipo_id) REFERENCES equipo(id_equipo) ON DELETE CASCADE, -- ¡CORREGIDO!
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);
