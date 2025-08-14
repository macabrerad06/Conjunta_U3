-- Tabla: participantes
-- Almacena los atributos comunes de Estudiantes y Mentores Técnicos.
CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'estudiante' o 'mentorTecnico'
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    nivelHabilidad VARCHAR(50) NOT NULL
);

-- Tabla: estudiantes_detalles
-- Almacena los atributos específicos de los estudiantes, extendiendo la tabla participantes.
CREATE TABLE IF NOT EXISTS estudiantes_detalles (
    participante_id INT PRIMARY KEY,
    grado VARCHAR(50),
    instituto VARCHAR(255),
    tiempoDisponibleSemanal INT,
    -- 'habilidades' se puede almacenar como JSON si es un array de strings
    habilidades TEXT, -- Se asume que se almacenará como JSON (ej. ["JavaScript", "UI/UX"])
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
);

-- Tabla: mentores_tecnicos_detalles
-- Almacena los atributos específicos de los mentores técnicos, extendiendo la tabla participantes.
CREATE TABLE IF NOT EXISTS mentores_tecnicos_detalles (
    participante_id INT PRIMARY KEY,
    especialidad VARCHAR(255),
    experiencia INT,
    disponibilidadHoraria VARCHAR(255),
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
    tipo VARCHAR(50) NOT NULL, -- 'retoReal' o 'retoExperimental'
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    dificultad VARCHAR(50),
    -- 'areasConocimiento' se puede almacenar como JSON si es un array de strings
    areasConocimiento TEXT -- Se asume que se almacenará como JSON (ej. ["Geolocalización", "Mobile"])
);

-- Tabla: retos_reales_detalles
-- Almacena los atributos específicos de los retos reales, extendiendo la tabla retos.
CREATE TABLE IF NOT EXISTS retos_reales_detalles (
    reto_id INT PRIMARY KEY,
    entidadColaboradora VARCHAR(255),
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- Tabla: retos_experimentales_detalles
-- Almacena los atributos específicos de los retos experimentales, extendiendo la tabla retos.
CREATE TABLE IF NOT EXISTS retos_experimentales_detalles (
    reto_id INT PRIMARY KEY,
    enfoquePedagogico VARCHAR(255),
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- Tabla: equipos
-- Almacena información sobre los equipos.
CREATE TABLE IF NOT EXISTS equipos (
    idEquipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    hackathon_id VARCHAR(50), -- Relación con la tabla hackathons
    -- 'participante_ids' se almacenará como JSON si es un array de IDs de participantes
    participante_ids TEXT, -- Se asume que se almacenará como JSON (ej. ["estudiante_001", "mentor_005"])
    FOREIGN KEY (hackathon_id) REFERENCES hackathons(id) ON DELETE SET NULL
);

-- Tabla pivote: equipo_reto
-- Gestiona la relación muchos a muchos entre equipos y retos.
CREATE TABLE IF NOT EXISTS equipo_reto (
    equipo_id INT NOT NULL,
    reto_id INT NOT NULL,
    estado VARCHAR(50) DEFAULT 'en_progreso', -- Añadido según el ejemplo del documento
    PRIMARY KEY (equipo_id, reto_id),
    FOREIGN KEY (equipo_id) REFERENCES equipos(idEquipo) ON DELETE CASCADE,
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);
