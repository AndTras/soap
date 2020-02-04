<?php

    require('conexion.php');
    require('api.php');
    $html = "";
    $sqlQuery = new conexion();
    $api = new api();

    //se valida si se esta recibiendo el parametro de consulta
    if(isset($_POST['start'])){

        $date = $_POST['start'];

        // se consulta en base de datos si la fecha ya fue registrada
        $sql = "SELECT date FROM soap_dates where date = '$date'"; 
        $sqlQuery->sql($sql);
        $consulta = $sqlQuery->get_result();

        if ($consulta->num_rows > 0) {

            // si ya fue registrada la fecha es porque ya se han guardado registros, a continuacion, procede a buscar esos registros 
            $sql = "SELECT archivos.id, archivos.name, tipo_archivo.formato FROM archivos INNER JOIN tipo_archivo ON archivos.id = tipo_archivo.id AND archivos.date = tipo_archivo.date where archivos.date = '$date'"; 
            $sqlQuery->sql($sql);
            $consulta = $sqlQuery->get_result();

            if ($consulta->num_rows > 0) {

                //codigo para armar los registros
                
                $html = "<table class='table table-striped table-dark table-responsive'>
                        <thead>
                                <tr>
                                    <th class='col-md-3'> id </th>
                                    <th class='col-md-3'> Nombre </th>
                                    <th class='col-md-3'> Formato </th>
                                    <th class='col-md-3' style='text-align:center'>
                                        <form method='post' action='index.php'>
                                            <input type='hidden' name='dateFormato' value='".$date."'>
                                            <button  class='btn btn-outline-light'>
                                                Total tipo documento
                                            </button >
                                        </form>
                                    </th>
                                </tr>
                        </thead>
                        <tbody>";
                        
                        while($row = $consulta->fetch_assoc()) {
                            $html .= "<tr>
                                        <td class='col-md-3'>".$row['id']."</td>
                                        <td class='col-md-3'> <span class='d-inline-block text-truncate' style='max-width: 40%;'>".$row['name']."</span></td>
                                        <td class='col-md-6' colspan='2'>".$row['formato']."</td>
                                    </tr>";
                        }

                $html .=  " </tbody><table>";
        
            }else {
                $html = "No hay archivos";
            }

        }else {
        
            //en dado caso que la fecha no haya sido registra, a continuacion se procedea a guardarla

            $sql = "INSERT INTO soap_dates (date)VALUES ('$date')";
            $sqlQuery->sql($sql);
            $array = $api->get($date);

            if(count($array) > 0){
                
                // se pinta la tabla con los datos

                $html = "<table class='table table-striped table-dark  table-responsive'>
                            <thead>
                                    <tr>
                                    <th class='col-md-3'> id </th>
                                    <th class='col-md-3'> Nombre </th>
                                    <th class='col-md-3'> Formato </th>
                                    <th class='col-md-3' style='text-align:center'>
                                        <form method='post' action='index.php'>
                                            <input type='hidden' name='dateFormato' value='".$date."'>
                                            <button  class='btn btn-outline-light'>
                                                Total tipo documento
                                            </button >
                                        </form>
                                    </th>
                                    </tr>
                            </thead>
                        <tbody>";

                        foreach ($array['Archivo'] as $key => $value) {
                
                            if (array_key_exists('@attributes', $value)) {
                                $value = $value['@attributes'];
                            }
                            
                            $id = $value['Id'];
                            $name = $value['Nombre'];

                            // codigo para separar el nombre del documento de su correspondiente formato 

                            $name = explode('.',$name);
                            $con = count($name)-1;
                            $nom ='';

                            for ($x = 0; $x <= $con-1; $x++) {
                                $nom .= $name[$x].'.';
                            }
                            
                            // se procede a guardar los registros, para luego poder consultarlos de una manera mas rapida

                            $sql = "INSERT INTO archivos (id,name,date) VALUES ('$id','$nom','$date')";
                            
                            if ($sqlQuery->sql($sql) === TRUE) {
                        
                                $sql = "INSERT INTO tipo_archivo (id,formato,date) VALUES ('$id','$name[$con]','$date')";
                                
                                if ($sqlQuery->sql($sql) === TRUE) {
                                    $html .= "<tr>
                                                <td class='col-md-3'>".$id."</td>
                                                <td class='col-md-3'><span class='d-inline-block text-truncate' style='max-width: 80%;'>".$nom."</span> </td>
                                                <td class='col-md-6' colspan='2'>".$name[$con]."</td>
                                            </tr>";
                                }

                            } 
                        }
                $html .= " </tbody><table>";
            }else{
                echo "no hay archivos";
            }
        }
    }else {
        $html = 0;
        
        // consulta cantidad de archivos por tipo documento  

        if(isset($_POST['dateFormato'])){
            $date = $_POST['dateFormato'];
            $sql = "SELECT formato , COUNT(formato) as cantidad FROM tipo_archivo where date = '$date' GROUP BY formato ORDER BY cantidad DESC "; 
            $sqlQuery->sql($sql);
            $consulta = $sqlQuery->get_result();

            if ($consulta->num_rows > 0) {

                $html = "<table class='table table-striped table-dark  table-responsive'>
                            <thead>
                                <tr>
                                    <th class='col-md-3'> Extension </th>
                                    <th class='col-md-3'> Cantidad </th>
                                </tr>
                            </thead>
                        <tbody>";

                while($row = $consulta->fetch_assoc()) {
               
                    $html .= "<tr>
                                <td class='col-md-3'>".$row['formato']."</td>
                                <td class='col-md-3'>".$row['cantidad']."</td>
                            </tr>";
                }

                $html .=  " </tbody><table>";
            }
        }
    }
?>


<html>
    <head>
        <meta charset="ISO-8859-1">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

    </head>
    <body>
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Consultar Registros</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="index.php">
                            <div class="form-group">
                                <label for="email">Fecha Inicio:</label>
                                <input type="date" class="form-control"  name="start" id="start" name="trip-start" min="2019-07-01 " max="<?php echo date('Y-m-d');?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Consultar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class=" col-md-12">
            <h1>Consulta de Archivos. <?php (isset($date)?print_r($date):'');?> </h1>
            <br>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal"> Consultar Archivos </button>
        </div>
        <div class="col-md-12 ">
                <?php ($html === 0)? print_r("No has hecho ninguna consulta") : print_r($html) ; ?>
        </div>
    </body>
</html>