<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once 'vendor/autoload.php';
include_once 'internal/config.php';
require_once "_concierge.php";

_sendConfirmationMail('mgoe@nahbereich.de', '/confirm?&t=TOKEN'.'&m=c');   

function logger($msg) {
    $verbose = true;
    $date = new DateTime("now");


    if ($verbose) {
        error_log("{$date->format('y-m-d H:i:s')} {$msg}");
    }
}

?>