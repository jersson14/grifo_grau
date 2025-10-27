<?php
    require_once 'model_conexion.php';

    class Modelo_Pagos extends conexionBD{
        

        public function Listar_Pagos(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_PAGOS()";
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Listar_pagos_filtro($fechaini,$fechafin,$servicio){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_PAGOS_FILTRO(?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->bindParam(1,$fechaini);
            $query->bindParam(2,$fechafin);
            $query->bindParam(3,$servicio);

            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Realizar_pago($id, $pagar, $igv, $subtotal, $saldo, $descrip, $idusu) {
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REALIZAR_PAGO(?,?,?,?,?,?,?)";
            $query = $c->prepare($sql);
            
            $query->bindParam(1, $id);
            $query->bindParam(2, $pagar);
            $query->bindParam(3, $igv);
            $query->bindParam(4, $subtotal);
            $query->bindParam(5, $saldo);
            $query->bindParam(6, $descrip);
            $query->bindParam(7, $idusu);
        
            $query->execute();
            
            $resultado = $query->fetch(PDO::FETCH_ASSOC); // Solo un registro esperado (el id_ingreso)
            
            conexionBD::cerrar_conexion();
        
            return $resultado['id_ingreso']; // Retornamos solo el ID que te interesa
        }
        
        public function Eliminar_Detalle_requisito($id){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_ELIMINAR_DETALLE_REQUISITOS(?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
    
            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Cargar_Select_Servicios(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SERVICIOS()";
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
        public function Listar_requisito_por_servicio($id_servicio){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_VER_DETALLE_REQUISITOS(?)";
            $arreglo = array();
            $query = $c->prepare($sql);
            $query->bindParam(1, $id_servicio); // Aquí estabas usando $id en lugar de $id_obra
        
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // No necesitas el formato "data" para el uso que le estás dando
            // Simplemente devuelve el array de resultados
            return $resultado;
            
            // Esta línea nunca se ejecuta porque está después del return
            // conexionBD::cerrar_conexion();
        }
        public function Eliminar_expediente($id){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_ELIMINAR_EXPEDIENTE(?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
    
            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Listar_Expedientes_archivados(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_EXPEDIENTES_ARCHIVADOS()";
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }

        public function Listar_expedientes_filtro_archivados($fechaini,$fechafin,$servicio){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_EXPEDIENTES_FILTRO_ARCHIVADOS(?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->bindParam(1,$fechaini);
            $query->bindParam(2,$fechafin);
            $query->bindParam(3,$servicio);

            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Estado($id,$esta,$motivo,$idusu){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_ESTADO(?,?,?,?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$esta);
            $query ->bindParam(3,$motivo);
            $query ->bindParam(4,$idusu);

            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Listar_Historial_Espedientes($id){
            $c = conexionBD::conexionPDO();
            $arreglo = array();
            $sql = "CALL SP_LISTA_HISTORIAL_EXPEDIENTES(?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        
        }
        public function Listar_Requisitos_Espedientes($id){
            $c = conexionBD::conexionPDO();
            $arreglo = array();
            $sql = "CALL SP_LISTA_REQUISITOS_EXPEDIENTES(?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Anular_pago($id,$idusu,$motivo_anulacion){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_ANULAR_PAGO(?,?,?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$idusu);
            $query ->bindParam(3,$motivo_anulacion);

            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Listar_historial_pagos($id) {
            $c = conexionBD::conexionPDO();
            $arreglo = ["data" => []]; // ✅ Asegura que la clave "data" siempre existe
            $sql = "CALL SP_LISTA_HISTORIAL_PAGOS(?)";
            $query = $c->prepare($sql);
            $query->bindParam(1, $id);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        
            if (!empty($resultado)) { // ✅ Solo llena "data" si hay resultados
                $arreglo["data"] = $resultado;
            }
        
            conexionBD::cerrar_conexion();
            return $arreglo;
        }
        public function Anular_pago_historial($id,$idusu,$motivo_anulacion,$monto_anulado){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_ANULAR_PAGO_HISTORIAL(?,?,?,?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$idusu);
            $query ->bindParam(3,$motivo_anulacion);
            $query ->bindParam(4,$monto_anulado);

            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Listar_pagos_expediente($id_pago) {
            $c = conexionBD::conexionPDO();
            $arreglo = ["data" => []]; // ✅ Asegura que la clave "data" siempre existe
            $sql = "CALL SP_LISTA_PAGOS_EXPEDIENTE(?)";
            $query = $c->prepare($sql);
            $query->bindParam(1, $id_pago);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        
            if (!empty($resultado)) { // ✅ Solo llena "data" si hay resultados
                $arreglo["data"] = $resultado;
            }
        
            conexionBD::cerrar_conexion();
            return $arreglo;
        }

    }




?>