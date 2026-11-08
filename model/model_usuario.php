<?php
    require_once 'model_conexion.php';

    class Modelo_Usuario extends conexionBD{
        
        /**
         * Verifica las credenciales y crea la sesión en el servidor.
         * Nunca devuelve datos del usuario al navegador: solo el resultado.
         */
        public function Iniciar_Sesion($usuario, $contrasena, $ip){
            $generico = array('ok' => false, 'motivo' => 'credenciales');
            if ($usuario === '' || $contrasena === '') {
                return $generico;
            }

            $c = conexionBD::conexionPDO();
            if ($c === null) {
                return array('ok' => false, 'motivo' => 'servicio');
            }

            $clave = hash('sha256', strtolower($usuario) . '|' . $ip);
            $query = $c->prepare("SELECT intentos, bloqueado_hasta FROM login_intentos WHERE clave = ?");
            $query->execute(array($clave));
            $intento = $query->fetch(PDO::FETCH_ASSOC);
            if ($intento && $intento['bloqueado_hasta'] !== null && strtotime($intento['bloqueado_hasta']) > time()) {
                return array('ok' => false, 'motivo' => 'bloqueado');
            }

            $query = $c->prepare("SELECT * FROM usuario WHERE usu_usuario = ? LIMIT 1");
            $query->execute(array($usuario));
            $fila = $query->fetch(PDO::FETCH_ASSOC);

            // Las cuentas antiguas guardaron el hash de la contraseña pasada por htmlspecialchars.
            $escapada = htmlspecialchars($contrasena, ENT_QUOTES, 'UTF-8');
            $valida   = $fila && password_verify($contrasena, $fila['usu_contrasenia']);
            $antigua  = $fila && !$valida && password_verify($escapada, $fila['usu_contrasenia']);

            if (!$valida && !$antigua) {
                $intentos = ($intento ? (int) $intento['intentos'] : 0) + 1;
                $bloqueo  = $intentos >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
                $query = $c->prepare("REPLACE INTO login_intentos (clave, intentos, bloqueado_hasta, actualizado_en) VALUES (?, ?, ?, NOW())");
                $query->execute(array($clave, $bloqueo ? 0 : $intentos, $bloqueo));
                $this->Registrar_Auditoria($c, null, $usuario, 'LOGIN_FALLIDO', $ip);
                return $bloqueo ? array('ok' => false, 'motivo' => 'bloqueado') : $generico;
            }

            if ($fila['usu_estatus'] !== 'ACTIVO') {
                return array('ok' => false, 'motivo' => 'inactivo');
            }

            if ($antigua || password_needs_rehash($fila['usu_contrasenia'], PASSWORD_DEFAULT)) {
                $query = $c->prepare("UPDATE usuario SET usu_contrasenia = ? WHERE id_usuario = ?");
                $query->execute(array(password_hash($contrasena, PASSWORD_DEFAULT), $fila['id_usuario']));
            }

            $query = $c->prepare("DELETE FROM login_intentos WHERE clave = ?");
            $query->execute(array($clave));

            // Quien entra con una clave de ejemplo o muy común debe cambiarla antes de usar el sistema.
            $cambiarClave = false;
            if (self::Forzar_Cambio_Activo($c)) {
                $cambiarClave = (int) ($fila['debe_cambiar_clave'] ?? 0) === 1;
                if (!$cambiarClave && self::Clave_Debil($contrasena, $fila['usu_usuario'])) {
                    $c->prepare('UPDATE usuario SET debe_cambiar_clave = 1 WHERE id_usuario = ?')->execute([$fila['id_usuario']]);
                    $cambiarClave = true;
                }
            }

            $empresa = array();
            try {
                $query = $c->query("CALL SP_LISTAR_EMPRESA()");
                $empresa = $query->fetch(PDO::FETCH_ASSOC) ?: array();
                $query->closeCursor();
            } catch (PDOException $e) {
                error_log('No se pudo leer la empresa al iniciar sesion: ' . $e->getMessage());
            }

            session_regenerate_id(true);
            $_SESSION['S_ID']           = $fila['id_usuario'];
            $_SESSION['S_USU']          = $fila['usu_usuario'];
            $_SESSION['S_ROL']          = $fila['usu_rol'];
            $_SESSION['S_NOMBRE']       = $fila['usu_nombre'];
            $_SESSION['S_COMPLETOS']    = trim($fila['usu_nombre'] . ' ' . $fila['usu_apellido']);
            $_SESSION['S_FOTO']         = self::Foto_Mostrable($fila['usu_foto']);
            $_SESSION['S_FOTO_EMPRESA'] = $empresa['logo'] ?? '';
            $_SESSION['S_RAZON']        = $empresa['nombre'] ?? '';
            $_SESSION['S_ACTIVIDAD']    = time();

            $this->Registrar_Auditoria($c, $fila['id_usuario'], $fila['usu_usuario'], 'LOGIN', $ip);
            return array('ok' => true, 'cambiar_clave' => $cambiarClave);
        }

        /** Claves de ejemplo o demasiado comunes: quien entra con una debe cambiarla. */
        public const CLAVES_DEBILES = ['admin123', 'grifero123', 'admin', '123456', '1234567', '12345678', '123456789', '1234567890',
            'password', 'contraseña', 'contrasena', 'grifo123', 'qwerty', '111111', '000000', 'abc123'];
        public const CLAVE_MINIMO = 8;

        public static function Clave_Debil(string $clave, string $usuario): bool {
            $minuscula = mb_strtolower($clave);
            return in_array($minuscula, self::CLAVES_DEBILES, true) || $minuscula === mb_strtolower($usuario);
        }

        /** Mensaje de error si la clave nueva no sirve, o null si es aceptable. */
        public static function Validar_Clave_Nueva(string $nueva, string $usuario): ?string {
            if (mb_strlen($nueva) < self::CLAVE_MINIMO) {
                return 'La contraseña nueva debe tener al menos ' . self::CLAVE_MINIMO . ' caracteres.';
            }
            if (mb_strlen($nueva) > 72) {
                return 'La contraseña nueva puede tener hasta 72 caracteres.';
            }
            if (self::Clave_Debil($nueva, $usuario)) {
                return 'Esa contraseña es muy fácil de adivinar. Elija otra.';
            }
            return null;
        }

        /** Si la exigencia de cambio de clave está encendida en Configuración. */
        public static function Forzar_Cambio_Activo($c): bool {
            $query = $c->prepare("SELECT valor FROM configuracion WHERE clave = 'clave_forzar_cambio'");
            $query->execute();
            $valor = $query->fetchColumn();
            return $valor === false || $valor === null || $valor === '1';
        }

        /** Marca la cuenta para que cambie su clave al entrar (clave asignada por un administrador). */
        public static function Marcar_Cambio_Clave(string $usuario): void {
            $c = conexionBD::conexionPDO();
            if (self::Forzar_Cambio_Activo($c)) {
                $c->prepare('UPDATE usuario SET debe_cambiar_clave = 1 WHERE usu_usuario = ?')->execute([$usuario]);
            }
        }

        /**
         * El usuario en sesión cambia su propia clave. Comprueba la actual con un límite de intentos
         * (5 fallos bloquean 15 minutos), exige una clave nueva aceptable y distinta, y quita la marca de cambio obligatorio.
         */
        public function Cambiar_Clave_Propia(int $id, string $actual, string $nueva, string $ip): array {
            $c = conexionBD::conexionPDO();
            $query = $c->prepare('SELECT usu_usuario, usu_contrasenia FROM usuario WHERE id_usuario = ?');
            $query->execute([$id]);
            $fila = $query->fetch(PDO::FETCH_ASSOC);
            if (!$fila) {
                return ['success' => false, 'message' => 'Su usuario no existe.'];
            }
            $clave = hash('sha256', 'cambio_clave|' . $id);
            $query = $c->prepare('SELECT intentos, bloqueado_hasta FROM login_intentos WHERE clave = ?');
            $query->execute([$clave]);
            $intento = $query->fetch(PDO::FETCH_ASSOC);
            if ($intento && $intento['bloqueado_hasta'] !== null && strtotime($intento['bloqueado_hasta']) > time()) {
                return ['success' => false, 'message' => 'Demasiados intentos. Espere 15 minutos e inténtelo de nuevo.'];
            }
            $escapada = htmlspecialchars($actual, ENT_QUOTES, 'UTF-8');
            if (!password_verify($actual, $fila['usu_contrasenia']) && !password_verify($escapada, $fila['usu_contrasenia'])) {
                $intentos = ($intento ? (int) $intento['intentos'] : 0) + 1;
                $bloqueo = $intentos >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
                $c->prepare('REPLACE INTO login_intentos (clave, intentos, bloqueado_hasta, actualizado_en) VALUES (?, ?, ?, NOW())')
                  ->execute([$clave, $bloqueo ? 0 : $intentos, $bloqueo]);
                $this->Registrar_Auditoria($c, $id, $fila['usu_usuario'], 'CAMBIO_CLAVE_FALLIDO', $ip);
                return ['success' => false, 'message' => 'La contraseña actual no es correcta.'];
            }
            $error = self::Validar_Clave_Nueva($nueva, $fila['usu_usuario']);
            if ($error === null && hash_equals($actual, $nueva)) {
                $error = 'La contraseña nueva debe ser distinta de la actual.';
            }
            if ($error !== null) {
                return ['success' => false, 'message' => $error];
            }
            $c->prepare('UPDATE usuario SET usu_contrasenia = ?, debe_cambiar_clave = 0, updated_at = NOW() WHERE id_usuario = ?')
              ->execute([password_hash($nueva, PASSWORD_DEFAULT), $id]);
            $c->prepare('DELETE FROM login_intentos WHERE clave = ?')->execute([$clave]);
            $this->Registrar_Auditoria($c, $id, $fila['usu_usuario'], 'CAMBIO_CLAVE', $ip);
            return ['success' => true, 'message' => 'Contraseña actualizada.'];
        }

        private function Registrar_Auditoria($c, $id_usuario, $usuario, $accion, $ip){
            $query = $c->prepare("INSERT INTO auditoria (id_usuario, usuario, accion, ip) VALUES (?, ?, ?, ?)");
            $query->execute(array($id_usuario, $usuario, $accion, $ip));
        }

        /**
         * Impide crear administradores o tocar sus cuentas sin serlo, asignar roles inexistentes
         * y dejar el sistema sin administradores activos. Responde 403 y termina si no procede.
         */
        public function Validar_Cambio_Rol($id, $rolNuevo){
            $c = conexionBD::conexionPDO();
            $rolActual = null;
            if ($id !== null) {
                $query = $c->prepare("SELECT usu_rol FROM usuario WHERE id_usuario = ?");
                $query->execute(array($id));
                $rolActual = $query->fetchColumn();
            }
            if ($rolNuevo !== null) {
                $existe = $c->prepare("SELECT COUNT(*) FROM roles WHERE codigo = ?");
                $existe->execute(array($rolNuevo));
                if (!$existe->fetchColumn()) {
                    denegar_acceso(403, 'El rol indicado no existe.');
                }
            }
            if (!es_administrador() && ($rolNuevo === 'ADMINISTRADOR' || $rolActual === 'ADMINISTRADOR')) {
                denegar_acceso(403, 'Solo un administrador puede gestionar cuentas de administrador.');
            }
            if ($rolActual === 'ADMINISTRADOR' && $rolNuevo !== null && $rolNuevo !== 'ADMINISTRADOR' && $this->Administradores_Activos($id) === 0) {
                denegar_acceso(403, 'Debe quedar al menos un administrador activo.');
            }
        }

        public function Validar_Cambio_Estatus($id, $estatus){
            $this->Validar_Cambio_Rol($id, null);
            if ($estatus === 'ACTIVO') {
                return;
            }
            if ($id === (int) $_SESSION['S_ID']) {
                denegar_acceso(403, 'No puede desactivar su propia cuenta.');
            }
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT usu_rol FROM usuario WHERE id_usuario = ?");
            $query->execute(array($id));
            if ($query->fetchColumn() === 'ADMINISTRADOR' && $this->Administradores_Activos($id) === 0) {
                denegar_acceso(403, 'Debe quedar al menos un administrador activo.');
            }
        }

        private function Administradores_Activos($excepto){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT COUNT(*) FROM usuario WHERE usu_rol = 'ADMINISTRADOR' AND usu_estatus = 'ACTIVO' AND id_usuario <> ?");
            $query->execute(array($excepto));
            return (int) $query->fetchColumn();
        }

        /** Las cuentas antiguas sin foto guardaron la ruta de la carpeta, que no es una imagen. */
        public static function Foto_Mostrable($foto){
            return ($foto === null || $foto === '' || substr($foto, -1) === '/') ? 'img/avatar.png' : $foto;
        }

        public function Obtener_Foto_Usuario($id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT usu_foto FROM usuario WHERE id_usuario = ?");
            $query->execute(array($id));
            return $query->fetchColumn();
        }

        public function Listar_Usuario(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_USUARIO()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                unset($resp['usu_contrasenia']);
                $resp['usu_foto'] = self::Foto_Mostrable($resp['usu_foto'] ?? null);
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Cargar_Select_Area(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SELECT_AREA()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Cargar_Select_Area_Solo(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SELECT_AREA_SOLO()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Cargar_Select_Empleado(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SELECT_EMPLEADO()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Registrar_Usuario($dni,$nombre,$apelli,$correo,$tele,$dire,$ruta,$usu,$contra,$rol){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_USUARIO(?,?,?,?,?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$dni);
            $query ->bindParam(2,$nombre);
            $query ->bindParam(3,$apelli);
            $query ->bindParam(4,$correo);
            $query ->bindParam(5,$tele);
            $query ->bindParam(6,$dire);
            $query ->bindParam(7,$ruta);
            $query ->bindParam(8,$usu);
            $query ->bindParam(9,$contra);
            $query ->bindParam(10,$rol);
            $query->execute();
            if($row = $query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Usuario($id,$dni, $nombre, $apelli, $correo, $tele, $dire, $ruta,$usu,$rol){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_USUARIO(?,?,?,?,?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$dni);
            $query ->bindParam(3,$nombre);
            $query ->bindParam(4,$apelli);
            $query ->bindParam(5,$correo);
            $query ->bindParam(6,$tele);
            $query ->bindParam(7,$dire);
            $query ->bindParam(8,$ruta);
            $query ->bindParam(9,$usu);
            $query ->bindParam(10,$rol);
            $query->execute();
            if($row = $query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Usuario_Contra($id,$con){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_USUARIO_CONTRA(?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$con);
            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Usuario_Estatus($id,$estatus){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_USUARIO_ESTATUS(?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$estatus);
            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
         
        public function Cargar_Select_Datos_Seguimiento($numero,$dni){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SEGUIMIENTO_TRAMITE(?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$numero);
            $query ->bindParam(2,$dni);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Traer_Datos_Detalle_Seguimiento($codigo){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE(?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$codigo);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Listar_notificacion_tramite($idarea){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_NOTIFICACION_TRAMITE(?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$idarea);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_reuniones_hoy(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_REUNIONES_HOY()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_reuniones(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_REUNIONES()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_expedientes(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_EXPEDIENTES()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_expedientes_proceso(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_EXPEDIENTES_PROCESO()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_expedientes_tramite(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_EXPEDIENTES_TRAMITE()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_expedientes_observados(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_EXPEDIENTES_OBSERVADOS()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_expedientes_finalizados(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_EXPEDIENTES_FINALIZADOS()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_clientes(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_CLIENTES()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_ingresos_hoy(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_INGRESOS_HOY()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_gastos_hoy(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_GASTOS_HOY()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_ingresos_mensual(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_INGRESOS_MENSUAL()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function listar_total_gastos_mensual(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TOTAL_GASTOS_MENSUAL()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
    }




?>