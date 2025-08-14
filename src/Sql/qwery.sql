CREATE DATABASE IF NOT EXISTS eduhack;
USE eduhack;

-- TABLAS

CREATE TABLE participante (
    id VARCHAR(100) PRIMARY KEY,
    tipo ENUM('estudiante', 'mentor') NOT NULL,
    nombre VARCHAR(20) NOT NULL,
    email VARCHAR(20) NOT NULL UNIQUE,
    nivelHabilidad VARCHAR(20)
);

CREATE TABLE estudiante (
    participanteId VARCHAR(100) PRIMARY KEY,
    grado VARCHAR(20),
    institucion VARCHAR(255),
    tiempoDisponibleSemanal INT,
    FOREIGN KEY (participanteId) REFERENCES participante(id)
);

CREATE TABLE mentorTecnico (
    participanteId VARCHAR(100) PRIMARY KEY,
    especialidad VARCHAR(255),
    experiencia INT,
    disponibilidadHoraria VARCHAR(50),
    FOREIGN KEY (participanteId) REFERENCES participante(id)
);

CREATE TABLE reto (
    id VARCHAR(100) PRIMARY KEY,
    tipo ENUM('real', 'experimental') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    complejidad VARCHAR(50),
    areasConocimiento TEXT
);

CREATE TABLE retoReal (
    retoId VARCHAR(255) PRIMARY KEY,
    entidadColaboradora VARCHAR(255),
    FOREIGN KEY (retoId) REFERENCES reto(id)
);

CREATE TABLE retoExperimental (
    retoId VARCHAR(255) PRIMARY KEY,
    enfoquePedagogico VARCHAR(255),
    FOREIGN KEY (retoId) REFERENCES reto(id)
);

CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    hackathonId VARCHAR(50) NOT NULL
);

CREATE TABLE equipo_participantes (
    equipoId INT,
    participanteId VARCHAR(255),
    PRIMARY KEY (equipoId, participanteId),
    FOREIGN KEY (equipoId) REFERENCES equipos(id),
    FOREIGN KEY (participanteId) REFERENCES participante(id)
);

CREATE TABLE equipo_reto (
    equipoId INT,
    retoId VARCHAR(255),
    estado ENUM('en_progreso', 'finalizado') DEFAULT 'en_progreso',
    PRIMARY KEY (equipoId, retoId),
    FOREIGN KEY (equipoId) REFERENCES equipos(id),
    FOREIGN KEY (retoId) REFERENCES reto(id)
);

--PROCEDIMIENTOS ALAMACENADOS

--1. Registro de participantes
DELIMITER //
CREATE PROCEDURE SP_RegistrarParticipante(
    IN p_id VARCHAR(255),
    IN p_tipo ENUM('estudiante', 'mentor'),
    IN p_nombre VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_nivelHabilidad VARCHAR(50),
    IN p_grado VARCHAR(50),
    IN p_institucion VARCHAR(255),
    IN p_tiempoDisponibleSemanal INT,
    IN p_especialidad VARCHAR(255),
    IN p_experiencia INT,
    IN p_disponibilidadHoraria VARCHAR(255)
)
BEGIN
    INSERT INTO participante (id, tipo, nombre, email, nivelHabilidad)
    VALUES (p_id, p_tipo, p_nombre, p_email, p_nivelHabilidad);

    IF p_tipo = 'estudiante' THEN
        INSERT INTO estudiante (participanteId, grado, institucion, tiempoDisponibleSemanal)
        VALUES (p_id, p_grado, p_institucion, p_tiempoDisponibleSemanal);
    ELSEIF p_tipo = 'mentor' THEN
        INSERT INTO mentorTecnico (participanteId, especialidad, experiencia, disponibilidadHoraria)
        VALUES (p_id, p_especialidad, p_experiencia, p_disponibilidadHoraria);
    END IF;
END //
DELIMITER ;

--2. FindById
DELIMITER //
CREATE PROCEDURE SP_ObtenerParticipantePorId(IN p_id VARCHAR(255))
BEGIN
    DECLARE v_tipo ENUM('estudiante', 'mentor');
    
    SELECT tipo INTO v_tipo FROM participante WHERE id = p_id;

    IF v_tipo = 'estudiante' THEN
        SELECT p.*, e.grado, e.institucion, e.tiempoDisponibleSemanal
        FROM participante p
        INNER JOIN estudiante e ON p.id = e.participanteId
        WHERE p.id = p_id;
    ELSEIF v_tipo = 'mentor' THEN
        SELECT p.*, m.especialidad, m.experiencia, m.disponibilidadHoraria
        FROM participante p
        INNER JOIN mentorTecnico m ON p.id = m.participanteId
        WHERE p.id = p_id;
    END IF;
