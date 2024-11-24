<?php
/*
//  Item Management
//  
//  
*/  

$switchboard += ['item' => array (
    'list' => 'getItems'
    ,'delete' => 'deleteItem'
    ,'update' => 'updateItem'
    ,'insert' => 'insertItem'
    )];

function getItems()
{
    header('Content-type: application/json');

    _sanitizeRequest(['userId']);

    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'items' => _getItems($_POST['userId']) ));
}
function _getItems($userId) 
{
global $db;
    logger("get items for user {$userId}");
    try {
        $query = $db->prepare( "SELECT * from artikel where benutzer_id = ? order by benutzer_id DESC");
        $ret = $query->bind_param('s', $userId );
        $ret = $query->execute();

        $result = $query->get_result();
        
        $newArr = array();
        while ( $db_field = $result->fetch_assoc() ) {
            
            $db_field['bild'] = handleImage('read', 'dataURI', 'item', $db_field['id']);
            $newArr[] = $db_field;

        }
        $query->close();

        return $newArr; 
    }
    catch( Exception $e ) {
        logger($e->getMessage());
        echo json_encode( 
            array('status'=> _createStatus(203,'item')
        ));
        exit();
    }                                
}


function insertItem()
{
    header('Content-type: application/json');

    _sanitizeRequest(['userId',  "bezeichnung", "beschreibung", "cabinetId"]);

    $itemId = _insertItem($_POST['userId'], $_POST['bezeichnung'], $_POST['beschreibung'], $_POST['cabinetId']);

    if ( array_key_exists('photo', $_FILES)) {
        handleImage( "store", "png", $_POST['entity'], $itemId);
    }
 
    echo json_encode( array('status'=> _createStatus(0,'item')
    ,'items' => _getItems($_POST['userId']) ));
}

function _insertItem($userId, $bezeichnung, $beschreibung, $cabinetId)
{
global $db;

try {
    $query = $db->prepare("insert into artikel (benutzer_id, bezeichnung, beschreibung, schrank_id) values (?,?,?,?)");
    $query->bind_param( 'ssss', $userId, $bezeichnung, $beschreibung, $cabinetId );
    
    $ret = $query->execute();
    return $db->insert_id;
}
catch( Exception $e ) {
    logger($e->getMessage());
    echo json_encode( 
        array('status'=> _createStatus(203,'cabinet')
    ));
    exit();
}
}


function updateItem()
{
    header('Content-type: application/json');

    _sanitizeRequest(['userId', 'itemId', 'bezeichnung', 'beschreibung', 'cabinetId']);

    _updateItem($_POST['userId'], $_POST['itemId'], $_POST['bezeichnung'], $_POST['beschreibung'], $_POST['cabinetId']);
    
    if ( array_key_exists('photo', $_FILES)) {
        handleImage( "store", "png", $_POST['entity'], $_POST['itemId']);
        //$ret = move_uploaded_file($_FILES['photo']['tmp_name'], $filename);
        //$ret = move_uploaded_file($_FILES['photo']['tmp_name'], "{$id}.png");
    }

    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'items' => _getItems($_POST['userId']) ));
}

function _updateItem($userId, $itemId, $bezeichnung, $beschreibung, $cabinetId)
{
global $db;

    try {
        $query = $db->prepare("update artikel set bezeichnung=?, beschreibung=?, schrank_id=? where id=?");
        $query->bind_param( 'ssss', $bezeichnung, $beschreibung, $cabinetId, $itemId );
        
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

function deleteItem()
{
    global $db;

    header('Content-type: application/json');

    _sanitizeRequest(['userId', 'itemId']);

    _deleteItem($_POST['userId'], $_POST['itemId']);
    if ( array_key_exists('photo', $_FILES)) {
        handleImage( "remove", "png", $_POST['entity'], $_POST['itemId']);
    }
   
    echo json_encode( array('status'=> _createStatus(0,'item deleted')
    ,'items' => _getItems($_POST['userId']) ));
}
function _deleteItem($userId, $itemId)
{
global $db;

try {
    $query = $db->prepare("Delete from artikel where id = ?");
    $query->bind_param( 's', $itemId );
    
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