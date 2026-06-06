-- Datos iniciales para el Sistema de Gestión Empresarial

-- Seeders para tbl-puestos
INSERT INTO `tbl-puestos` (`ID`, `Nombredelpuesto`) VALUES(1, 'Programador Semi Sr.');
INSERT INTO `tbl-puestos` (`ID`, `Nombredelpuesto`) VALUES(2, 'Programador Sr.');
INSERT INTO `tbl-puestos` (`ID`, `Nombredelpuesto`) VALUES(3, 'Líder de Proyectos');
INSERT INTO `tbl-puestos` (`ID`, `Nombredelpuesto`) VALUES(4, 'Desarrolador de base de datos');
INSERT INTO `tbl-puestos` (`ID`, `Nombredelpuesto`) VALUES(5, 'Programador Fullstack');
INSERT INTO `tbl-puestos` (`ID`, `Nombredelpuesto`) VALUES(6, 'Diseñador Web');

-- Seeders para tbl-usuarios
-- Contraseñas en texto plano (para referencia de desarrollo):
--   ID 1 - Administrador: 1234
--   ID 2 - Kevin 11:      7896
--   ID 3 - Hacker:        Hackerman15
INSERT INTO `tbl-usuarios` (`ID`, `Nombreusuario`, `Password`, `Correo`, `is_admin`) VALUES(1, 'Administrador', '$2y$10$knFNGqVYR4kg.juQxc64QOYKiJPMFwBmxzyyGOnSzHM.llLuIkbUS', 'admin@gmail.com', 1);
INSERT INTO `tbl-usuarios` (`ID`, `Nombreusuario`, `Password`, `Correo`, `is_admin`) VALUES(2, 'Kevin 11', '$2y$10$aI8VJZ.14CL73HqSu/zEZOhsmjaxDoK18jazP9qfeaNl07uF5fjw6', 'Juanito@gmail.com', 0);
INSERT INTO `tbl-usuarios` (`ID`, `Nombreusuario`, `Password`, `Correo`, `is_admin`) VALUES(3, 'Hacker', '$2y$10$wyLp5t2/TO9KLxcDQSKCTOj4q7/ujE8sfLnrr3qgz8kf4bxmiLEk6', 'Hackandslash@hotmail.com', 0);

-- Seeders para tbl-empleados (Opcional, datos de ejemplo)
INSERT INTO `tbl-empleados` (`ID`, `Primernombre`, `Segundonombre`, `Primerapellido`, `Segundoapellido`, `Idpuesto`, `Fecha`) VALUES(1, 'Charles', '', 'W.', 'Bachman', 2, '2020-04-20');
INSERT INTO `tbl-empleados` (`ID`, `Primernombre`, `Segundonombre`, `Primerapellido`, `Segundoapellido`, `Idpuesto`, `Fecha`) VALUES(2, 'Eduard', 'Frank', 'Codd', 'Smith', 6, '2017-10-01');
