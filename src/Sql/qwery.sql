-- Tabla participantes
CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'estudiante' o 'mentorTecnico'
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    nivel_habilidad VARCHAR(50) NOT NULL -- Cambiado a snake_case
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
    disponibilidad_horaria VARCHAR(255), -- Cambiado a snake_case
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
);

-- Tabla: hackathons
CREATE TABLE IF NOT EXISTS hackathons (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    lugar VARCHAR(255)
);

-- Tabla: retos
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
    entidad_colaboradora VARCHAR(255), -- Cambiado a snake_case
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- Tabla: retos_experimentales_detalles
-- Almacena los atributos específicos de los retos experimentales, extendiendo la tabla retos.
CREATE TABLE IF NOT EXISTS retos_experimentales_detalles (
    reto_id INT PRIMARY KEY,
    enfoque_pedagogico VARCHAR(255), -- Cambiado a snake_case
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
    estado VARCHAR(50) DEFAULT 'en_progreso',
    PRIMARY KEY (equipo_id, reto_id),
    FOREIGN KEY (equipo_id) REFERENCES equipo(id_equipo) ON DELETE CASCADE, -- ¡CORREGIDO!
    FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
);

-- 1. PROCEDIMIENTOS PARA PARTICIPANTES (Y SUS DETALLES)

DELIMITER //
CREATE PROCEDURE SP_InsertarParticipanteCompleto(
    IN p_tipo VARCHAR(50),
    IN p_nombre VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_nivelHabilidad VARCHAR(50),
    -- Parámetros para Estudiante
    IN p_grado VARCHAR(50),
    IN p_instituto VARCHAR(255),
    IN p_tiempoDisponibleSemanal INT,
    IN p_habilidades TEXT,
    -- Parámetros para Mentor Técnico
    IN p_especialidad VARCHAR(255),
    IN p_experiencia INT,
    IN p_disponibilidadHoraria VARCHAR(255)
)
BEGIN
    DECLARE new_participante_id INT;

    INSERT INTO participantes (tipo, nombre, email, nivelHabilidad)
    VALUES (p_tipo, p_nombre, p_email, p_nivelHabilidad);

    SET new_participante_id = LAST_INSERT_ID();

    IF p_tipo = 'estudiante' THEN
        INSERT INTO estudiantes_detalles (participante_id, grado, instituto, tiempoDisponibleSemanal, habilidades)
        VALUES (new_participante_id, p_grado, p_instituto, p_tiempoDisponibleSemanal, p_habilidades);
    ELSEIF p_tipo = 'mentor_tecnico' THEN
        INSERT INTO mentores_tecnicos_detalles (participante_id, especialidad, experiencia, disponibilidadHoraria)
        VALUES (new_participante_id, p_especialidad, p_experiencia, p_disponibilidadHoraria);
    END IF;

    SELECT new_participante_id AS participante_id;
END //

-- SP_ObtenerDetallesParticipante: Obtiene todos los detalles de un participante dado su ID.
CREATE PROCEDURE SP_ObtenerDetallesParticipante(
    IN p_id INT
)
BEGIN
    DECLARE v_tipo VARCHAR(50);

    -- Obtener el tipo de participante primero
    SELECT tipo INTO v_tipo FROM participantes WHERE id = p_id;

    IF v_tipo = 'estudiante' THEN
        SELECT
            p.id,
            p.tipo,
            p.nombre,
            p.email,
            p.nivelHabilidad,
            ed.grado,
            ed.instituto,
            ed.tiempoDisponibleSemanal,
            ed.habilidades
        FROM participantes p
        INNER JOIN estudiantes_detalles ed ON p.id = ed.participante_id
        WHERE p.id = p_id;
    ELSEIF v_tipo = 'mentor_tecnico' THEN
        SELECT
            p.id,
            p.tipo,
            p.nombre,
            p.email,
            p.nivelHabilidad,
            mtd.especialidad,
            mtd.experiencia,
            mtd.disponibilidadHoraria
        FROM participantes p
        INNER JOIN mentores_tecnicos_detalles mtd ON p.id = mtd.participante_id
        WHERE p.id = p_id;
    END IF;
