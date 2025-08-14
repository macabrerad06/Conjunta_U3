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
    equipoId VARCHAR(255),
    retoId VARCHAR(255),
    estado ENUM('estudiante', 'mentor') DEFAULT 'en_progreso',
    PRIMARY KEY (equipoId, retoId),
    FOREIGN KEY (equipoId) REFERENCES equipos(id),
    FOREIGN KEY (retoId) REFERENCES reto(id)
);