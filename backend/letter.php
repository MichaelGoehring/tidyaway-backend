<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


include_once 'internal/config.php';
require_once "database.php";
require_once 'vendor/autoload.php';
//require_once 'bootstrap.php';
//require_once "user.php";

$switchboard = array();
//require_once "resume.php";

use PhpOffice\PhpWord\Settings;
Settings::setZipClass(Settings::PCLZIP);


cors();
createResumeLetter();

function cors() {
    
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        error_log("Allow cors from: {$_SERVER['HTTP_ORIGIN']}");    
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
        exit(0);
    }
    
    error_log("You have CORS!");
}

function createResumeLetter() {
    session_start();

    //_sanitizeRequest(['userId', 'resumeId']);

    if($_SESSION['user']['userId'] == $_REQUEST['userId']) {
        //$user = _getUserById($_REQUEST['userId']);
        //$resume = _getResume($_REQUEST['resumeId']);

      

        //PHPWord_Settings::setZipClass(Settings::PCLZIP);
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
       //$footer=$phpWord->addFooter();

        $section = $phpWord->addSection();
        $section->addText('Hello World!');

        
        $cellWidth = Converter::cmToTwip( 8 );
        $table = $section->addTable( );
        $table->addRow( null );
        $cell_left = $table->addCell( $cellWidth);
        $cell_right = $table->addCell( $cellWidth);
        
        $cell_left->addText("foto");
        $cell_left->addImage( handleImage("read". "png","user", "21"));
        
        $file = 'Resume.docx';
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $xmlWriter->save("php://output"); 
    }
}
?>