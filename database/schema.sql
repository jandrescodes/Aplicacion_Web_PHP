-- Estructura de la base de datos para el Sistema de Gestión Empresarial

CREATE TABLE IF NOT EXISTS `tbl-puestos` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Nombredelpuesto` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`), 
  UNIQUE KEY `Nombredelpuesto` (`Nombredelpuesto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl-empleados` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Primernombre` varchar(255) NOT NULL,
  `Segundonombre` varchar(255) DEFAULT NULL,
  `Primerapellido` varchar(255) NOT NULL,
  `Segundoapellido` varchar(255) NOT NULL,
  `Foto` varchar(255) DEFAULT 'user-default.jpg',
  `CV` varchar(255) DEFAULT 'cv_default.pdf',
  `Idpuesto` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Idpuesto` (`Idpuesto`),
  CONSTRAINT `tbl-empleados_ibfk_1` FOREIGN KEY (`Idpuesto`) REFERENCES `tbl-puestos` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl-usuarios` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Nombreusuario` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Correo` varchar(255) NOT NULL,
  `remember_token` CHAR(64) DEFAULT NULL,
  `remember_token_expires` DATETIME DEFAULT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Correo` (`Correo`),
  INDEX `idx_remember_token` (`remember_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `audit_log` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT NULL,
  `action`     ENUM('create','update','delete') NOT NULL,
  `entity`     ENUM('employee','position','user') NOT NULL,
  `entity_id`  INT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_entity` (`entity`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
