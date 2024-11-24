<?php
/*
//  Cabinet Management
//  
//  
*/  

$switchboard += ['cabinet' => array (
    'list' => 'getCabinets'
    ,'delete' => 'deleteCabinet'
    ,'update' => 'updateCabinet'
    ,'insert' => 'insertCabinet'
    )];

function getCabinets()
{
    header('Content-type: application/json');

    _sanitizeRequest(['userId']);

    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'cabinets' => _getCabinets($_POST['userId']) ));
}
function _getCabinets($userId) 
{
global $db;
    logger("get cabinets for user {$userId}");
    try {
        $query = $db->prepare( "SELECT * from schrank where benutzer_id = ? order by benutzer_id DESC");
        $ret = $query->bind_param('s', $userId );
        $ret = $query->execute();

        $result = $query->get_result();
        
        $newArr = array();
        while ( $db_field = $result->fetch_assoc() ) {
            
            $db_field['bild'] = handleImage('read', 'dataURI', 'cabinet', $db_field['id']);
            $newArr[] = $db_field;

        }
        $query->close();

        return $newArr; 
    }
    catch( Exception $e ) {
        logger($e->getMessage());
        echo json_encode( 
            array('status'=> _createStatus(203,'register')
        ));
        exit();
    }                                
}


function insertCabinet()
{
    header('Content-type: application/json');

    _sanitizeRequest(['userId',  "bezeichnung"]);

    _insertCabinet($_POST['userId'], $_POST['bezeichnung']);

    if ( array_key_exists('photo', $_FILES)) {
        handleImage( "store", "png", $_POST['entity'], $_POST['cabinetId']);
    }
 
    echo json_encode( array('status'=> _createStatus(0,'cabinet')
    ,'cabinets' => _getCabinets($_POST['userId']) ));
}

function _insertCabinet($userId, $bezeichnung)
{
global $db;

try {
    $query = $db->prepare("insert into schrank (benutzer_id, bezeichnung) values (?,?)");
    $query->bind_param( 'ss', $userId, $bezeichnung );
    
    $ret = $query->execute();
}
catch( Exception $e ) {
    logger($e->getMessage());
    echo json_encode( 
        array('status'=> _createStatus(203,'cabinet')
    ));
    exit();
}
}


function updateCabinet()
{
    header('Content-type: application/json');

    _sanitizeRequest(['userId', 'cabinetId', 'bezeichnung']);

    _updateCabinet($_POST['userId'], $_POST['cabinetId'], $_POST['bezeichnung']);
    
    if ( array_key_exists('photo', $_FILES)) {
        handleImage( "store", "png", $_POST['entity'], $_POST['cabinetId']);
        //$ret = move_uploaded_file($_FILES['photo']['tmp_name'], $filename);
        //$ret = move_uploaded_file($_FILES['photo']['tmp_name'], "{$id}.png");
    }

    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'cabinets' => _getCabinets($_POST['userId']) ));
}

function _updateCabinet($userId, $cabinetId, $bezeichnung)
{
global $db;

    try {
        $query = $db->prepare("update schrank set bezeichnung=? where id=?");
        $query->bind_param( 'ss', $bezeichnung, $cabinetId );
        
        $ret = $query->execute();
    }
    catch( Exception $e ) {
        logger($e->getMessage());
        echo json_encode( 
            array('status'=> _createStatus(203,'register')
        ));
        exit();
    }
}

function deleteCabinet()
{
    global $db;

    header('Content-type: application/json');

    _sanitizeRequest(['userId', 'cabinetId']);

    _deleteCabinet($_POST['userId'], $_POST['cabinetId']);
    if ( array_key_exists('photo', $_FILES)) {
        handleImage( "remove", "png", $_POST['entity'], $_POST['cabinetId']);
    }
   
    echo json_encode( array('status'=> _createStatus(0,'cabinet')
    ,'cabinets' => _getCabinets($_POST['userId']) ));
}
function _deleteCabinet($userId, $cabinetId)
{
global $db;

try {
    $query = $db->prepare("Delete from schrank where id = ?");
    $query->bind_param( 's', $cabinetId );
    
    $ret = $query->execute();
}
catch( Exception $e ) {
    logger($e->getMessage());
    echo json_encode( 
        array('status'=> _createStatus(203,'register')
    ));
    exit();
}
}

?>