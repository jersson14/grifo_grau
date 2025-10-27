<?php
    require_once 'model_conexion.php';

    class Modelo_Usuario extends conexionBD{
        
        public function Verificar_Usuario($usu,$con){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_VERIFICAR_USUARIO(?)";
            $arreglo = array();
            $query = $c->prepare($sql);
            $query->bindParam(1,$usu);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                if(password_verify($con,$resp['usu_contrasenia'])){
                    $arreglo[]=$resp;
                }
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        } 
        public function Listar_Usuario(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_USUARIO()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
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