END //

DELIMITER ;

-- -----------------------------------------------------------
-- 2. PROCEDIMIENTOS PARA HACKATHONS
-- -----------------------------------------------------------

DELIMITER //

-- SP_InsertarHackathon: Inserta un nuevo registro de hackathon.
CREATE PROCEDURE SP_InsertarHackathon(
    IN p_id VARCHAR(50),
    IN p_nombre VARCHAR(255),
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_lugar VARCHAR(255)
)
BEGIN
    INSERT INTO hackathons (id, nombre, fecha_inicio, fecha_fin, lugar)
    VALUES (p_id, p_nombre, p_fecha_inicio, p_fecha_fin, p_lugar);
END //

-- SP_ObtenerHackathonPorId: Obtiene los detalles de un hackathon por su ID.
CREATE PROCEDURE SP_ObtenerHackathonPorId(
    IN p_id VARCHAR(50)
)
BEGIN
    SELECT * FROM hackathons WHERE id = p_id;
END //

-- SP_ListarHackathons: Lista todos los hackathons.
CREATE PROCEDURE SP_ListarHackathons()
BEGIN
    SELECT * FROM hackathons;
END //

DELIMITER ;

-- -----------------------------------------------------------
-- 3. PROCEDIMIENTOS PARA RETOS (Y SUS DETALLES)
-- -----------------------------------------------------------

DELIMITER //

-- SP_InsertarRetoCompleto: Inserta un nuevo reto y sus detalles específicos (real o experimental).
-- Los parámetros no aplicables para un tipo deben pasarse como NULL.
CREATE PROCEDURE SP_InsertarRetoCompleto(
    IN p_tipo VARCHAR(50), -- 'real' o 'experimental'
    IN p_titulo VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_dificultad VARCHAR(50),
    IN p_areasConocimiento TEXT,
    -- Parámetros para Reto Real
    IN p_entidadColaboradora VARCHAR(255),
    -- Parámetros para Reto Experimental
    IN p_enfoquePedagogico VARCHAR(255)
)
BEGIN
    DECLARE new_reto_id INT;

    -- Insertar en la tabla principal de retos
    INSERT INTO retos (tipo, titulo, descripcion, dificultad, areasConocimiento)
    VALUES (p_tipo, p_titulo, p_descripcion, p_dificultad, p_areasConocimiento);

    -- Obtener el ID generado para el nuevo reto
    SET new_reto_id = LAST_INSERT_ID();

    -- Insertar en la tabla de detalles correspondiente
    IF p_tipo = 'real' THEN
        INSERT INTO retos_reales_detalles (reto_id, entidadColaboradora)
        VALUES (new_reto_id, p_entidadColaboradora);
    ELSEIF p_tipo = 'experimental' THEN
        INSERT INTO retos_experimentales_detalles (reto_id, enfoquePedagogico)
        VALUES (new_reto_id, p_enfoquePedagogico);
    END IF;

    -- Devolver el ID del reto insertado (opcional, pero útil)
    SELECT new_reto_id AS reto_id;
END //

-- SP_ObtenerDetallesReto: Obtiene todos los detalles de un reto dado su ID.
CREATE PROCEDURE SP_ObtenerDetallesReto(
    IN p_id INT
)
BEGIN
    DECLARE v_tipo VARCHAR(50);

    -- Obtener el tipo de reto primero
    SELECT tipo INTO v_tipo FROM retos WHERE id = p_id;

    IF v_tipo = 'real' THEN
        SELECT
            r.id,
            r.tipo,
            r.titulo,
            r.descripcion,
            r.dificultad,
            r.areasConocimiento,
            rrd.entidadColaboradora
        FROM retos r
        INNER JOIN retos_reales_detalles rrd ON r.id = rrd.reto_id
        WHERE r.id = p_id;
    ELSEIF v_tipo = 'experimental' THEN
        SELECT
            r.id,
            r.tipo,
            r.titulo,
            r.descripcion,
            r.dificultad,
            r.areasConocimiento,
            red.enfoquePedagogico
        FROM retos r
        INNER JOIN retos_experimentales_detalles red ON r.id = red.reto_id
        WHERE r.id = p_id;
    END IF;
