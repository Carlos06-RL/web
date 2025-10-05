-- Eliminar si existe
DROP DATABASE IF EXISTS musculosdb;
CREATE DATABASE musculosdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE musculosdb;

-- ========================
--   TABLAS PRINCIPALES
-- ========================

-- Usuarios
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL, -- contraseña hasheada
  edad INT,
  peso DECIMAL(5,2),
  altura DECIMAL(5,2),
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Músculos (bíceps, pecho, abdomen, etc.)
CREATE TABLE musculos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  modelo_3d_url VARCHAR(500), -- iframe o URL del modelo 3D
  descripcion TEXT
);

-- Ejercicios asociados a cada músculo
CREATE TABLE ejercicios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  musculo_id INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NOT NULL,
  video_url VARCHAR(500) NOT NULL,
  dificultad ENUM('Principiante','Intermedio','Avanzado') DEFAULT 'Principiante',
  FOREIGN KEY (musculo_id) REFERENCES musculos(id) 
    ON UPDATE CASCADE 
    ON DELETE CASCADE
);

-- Rutinas personalizadas de usuarios
CREATE TABLE rutinas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
    ON UPDATE CASCADE 
    ON DELETE CASCADE
);

-- Relación rutina-ejercicios (muchos a muchos)
CREATE TABLE rutina_ejercicios (
  rutina_id INT NOT NULL,
  ejercicio_id INT NOT NULL,
  series INT DEFAULT 3,
  repeticiones INT DEFAULT 10,
  PRIMARY KEY (rutina_id, ejercicio_id),
  FOREIGN KEY (rutina_id) REFERENCES rutinas(id) 
    ON UPDATE CASCADE 
    ON DELETE CASCADE,
  FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id) 
    ON UPDATE CASCADE 
    ON DELETE CASCADE
);

-- Progreso del usuario
CREATE TABLE progreso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    peso DECIMAL(5,2),
    grasa DECIMAL(5,2),
    musculo DECIMAL(5,2),
    imc DECIMAL(5,2),
    observaciones VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ========
--   DATOS
-- ========

-- Insertar músculos
INSERT INTO musculos (nombre, modelo_3d_url, descripcion) VALUES
('Bíceps', 'https://sketchfab.com/models/91cf45e3cf8b490f96caa34d994c545b/embed', 'Músculo del brazo encargado de la flexión del codo.'),
('Pecho', 'https://sketchfab.com/models/65e1a062661f4af89bad2822cd3dbb08/embed', 'Músculo principal de la parte superior del torso.'),
('Abdomen', 'https://sketchfab.com/models/a6831716a15540d1889efb57305572f8/embed', 'Músculos encargados de la flexión del tronco y estabilidad del core.');

-- Ejercicios de Bíceps
INSERT INTO ejercicios (musculo_id, nombre, descripcion, video_url, dificultad) VALUES
(1, 'Curl con Barra', 'Ponte de pie con la barra en las manos. Mantén los codos pegados al torso. Sube la barra contrayendo el bíceps. Baja lentamente.', 'https://www.youtube.com/embed/mFgTFstIfFs', 'Principiante'),
(1, 'Curl con Mancuernas Alterno', 'Sujeta una mancuerna en cada mano. Levanta un brazo a la vez. Alterna sin balancear el cuerpo.', 'https://www.youtube.com/embed/qZaMpIcIswY', 'Principiante'),
(1, 'Curl Martillo', 'Agarre neutro tipo martillo. Flexiona el codo y eleva la mancuerna.', 'https://www.youtube.com/embed/j99intoPKGE', 'Intermedio'),
(1, 'Curl Concentrado', 'Siéntate en un banco. Apoya el codo en el muslo y eleva la mancuerna.', 'https://www.youtube.com/embed/Is3JRhq37o4', 'Avanzado');

-- Ejercicios de Pecho
INSERT INTO ejercicios (musculo_id, nombre, descripcion, video_url, dificultad) VALUES
(2, 'Press de Banca', 'Túmbate en un banco plano. Sujeta la barra con ambas manos. Baja hasta el pecho y empuja hacia arriba.', 'https://www.youtube.com/embed/2z8JmcrW-As', 'Intermedio'),
(2, 'Aperturas con Mancuernas', 'En banco plano con mancuernas. Abre los brazos hacia los lados y vuelve al centro.', 'https://www.youtube.com/embed/eozdVDA78K0', 'Intermedio'),
(2, 'Flexiones', 'Apoya las manos en el suelo. Baja el pecho y empuja hacia arriba.', 'https://www.youtube.com/embed/IODxDxX7oi4', 'Principiante'),
(2, 'Press Inclinado', 'Banco inclinado. Sujeta la barra y empuja hacia arriba.', 'https://www.youtube.com/embed/DbFgADa2PL8', 'Avanzado');

-- Ejercicios de Abdomen
INSERT INTO ejercicios (musculo_id, nombre, descripcion, video_url, dificultad) VALUES
(3, 'Crunch Abdominal', 'Túmbate boca arriba con rodillas flexionadas. Eleva el tronco contrayendo el abdomen.', 'https://www.youtube.com/embed/p-kdPTKDgNs', 'Principiante'),
(3, 'Plancha Frontal', 'Apoya antebrazos y puntas de los pies en el suelo. Mantén el cuerpo recto y aprieta el abdomen.', 'https://www.youtube.com/embed/pSHjTRCQxIw', 'Intermedio'),
(3, 'Elevación de Piernas', 'Acostado boca arriba, eleva ambas piernas hasta 90°. Baja lentamente sin tocar el suelo.', 'https://www.youtube.com/embed/JB2oyawG9KI', 'Intermedio'),
(3, 'Bicicleta en el Aire', 'Túmbate boca arriba, simula pedaleo llevando el codo contrario a la rodilla.', 'https://www.youtube.com/embed/Iwyvozckjak', 'Avanzado');