END //
DELIMITER ;

--3. Crear reto
DELIMITER //
CREATE PROCEDURE SP_CrearReto(
    IN p_id VARCHAR(255),
    IN p_tipo ENUM('real', 'experimental'),
    IN p_titulo VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_complejidad VARCHAR(50),
    IN p_areasConocimiento TEXT,
    IN p_entidadColaboradora VARCHAR(255),
    IN p_enfoquePedagogico VARCHAR(255)
)
BEGIN
    INSERT INTO reto (id, tipo, titulo, descripcion, complejidad, areasConocimiento)
    VALUES (p_id, p_tipo, p_titulo, p_descripcion, p_complejidad, p_areasConocimiento);

    IF p_tipo = 'real' THEN
        INSERT INTO retoReal (retoId, entidadColaboradora)
        VALUES (p_id, p_entidadColaboradora);
    ELSEIF p_tipo = 'experimental' THEN
        INSERT INTO retoExperimental (retoId, enfoquePedagogico)
        VALUES (p_id, p_enfoquePedagogico);
    END IF;
END //
DELIMITER ;

--4. FindById en reto
DELIMITER //
CREATE PROCEDURE SP_ObtenerRetoPorId(IN p_id VARCHAR(255))
BEGIN
    DECLARE v_tipo ENUM('real', 'experimental');
    
    SELECT tipo INTO v_tipo FROM reto WHERE id = p_id;

    IF v_tipo = 'real' THEN
        SELECT r.*, rr.entidadColaboradora
        FROM reto r
        INNER JOIN retoReal rr ON r.id = rr.retoId
        WHERE r.id = p_id;
    ELSEIF v_tipo = 'experimental' THEN
        SELECT r.*, re.enfoquePedagogico
        FROM reto r
        INNER JOIN retoExperimental re ON r.id = re.retoId
        WHERE r.id = p_id;
    END IF;
END //
DELIMITER ;

--5. Listar retos
DELIMITER //
CREATE PROCEDURE SP_ListarRetos(IN p_tipo ENUM('real', 'experimental', NULL))
BEGIN
    SELECT * FROM reto
    WHERE p_tipo IS NULL OR tipo = p_tipo;
END //
DELIMITER ;

--6. Crear equipo
DELIMITER //
CREATE PROCEDURE SP_CrearEquipo(
    IN p_nombre VARCHAR(255),
    IN p_hackathonId VARCHAR(255)
)
BEGIN
    INSERT INTO equipos (nombre, hackathonId)
    VALUES (p_nombre, p_hackathonId);
END //
DELIMITER ;

--7. Asignar participantes a equipo
DELIMITER //
CREATE PROCEDURE SP_AsignarParticipanteAEquipo(
    IN p_equipoId INT,
    IN p_participanteId VARCHAR(255)
)
BEGIN
    INSERT INTO equipo_participantes (equipoId, participanteId)
    VALUES (p_equipoId, p_participanteId);
END //
DELIMITER ;

--8. Asignar reto a equipo
DELIMITER //
CREATE PROCEDURE SP_AsignarRetoAEquipo(
    IN p_equipoId INT,
    IN p_retoId VARCHAR(255)
)
BEGIN
    INSERT INTO equipo_reto (equipoId, retoId, estado)
    VALUES (p_equipoId, p_retoId, 'en_progreso');
END //
DELIMITER ;

--9. Retos a equipo
DELIMITER //
CREATE PROCEDURE SP_ObtenerRetosDeEquipo(IN p_equipoId INT)
BEGIN
    SELECT r.*
    FROM reto r
    INNER JOIN equipo_reto er ON r.id = er.retoId
    WHERE er.equipoId = p_equipoId;
END //
DELIMITER ;

--10. 
DELIMITER //
CREATE PROCEDURE SP_ObtenerEquiposPorReto(IN p_retoId VARCHAR(255))
BEGIN
    SELECT e.id, e.nombre, COUNT(ep.participanteId) AS miembros
    FROM equipos e
    INNER JOIN equipo_reto er ON e.id = er.equipoId
    LEFT JOIN equipo_participantes ep ON e.id = ep.equipoId
    WHERE er.retoId = p_retoId
    GROUP BY e.id, e.nombre;
END //
DELIMITER ;