END //

-- SP_ListarRetos: Lista todos los retos, con opción de filtrar por tipo.
CREATE PROCEDURE SP_ListarRetos(
    IN p_tipo_filtro VARCHAR(50) -- 'real', 'experimental', o NULL para todos
)
BEGIN
    IF p_tipo_filtro IS NULL THEN
        SELECT * FROM retos;
    ELSE
        SELECT * FROM retos WHERE tipo = p_tipo_filtro;
    END IF;
END //

DELIMITER ;

-- -----------------------------------------------------------
-- 4. PROCEDIMIENTOS PARA EQUIPOS
-- -----------------------------------------------------------

DELIMITER //

-- SP_InsertarEquipo: Inserta un nuevo equipo.
-- Se recomienda considerar una tabla pivote 'equipo_participante' para una relación muchos a muchos adecuada.
CREATE PROCEDURE SP_InsertarEquipo(
    IN p_nombre VARCHAR(255),
    IN p_hackathon_id VARCHAR(50),
    IN p_participante_ids TEXT -- Lista de IDs de participantes separados por coma (ej. '1,2,3')
)
BEGIN
    INSERT INTO equipos (nombre, hackathon_id, participante_ids)
    VALUES (p_nombre, p_hackathon_id, p_participante_ids);

    SELECT LAST_INSERT_ID() AS idEquipo;
END //

-- SP_ObtenerEquipoPorId: Obtiene los detalles de un equipo por su ID.
CREATE PROCEDURE SP_ObtenerEquipoPorId(
    IN p_idEquipo INT
)
BEGIN
    SELECT * FROM equipos WHERE idEquipo = p_idEquipo;
END //

DELIMITER ;

-- -----------------------------------------------------------
-- 5. PROCEDIMIENTOS PARA LA TABLA PIVOTE EQUIPO-RETO
-- -----------------------------------------------------------

DELIMITER //

-- SP_AsignarRetoAEquipo: Asigna un reto a un equipo insertando en la tabla equipo_reto.
CREATE PROCEDURE SP_AsignarRetoAEquipo(
    IN p_equipo_id INT,
    IN p_reto_id INT
)
BEGIN
    INSERT INTO equipo_reto (equipo_id, reto_id, estado)
    VALUES (p_equipo_id, p_reto_id, 'en_progreso');
END //

-- SP_ActualizarEstadoRetoEquipo: Actualiza el estado de un reto para un equipo específico.
CREATE PROCEDURE SP_ActualizarEstadoRetoEquipo(
    IN p_equipo_id INT,
    IN p_reto_id INT,
    IN p_nuevo_estado VARCHAR(50)
)
BEGIN
    UPDATE equipo_reto
    SET estado = p_nuevo_estado
    WHERE equipo_id = p_equipo_id AND reto_id = p_reto_id;
END //

-- SP_ObtenerRetosDeEquipo: Obtiene todos los retos asignados a un equipo, incluyendo sus detalles básicos.
CREATE PROCEDURE SP_ObtenerRetosDeEquipo(
    IN p_equipo_id INT
)
BEGIN
    SELECT
        r.id,
        r.tipo,
        r.titulo,
        er.estado
    FROM retos r
    INNER JOIN equipo_reto er ON r.id = er.reto_id
    WHERE er.equipo_id = p_equipo_id;
END //

-- SP_ObtenerEquiposPorReto: Obtiene todos los equipos asignados a un reto, incluyendo su estado.
CREATE PROCEDURE SP_ObtenerEquiposPorReto(
    IN p_reto_id INT
)
BEGIN
    SELECT
        e.idEquipo,
        e.nombre,
        e.hackathon_id,
        e.participante_ids,
        er.estado
    FROM equipos e
    INNER JOIN equipo_reto er ON e.idEquipo = er.equipo_id
    WHERE er.reto_id = p_reto_id;
END //

DELIMITER ;