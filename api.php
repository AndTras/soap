<?php 
  class api {
    public $date;
        function get($date){
            
            // Cogigo para la consulta al API, se usa de la funcion curl para realizar el request

            $curl = curl_init();

            curl_setopt_array($curl, array(
            
            // parametros de consulta

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

            // respuesta a la consulta
            $response = curl_exec($curl);
            curl_close($curl);

            $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );
            $xml = new SimpleXMLElement($response);
            $body = $xml->xpath('//soapEnvelope')[0];
            
            // codigo para convertir string desordenado a un array en JSON

            $array = json_decode(json_encode((array)$body), TRUE); 
            $array = $array['soapBody']['azRtaBuscarArchivo'];
            return $array;
        }
    }
?>