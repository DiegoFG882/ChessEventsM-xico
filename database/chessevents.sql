-- ============================================================================
-- BASE DE DATOS NORMALIZADA (3FN) - CHESSEVENTS MÉXICO (POSTGRESQL)
-- ============================================================================

DROP TABLE IF EXISTS inscripciones CASCADE;
DROP TABLE IF EXISTS torneos CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS ritmos CASCADE;
DROP TABLE IF EXISTS estados CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

-- 1. CATÁLOGO DE ROLES
CREATE TABLE roles (
    id_rol SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO roles (nombre_rol) VALUES 
('admin'),        -- ID 1: Administrador general
('organizador'),  -- ID 2: Publica y gestiona torneos
('jugador');      -- ID 3: Se registra e inscribe a torneos

-- 2. CATÁLOGO DE ESTADOS DE LA REPÚBLICA
CREATE TABLE estados (
    id_estado SERIAL PRIMARY KEY,
    nombre_estado VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO estados (nombre_estado) VALUES 
('Aguascalientes'), ('Baja California'), ('Baja California Sur'), ('Campeche'),
('Chiapas'), ('Chihuahua'), ('Ciudad de México'), ('Coahuila'), ('Colima'),
('Durango'), ('Estado de México'), ('Guanajuato'), ('Guerrero'), ('Hidalgo'),
('Jalisco'), ('Michoacán'), ('Morelos'), ('Nayarit'), ('Nuevo León'), ('Oaxaca'),
('Puebla'), ('Querétaro'), ('Quintana Roo'), ('San Luis Potosí'), ('Sinaloa'),
('Sonora'), ('Tabasco'), ('Tamaulipas'), ('Tlaxcala'), ('Veracruz'),
('Yucatán'), ('Zacatecas');

-- 3. CATÁLOGO DE RITMOS DE JUEGO
CREATE TABLE ritmos (
    id_ritmo SERIAL PRIMARY KEY,
    nombre_ritmo VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO ritmos (nombre_ritmo) VALUES 
('Clásico'), 
('Rápido'), 
('Blitz');

-- 4. TABLA DE USUARIOS
CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    id_rol INT NOT NULL DEFAULT 3 REFERENCES roles(id_rol),
    id_estado INT NOT NULL REFERENCES estados(id_estado),
    nombre_completo VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(15) NOT NULL,
    id_fide VARCHAR(20) NULL,
    elo_actual INT DEFAULT 0,
    password_hash VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Evita correos duplicados aunque cambien mayúsculas/minúsculas
CREATE UNIQUE INDEX idx_usuarios_correo_lower ON usuarios (LOWER(correo));

-- Usuario Administrador por defecto (Contraseña inicial: password — cambiar en Mi Perfil tras desplegar)
INSERT INTO usuarios (id_rol, id_estado, nombre_completo, correo, telefono, id_fide, elo_actual, password_hash)
VALUES (
    1, 
    11, -- Estado de México
    'Diego Flores Gutiérrez', 
    'diegofloresgutierrez834@gmail.com', 
    '5542342512', 
    '5110981', 
    1850, 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);

-- 5. TABLA DE TORNEOS
CREATE TABLE torneos (
    id_torneo SERIAL PRIMARY KEY,
    id_organizador INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    id_estado INT NOT NULL REFERENCES estados(id_estado),
    id_ritmo INT NOT NULL REFERENCES ritmos(id_ritmo),
    nombre VARCHAR(150) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    url_maps TEXT NULL,
    ritmo_detalle VARCHAR(30) NOT NULL,
    rondas INT NOT NULL DEFAULT 5,
    fecha_evento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    cupo INT NOT NULL DEFAULT 150 CHECK (cupo >= 2),
    costo DECIMAL(10, 2) DEFAULT 0.00,
    es_fide BOOLEAN DEFAULT FALSE,
    whatsapp_contacto VARCHAR(15) NOT NULL,
    red_social TEXT NULL,
    archivo_contenido TEXT NULL,
    archivo_nombre VARCHAR(150) NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. TABLA DE INSCRIPCIONES
CREATE TABLE inscripciones (
    id_inscripcion SERIAL PRIMARY KEY,
    id_torneo INT NOT NULL REFERENCES torneos(id_torneo) ON DELETE CASCADE,
    id_jugador INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    categoria VARCHAR(50) DEFAULT 'Libre',
    estado_inscripcion VARCHAR(30) DEFAULT 'Confirmada',
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_torneo, id_jugador)
);