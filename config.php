<?php
if ($_SERVER['SERVER_NAME'] == 'www.lullaby.de') {
    // database config
    define('DB_HOST', 'localhost');
    define('DB_USERNAME', '13470m1705_3'); 
    define('DB_PASSWORD', 'Mgoe##55'); 
    define('DB_SCHEMA', '13470m1705_3');

    define('BASE_URL', 'https://www.lullaby.de/');

    DEFINE('CONCIERGE_ACCOUNT', 'concierge@vita.mainkiez.de');
    DEFINE('CONCIERGE_PASSWORD', 'dwcCR44VT$H$;CVPEV;3v');
    // ...

    // data path config
    define('DATADIR', $_SERVER['DOCUMENT_ROOT'].'/data/');
}
else {
    // database config
    define('DB_HOST', 'localhost');
    define('DB_USERNAME', 'tidyawayUser'); 
    //define('DB_USERNAME', 'lunchAppUser'); 
    define('DB_PASSWORD', 'tidyawayPassword'); 
    define('DB_SCHEMA', 'tidyaway');

    define('BASE_URL', 'http://localhost:5173/');

    DEFINE('CONCIERGE_ACCOUNT', 'concierge@vita.mainkiez.de');
    DEFINE('CONCIERGE_PASSWORD', 'dwcCR44VT$H$;CVPEV;3v');
    // ...

    // data path config
    define('DATADIR', $_SERVER['DOCUMENT_ROOT'].'/data/');

}
if (!empty($_SERVER['SERVER_NAME'])) error_log("*** Configuration loaded for server ".$_SERVER['SERVER_NAME']);
?>