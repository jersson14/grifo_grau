<?php
    require_once 'model_conexion.php';

    class Modelo_Gastos extends conexionBD{
        

        public function listar_Gastos(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_GASTOS()";
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
        public function Listar_gastos_fechas($indica,$fechainicio,$fechafin){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_GASTOS_FECHAS(?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->bindParam(1,$indica);
            $query->bindParam(2,$fechainicio);
            $query->bindParam(3,$fechafin);

            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
         public function Registrar_Gastos($indi,$cantidad,$monto,$descri,$idusu){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_GASTOS(?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$indi);
            $query ->bindParam(2,$cantidad);
            $query ->bindParam(3,$monto);
            $query ->bindParam(4,$descri); 
            $query ->bindParam(5,$idusu);

            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Gastos($id,$indi,$cantidad,$monto,$descri,$idusu){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_GASTOS(?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$indi);
            $query ->bindParam(3,$cantidad);
            $query ->bindParam(4,$monto);
            $query ->bindParam(5,$descri);  
            $query ->bindParam(6,$idusu);

            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Anular_Gastos($id,$descri,$idusu){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_ANULAR_GASTOS(?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$descri);  
            $query ->bindParam(3,$idusu);

            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
      
    }




?>