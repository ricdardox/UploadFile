<?php

/* Clase que permite subir archivos al servidor con ciertas restricciones sobre los archivos
 * Ricardo José Montes Rodriguez
 */
 

class Upload {

    /**
     * Valida que los archivos o el archivo que se suba tenga las extensiones que se pasan en el arreglo $allowedExts
     * @param Array $allowedExts  es un arreglo con las extensiones permitidas ejemplo: array('txt','png') 
     * @return boolean retorna verdader si la extensión del archivo es permitida
     */
    public static function allowedExts($file, $allowedExts = array()) {
        $nameAux = $file["name"];
        $temp = explode(".", $nameAux);
        $ext = end($temp);
        if (in_array($ext, $allowedExts) || count($allowedExts) == 0) {
            return "OK";
        } else {
            return "ERROR";
        }
    }

    /**
     * Valida que los archivos o el archivo que se suba sea del tipo que se pasa en el arreglo $allowedTypes
     * @param Array $allowedExts  es un arreglo con las extensiones permitidas ejemplo: array('image/gif','image/x-png') 
     * @return boolean retorna verdadero si el archivo es del tipo permitido
     */
    public static function allowedTypes($file, $allowedTypes = array()) {
        $type = $file["type"];
        if (in_array($type, $allowedTypes) || count($allowedTypes) == 0) {
            return "OK";
        } else {
            return "ERROR";
        }
    }

    /**
     * Valida que los archivos o el archivo que se suba sea del tipo que se pasa en el arreglo $allowedTypes
     * @param Array $allowedExts  es un arreglo con las extensiones permitidas ejemplo: array('image/gif','image/x-png') 
     * @return boolean retorna verdadero si el archivo es del tipo permitido
     */
    public static function allowedSizes($file, $sizeKb = -1) {
        $size = $file["size"] / 1024;
        if ($size <= $sizeKb || $sizeKb == -1) {
            return "OK";
        } else {
            return "ERROR";
        }
    }

    public static function errorUpload($code) {
        $outPut = "NONE";
        switch ($code) {
            case 0:
                //UPLOAD_ERR_OK
                $outPut = "Archivo subido con éxito.";
                break;
            case 1:
                //UPLOAD_ERR_INI_SIZE
                $outPut = "El archivo subido excede el tamaño permitido";
                break;
            case 2:
                //UPLOAD_ERR_FORM_SIZE
                $outPut = "El archivo subido excede la directiva MAX_FILE_SIZE que fue especificada en el formulario HTML.";
                break;
            case 3:
                //UPLOAD_ERR_PARTIAL
                $outPut = "Archivo subido con éxito.";
                break;
            case 4:
                //UPLOAD_ERR_NO_FILE
                $outPut = "Ningún archivo fue subido.";
                break;
            case 6:
                //UPLOAD_ERR_NO_TMP_DIR
                $outPut = "Falta la carpeta temporal.";
                break;
            case 7:
                //UPLOAD_ERR_CANT_WRITE
                $outPut = "No se pudo escribir el archivo en el disco.";
                break;
            case 8:
                //UPLOAD_ERR_EXTENSION
                $outPut = "No se proporcionó una forma de determinar una extensión.";
                break;
            default:
                $outPut = "Error desconocido.";
                break;
        }
        return $outPut;
    }

    public static function move($file, $destination) {
        $aux = $destination . $file["name"];
        $state = move_uploaded_file($file["tmp_name"], $aux);
        /* if ($state) {
          chmod($aux, 0777);
          } */
        return $state;
    }

    public static function details($FILE, $allowedExts = array(), $allowedTypes = array(), $sizeKb = -1, $fileTemp = false) {
        $files = array();
        $state = "OK";
        foreach ($FILE as $name => $file) {
            if ($file['name'] != "") {
                $aux = array(
                    "name" => $file['name'],
                    "errorcode" => $file['error'],
                    "error" => self::errorUpload($file['error']),
                    "ext" => self::allowedExts($file, $allowedExts),
                    "type" => self::allowedTypes($file, $allowedTypes),
                    "size" => self::allowedSizes($file, $sizeKb)
                );
                if ($fileTemp == true) {
                    $aux["temp"] = $file['tmp_name'];
                }
                if ($aux['errorcode'] != 0 || $aux['ext'] == 'ERROR' || $aux['type'] == 'ERROR' || $aux['size'] == 'ERROR') {
                    $state = "ERROR";
                }
                $files[$name] = $aux;
            }
        }
        return array('state' => $state, 'details' => $files);
    }

    public static function upload($FILE, $destination, $allowedExts = array(), $allowedTypes = array(), $sizeKb = -1) {
        $files = array();
        foreach ($FILE as $name => $file) {
            if ($file['name'] != "") {

                $error = array(
                    "error" => self::errorUpload($file['error']),
                    "ext" => self::allowedExts($file, $allowedExts),
                    "type" => self::allowedTypes($file, $allowedTypes),
                    "size" => self::allowedSizes($file, $sizeKb),
                    "copy" => "ERROR"
                );
                if ($error['ext'] === 'OK' && $error['type'] === 'OK' && $error['size'] === 'OK') {
                    if (self::move($file, $destination)) {
                        $error['copy'] = 'OK';
                    }
                }
                $files[$name] = $error;
            }
        }
        return $files;
    }

}
