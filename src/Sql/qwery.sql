CREATE DATABASE IF NOT EXISTS eduhack;
USE eduhack;

-- TABLAS

CREATE TABLE participante (
    id VARCHAR(100) PRIMARY KEY,
    tipo ENUM('estudiante', 'mentor') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nivelHabilidad VARCHAR(50)
);

CREATE TABLE estudiante (
    participanteId VARCHAR(255) PRIMARY KEY,
    grado VARCHAR(50),
    institucion VARCHAR(255),
    tiempoDisponibleSemanal INT,
    FOREIGN KEY (participanteId) REFERENCES participantes(id)
);

CREATE TABLE mentorTecnico (
    participanteId VARCHAR(255) PRIMARY KEY,
    especialidad VARCHAR(255),
    experiencia INT,
    disponibilidadHoraria VARCHAR(255),
    FOREIGN KEY (participanteId) REFERENCES participantes(id)
);

CREATE TABLE reto (
    id VARCHAR(255) PRIMARY KEY,
    tipo ENUM('real', 'experimental') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    complejidad VARCHAR(50),
    areasConocimiento TEXT
);

CREATE TABLE retoReal (
    retoId VARCHAR(255) PRIMARY KEY,
    entidadColaboradora VARCHAR(255),
    FOREIGN KEY (retoId) REFERENCES retos(id)
);

CREATE TABLE retoExperimental (
    retoId VARCHAR(255) PRIMARY KEY,
    enfoquePedagogico VARCHAR(255),
    FOREIGN KEY (retoId) REFERENCES retos(id)
);

CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    hackathonId VARCHAR(50) NOT NULL
);

CREATE TABLE equipos_participantes (
    equipoId INT,
    participanteId VARCHAR(255),
    PRIMARY KEY (equipoId, participanteId),
    FOREIGN KEY (equipoId) REFERENCES equipos(id),
    FOREIGN KEY (participanteId) REFERENCES participantes(id)
);

CREATE TABLE equipos_retos (
    equipoId VARCHAR(255),
    retoId VARCHAR(255),
    estado ENUM('estudiante', 'mentor') DEFAULT 'en_progreso',
    PRIMARY KEY (equipoId, retoId),
    FOREIGN KEY (equipoId) REFERENCES equipos(id),
    FOREIGN KEY (retoId) REFERENCES retos(id)
);