<?php
/** Procedimientos usados por los módulos activos. Provisionales, igual que 000_base_provisional.sql. */
return [
    "CREATE PROCEDURE SP_LISTAR_USUARIO()
     SELECT id_usuario, dni_usuario, usu_nombre, usu_apellido, usu_email, usu_telefono, usu_direccion,
            usu_foto, usu_usuario, usu_usuario AS USUARIO, usu_rol, usu_estatus, created_at,
            DATE_FORMAT(created_at, '%d/%m/%Y') AS fecha_formateada
     FROM usuario ORDER BY id_usuario DESC",

    "CREATE PROCEDURE SP_REGISTRAR_USUARIO(
        IN p_dni VARCHAR(20), IN p_nombre VARCHAR(100), IN p_apellido VARCHAR(100), IN p_email VARCHAR(150),
        IN p_telefono VARCHAR(30), IN p_direccion VARCHAR(255), IN p_foto VARCHAR(255),
        IN p_usuario VARCHAR(60), IN p_contra VARCHAR(255), IN p_rol VARCHAR(30))
     BEGIN
        IF EXISTS (SELECT 1 FROM usuario WHERE usu_usuario = p_usuario) THEN
            SELECT 2;
        ELSE
            INSERT INTO usuario (dni_usuario, usu_nombre, usu_apellido, usu_email, usu_telefono, usu_direccion,
                                 usu_foto, usu_usuario, usu_contrasenia, usu_rol)
            VALUES (p_dni, p_nombre, p_apellido, p_email, p_telefono, p_direccion, p_foto, p_usuario, p_contra, p_rol);
            SELECT 1;
        END IF;
     END",

    "CREATE PROCEDURE SP_MODIFICAR_USUARIO(
        IN p_id INT, IN p_dni VARCHAR(20), IN p_nombre VARCHAR(100), IN p_apellido VARCHAR(100), IN p_email VARCHAR(150),
        IN p_telefono VARCHAR(30), IN p_direccion VARCHAR(255), IN p_foto VARCHAR(255),
        IN p_usuario VARCHAR(60), IN p_rol VARCHAR(30))
     BEGIN
        IF EXISTS (SELECT 1 FROM usuario WHERE usu_usuario = p_usuario AND id_usuario <> p_id) THEN
            SELECT 2;
        ELSE
            UPDATE usuario SET dni_usuario = p_dni, usu_nombre = p_nombre, usu_apellido = p_apellido, usu_email = p_email,
                   usu_telefono = p_telefono, usu_direccion = p_direccion, usu_foto = p_foto, usu_usuario = p_usuario,
                   usu_rol = p_rol, updated_at = NOW()
            WHERE id_usuario = p_id;
            SELECT 1;
        END IF;
     END",

    "CREATE PROCEDURE SP_MODIFICAR_USUARIO_CONTRA(IN p_id INT, IN p_contra VARCHAR(255))
     UPDATE usuario SET usu_contrasenia = p_contra, updated_at = NOW() WHERE id_usuario = p_id",

    "CREATE PROCEDURE SP_MODIFICAR_USUARIO_ESTATUS(IN p_id INT, IN p_estatus VARCHAR(15))
     UPDATE usuario SET usu_estatus = p_estatus, updated_at = NOW() WHERE id_usuario = p_id",

    "CREATE PROCEDURE SP_LISTAR_EMPRESA()
     SELECT id_empresa, nombre, email, codigo, telefono, direccion, logo FROM empresa",

    "CREATE PROCEDURE SP_MODIFICAR_EMPRESA(
        IN p_id INT, IN p_nombre VARCHAR(200), IN p_email VARCHAR(150), IN p_codigo VARCHAR(30),
        IN p_telefono VARCHAR(30), IN p_direccion VARCHAR(255))
     UPDATE empresa SET nombre = p_nombre, email = p_email, codigo = p_codigo, telefono = p_telefono, direccion = p_direccion
     WHERE id_empresa = p_id",

    "CREATE PROCEDURE SP_MODIFICAR_EMPRESA_FOTO(IN p_id INT, IN p_logo VARCHAR(255))
     UPDATE empresa SET logo = p_logo WHERE id_empresa = p_id",
];
