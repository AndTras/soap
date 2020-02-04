<?php

//Este codigo permite que las consultas puedan tomar mas tiempo
ini_set('max_execution_time', 300);
set_time_limit(300);

class conexion {

    public $query;
    public $result;

    function sql($query){

        //parametros para la conexion a base de datos
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "soap";

        //codigo para conexion a base de datos
        $conn = new mysqli($servername, $username, $password,$dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }else{
            $this->result = $conn->query($query);
            return true;
        }

        $conn->close();
    }

    function get_result (){
        return $this->result;
    }
}

?>