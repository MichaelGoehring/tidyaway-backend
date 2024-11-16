<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once 'vendor/autoload.php';
//require_once 'bootstrap.php';
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Paper;
Settings::setZipClass(Settings::PCLZIP);
Settings::setOutputEscapingEnabled(true);

cors();
session_start();
require_once "database.php";
include_once 'internal/config.php';
require_once "image.php";

$switchboard = array();
require_once "user.php";
require_once "cabinet.php";
require_once "item.php";
require_once "account.php";

try {
    _dbConnectAndOpen();
}
catch(err) {

}

if ($_REQUEST['action'] == 'letter') {
    logger("Creating resume letter");
   createResumeLetter();
   exit;
}
elseif($_REQUEST['action'] == 'getPhoto') {
    getPhoto();
    exit;
}

require_once "image.php";

$stripeSecretKey = "sk_test_51OI703Ced0bi1v5g2XvTrgBek4DzbO0exkGSy6zEznJVPSEyjAnOJc8WEnJfDuLurmf5M1GYIKFOw3LSPPVU8l9L00Np4Awm0d";
\Stripe\Stripe::setApiKey($stripeSecretKey);

error_log("entity ".$_REQUEST['entity']);
logger("document_root=".$_SERVER['DOCUMENT_ROOT']);

$switchboard += ['user' => array (
    'signup' => 'startRegistration',
    'confirm' => 'confirmRegistration',
    'signin' => 'loginUser',
    //'validate' => 'validateUser',
    'logout' => 'logoutUser',
    'changePassword' => 'changePassword',
    'reset' => 'requestChangePasswordURL',
    //'deleteUser' => 'deleteUser'
  
    //,'updateUserFilter' => 'updateUserFilter'
    'createCheckoutSession' => 'createCheckoutSession',
    'test' => 'test'
    )];

    $switchboard += ['personal' => array (
        'savePhoto' => 'savePhoto'
        ,'getPhoto' => 'getPhoto'
        )];

$session = null;

logger("starting php script with session id: ".session_id());

if  ($_POST && 
    isset($_POST['entity']) &&
    isset( $switchboard[$_POST['entity']]) && 
    isset($switchboard[ $_POST['entity'] ][$_POST['action']])) {    

    if (function_exists( $switchboard[ $_POST['entity'] ][ $_POST['action'] ])) { 

        //_dbConnectAndOpen();
       
        error_log("start func ".$switchboard[ $_POST['entity'] ][ $_POST['action'] ]);
        $switchboard[ $_POST['entity'] ][ $_POST['action'] ]();
    }
    else {
        header('Content-type: application/json');
        echo json_encode( _createResponse( _createStatus( 999, 'Activity unknown')));
    }
}
else {
    header('Content-type: application/json');
    echo json_encode( _createStatus( 999, 'Parameter wrong'));
}

function _createStatus($err_succ, $err_mess)
{
    $arr = array(
        "error_success" => $err_succ,
        "message" => $err_mess
    );
   
    return $arr;
} 

function logger($msg) {
    $verbose = true;
    $date = new DateTime("now");


    if ($verbose) {
        error_log("{$date->format('y-m-d H:i:s')} {$msg}");
    }
}

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

function _checkSession( $userId ) {
    if (empty($_SESSION['userId']) || ($_SESSION['userId'] != $userId)) {  
        echo json_encode( array('status'=> _createStatus(701,"Problem mit der Benutzerauthentifikation.")));    
        exit;      
    }
}
function _sanitizeRequest( array $felder ) 
{
    foreach( $felder as $feld)
    {
        if (!key_exists($feld, $_REQUEST)) {
            error_log("sanitize - Feld $feld existiert nicht");
            echo json_encode( array('status'=> _createStatus(100,"Feld $feld existiert nicht")));
            exit;
        }
    }
};

function _createResponse( ...$arrs)
{
    return array_merge($arrs);
}



// function _saveUploadedPhoto($entity, $id)
// {
//     //global $photodir;

//     if ( array_key_exists('photo', $_FILES)) {
//         $ret = move_uploaded_file($_FILES['photo']['tmp_name'], "{PHOTODIR}photo/{$entity}/{$id}.png");
//         //error_log("move_uploaded_file returned $ret");
//     }
// }

// function _deletePhoto($entity, $id)
// {
//     //global $photodir;

//     try {
//         if (file_exists("{PHOTODIR}photo/{$entity}/{$id}.png")) {
//             unlink( "{PHOTODIR}photo/{$entity}/{$id}.png" );
//         }
//     }
//     catch(Exception $e) {

//     }
// }

function createCheckoutSession() {
    $YOUR_DOMAIN = 'http://localhost:5173';
    $checkout_session = \Stripe\Checkout\Session::create([
        'line_items' => [[
          # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
          'price' => 'price_1PmZDICed0bi1v5gbpefjX7Q',
          'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'locale' => 'de',
        'success_url' => $YOUR_DOMAIN . '/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $YOUR_DOMAIN . '/cancel?session_id={CHECKOUT_SESSION_ID}&userId=233424',
        'automatic_tax' => [
          'enabled' => true,
        ],
        'consent_collection' => [
            'terms_of_service' => 'required'
        ]
      ]);
      logger($checkout_session->url);
      echo( json_encode( array('url'=>$checkout_session->url)));

      //header("HTTP/1.1 303 See Other");
      //header("Location: " . $checkout_session->url);      
}
function test() {
    foreach ($_SERVER as $parm => $value)  echo "$parm = '$value'\n";
}
function savePhoto()
{
     
    _sanitizeRequest(['userId']);

    if ($_SESSION['user']['userId'] == $_REQUEST['userId']) {
        logger("saving photo");
        handleImage( "store", "png", "user", $_REQUEST['userId']);
        }
    echo json_encode( array('status'=> _createStatus(0,"OK")));
    exit;
}
function getPhoto()
{
    
    //_sanitizeRequest(['userId']);
    if ($_SESSION['userId'] == $_REQUEST['userId']) {
        
        $image = handleImage( "read", "base64", "user", $_REQUEST['userId']);
        header('Content-type: image/jpeg;');
        header("Content-Length: " . strlen($image));
        echo $image;    
        exit;    
    }
    logger("saving photo");

    echo json_encode( array('status'=> _createStatus(0,"OK")));
    exit;
}
?>