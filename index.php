<?php

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "soap";
    $html = "";

    $conn = new mysqli($servername, $username, $password,$dbname);
    if(isset($_POST['start'])){

        $date = $_POST['start'];
  
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT date FROM soap_dates where date = '$date'"; 

        $consulta = $conn->query($sql);

        if ($consulta->num_rows > 0) {

            $sql = "SELECT archivos.id, archivos.name, tipo_archivo.formato FROM archivos INNER JOIN tipo_archivo ON archivos.id = tipo_archivo.id where archivos.date = '$date'"; 
            
            $consulta2 = $conn->query($sql);

            if ($consulta2->num_rows > 0) {

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
                while($row = $consulta2->fetch_assoc()) {
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
        
    
            $sqlInsert = "INSERT INTO soap_dates (date)VALUES ('$date')";
            
            if ($conn->query($sqlInsert) === TRUE) {
            
            } else {
                echo "Error: " . $sqlInsert . "<br>" . $conn->error;
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "http://test.analitica.com.co/AZDigital_Pruebas/WebServices/SOAP/index.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>"\r\n\r\n
                <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"
                xmlns:xsds=\"http://www.analitica.com.co/AZDigital/xsds/\">\r\n   
                    <soapenv:Header/>\r\n   
                    <soapenv:Body>\r\n      
                        <xsds:BuscarArchivo>\r\n        
                            <Condiciones>\r\n                
                                <Condicion Tipo=\"FechaInicial\" Expresion=\"$date 00:00:00\"/>\r\n            
                            </Condiciones>\r\n      
                        </xsds:BuscarArchivo>\r\n   
                    </soapenv:Body>\r\n
                </soapenv:Envelope>",
            CURLOPT_HTTPHEADER => array("Content-Type: text/xml"),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
            $xml = new SimpleXMLElement($response);
            $body = $xml->xpath('//soapEnvelope')[0];
            $array = json_decode(json_encode((array)$body), TRUE); 
            $array = $array['soapBody']['azRtaBuscarArchivo'];

            if(count($array) > 0){

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
    
                    $name = explode('.',$name);
                    $con = count($name)-1;
                    $nom ='';

                    for ($x = 0; $x <= $con-1; $x++) {
                        $nom .= $name[$x].'.';
                    }
              
                    $sqlInsert = "INSERT INTO archivos (id,name,date) VALUES ('$id','$nom','$date')";
                    
                    if ($conn->query($sqlInsert) === TRUE) {
                
                        $sqlInsert2 = "INSERT INTO tipo_archivo (id,formato,date) VALUES ('$id','$name[$con]','$date')";
                        
                        if ($conn->query($sqlInsert2) === TRUE) {
                            $html .= "<tr>
                                        <td class='col-md-3'>".$id."</td>
                                        <td class='col-md-3'><span class='d-inline-block text-truncate' style='max-width: 80%;'>".$nom."</span> </td>
                                        <td class='col-md-6' colspan='2'>".$name[$con]."</td>
                                      </tr>";
                        }

                    } else {
                        echo "Error: " . $sqlInsert . "<br>" . $conn->error;
                    }
                }
                $html .= " </tbody><table>";
            }else{
                echo "no hay archivos";
            }
        }
      

    }else {
        $html = 0;
        
        if(isset($_POST['dateFormato'])){
            $dateFormato = $_POST['dateFormato'];

            $sql = "SELECT formato , COUNT(formato) as cantidad FROM tipo_archivo where date = '$dateFormato' GROUP BY formato ORDER BY cantidad DESC "; 
            
            $consulta = $conn->query($sql);

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

    $conn->close();
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
            <h1>Consulta de Archivos.</h1>
            <br>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal"> Consultar Archivos </button>
        </div>
        <div class="col-md-12 ">
                <?php ($html === 0)? print_r("No has hecho ninguna consulta") : print_r($html) ; ?>
        </div>
    </body>
</html>