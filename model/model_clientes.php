<?php
    require_once 'model_conexion.php';

    class Modelo_Clientes extends conexionBD{
        

        public function Listar_Clientes(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_CLIENTES()";
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
      
        public function Modificar_Clientes($id,$tipo,$nro,$nombre,$apellido,$celular,$telefono,$direccion,$email,$iddistri,$ober){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_CLIENTES(?,?,?,?,?,?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$tipo);
            $query ->bindParam(3,$nro);
            $query ->bindParam(4,$nombre);
            $query ->bindParam(5,$apellido);
            $query ->bindParam(6,$celular);
            $query ->bindParam(7,$telefono);
            $query ->bindParam(8,$direccion);
            $query ->bindParam(9,$email);
            $query ->bindParam(10,$iddistri);
            $query ->bindParam(11,$ober);

            $query->execute();
            if($row = $query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        public function Eliminar_Cliente($id){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_ELIMINAR_CLIENTE(?)";
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
        public function Cargar_Select_Area(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SELECT_AREA()";
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