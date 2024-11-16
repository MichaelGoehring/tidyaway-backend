<?php 
/*
//  function _tokenIsValid($tokenInDb, $tokenInRequest, $tokenCreated)
//  function _getUserByEmail($email)
//  function _timestampUser($id)
//  function _confirmRegistration($user)
//  function _deleteUserByEmail($email)
//  function _deleteToken($email)
//  function _startRegistration($email, $password_hash, $token)
*/  

function _startRegistration($email, $password_hash, $token) {
global $db;
    try {
        //error_log("preparing insert for ".$_POST['username']);
        $query = $db->prepare( "insert into user (email, passwordHash, token, tokenCreated, tokenType) VALUES (?,?,?,NOW(),'c')");
        //error_log("binding params to ".$db->error);
        $query->bind_param( 'sss', $email, $password_hash, $token );
        $ret = $query->execute();
        $userId = $db->insert_id;

        _addResume( $userId, 'MyResume');
        //_startRegistrationBet($userId);
    }
    catch(Exception $e) {
        logger("_startRegistration DB exception: {$e->getMessage()}");
        return false;
    }

    return true;
}

function _startRegistrationBet($userId) {
    logger("Start registration bet account for $userId");
    try {
        $query = $db->prepare( "insert into user (email, password_hash, token, token_created, token_type) VALUES (?,?,?,NOW(),'c')");
        error_log("binding params to ".$db->error);
        $query->bind_param( 'sss', $_POST['username'], $password_hash, $token );
        $ret = $query->execute();
        $userId = $db->insert_id;
        _startRegistrationBet($userId);
    }
    catch(Exception $e) {
        return array(false, null);
    }
}

function _updatePassword($email, $password) {
    global $db;
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $date_utc = new DateTime("now", new DateTimeZone("UTC"));
    $bestaetigt = $date_utc->format('Y-m-d H:i:s');
    try {
        $query = $db->prepare("Update user set passwordHash=?, token=null, tokenCreated=null, status=1, confirmed=? where email = ?" );
        $query->bind_param( 'sss', $password_hash, $bestaetigt, $username );
        $ret = $query->execute();
    }
    catch( Exception $e) {
        logger( $e->getMessage());
        echo json_encode( array('status'=> _createStatus(700,'DB technical error')));
        exit;
    }
}

function _getUserByEmail($email)
{
    global $db;

    $user = null;

    logger("retrieving user details for email $email");

    try {
        $query = $db->prepare( "Select * from user where email = ?;");
        $ret = $query->bind_param( 's', $email );
        $ret = $query->execute();
       
            $result = $query->get_result();
            //error_log("getting result set $result");
            
            if ($user = $result->fetch_assoc()) {
                logger("retrieving user details for email $email successful");
                return $user;
            }
            else {
                logger("User $email not found");
                return null;
            }
            //error_log( var_dump($user));
            
        
    }
    catch(mysqli_sql_exception $e) {
        logger("DB error {$e->getMessage()}");
        echo json_encode( array('status'=> _createStatus(999,"Technical error: {$e->getMessage()}")));
        exit;
    }
    logger("retrieving user details for email $email successful");

    return $user;
}

function _getUserAccount($id) // 9.7.23
{
    global $db;

    try {
        $query = $db->prepare( "Select * from useraccount where userId = ?;");
        $ret = $query->bind_param( 's', $id );
        $ret = $query->execute();
       
            $result = $query->get_result();
            //error_log("getting result set $result");
            
            if ($useraccount = $result->fetch_assoc()) {
                logger("retrieving userAccount for userId $id successful");
                return $useraccount;
            }
            else {
                logger("UserAccount $id not found");
                return [];
            }
            //error_log( var_dump($user));
            
        
    }
    catch(mysqli_sql_exception $e) {
        logger("DB error {$e->getMessage()}");
        echo json_encode( array('status'=> _createStatus(700,"Technical error: {$e->getMessage()}")));
        exit;
    }
  
    return [];
}    

function _updateUserSecurityToken($userId, $token) {
    global $db;

    try {
        $query = $db->prepare( "update user set token=?, tokenType='w' where id = ?;");
        $ret = $query->bind_param( 'ss', 
                                    $token,
                                    $userId
            );
        $ret = $query->execute();
    }
    catch(mysqli_sql_exception $e) {
        logger("DB error {$e->getMessage()}");
        echo json_encode( array('status'=> _createStatus(999,"Technical error: {$e->getMessage()}")));
        exit;
    }
    
    return;
}

function _timestampUser($id)
{
    global $db;

    $ret = null;
    $now = date('Y-m-d H:i:s');
    $query = $db->prepare( "Update user set lastUsed = ? where id = ?;");
    $query->bind_param( 'sd', $now, $id );

    if ($ret = $query->execute())
    {
        //$result = $query->get_result();
    
        //$ret = $result->fetch_assoc();
    }
    
    return $ret;
}    

function _confirmRegistration($token)
{
    global $db;
    logger("Confirm registration for {$token}");

    try {
        $query = $db->prepare( "Select * from token where token = ?;");
        $ret = $query->bind_param( 's', $token );
        $ret = $query->execute();
       
        $result = $query->get_result();
        //error_log("getting result set $result");
        
        if ($tokenrec = $result->fetch_assoc()) {
            if (is_null($tokenrec)) {
                echo json_encode( array('status'=> _createStatus(302,'Token invalid')));
                exit;                
            }
            $now = new DateTime("now", new DateTimeZone("UTC"));
            $expires = new DateTime($tokenrec['tokenExpires']);
            if ($now > $expires) {
                _deleteUserById($tokenrec['userId']);
                echo json_encode( array('status'=> _createStatus(302,'Token expired')));
                exit;
            }
            else {
                _updateUserStatus($tokenrec['userId'], 1);
            }
        }
   }
   catch( Exception $e ) {
        logger($e->getMessage());
        $db->rollback();
        echo json_encode( array('status'=> _createStatus(999,'Technical error')));    
        exit;    
    }

}

function _deleteUserByEmail($email)
{
    global $db;
    
    logger("delete user $email");

    try {
        $query = $db->prepare( "Delete from user where email = ?;");
        $ret = $query->bind_param( 's', $email );
        $ret = $query->execute();
    }
    catch(mysqli_sql_exception $e) {
        logger("DB error {$e->getMessage()}");
        echo json_encode( array('status'=> _createStatus(999,"Technical error: {$e->getMessage()}")));
        exit;
    }
   
}

function _deleteUserById($userId)
{
    global $db;
    
    logger("delete user $userId");

    try {
        $query = $db->prepare( "Delete from user where userId = ?;");
        $ret = $query->bind_param( 's', $userId );
        $ret = $query->execute();
    }
    catch(mysqli_sql_exception $e) {
        logger("DB error {$e->getMessage()}");
        echo json_encode( array('status'=> _createStatus(999,"Technical error: {$e->getMessage()}")));
        exit;
    }
   
}

function _updateUserStatus($userId, $status)
{
    global $db;
    
    logger("update user $userId status to $status");

    try {
        $query = $db->prepare( "Update user set status = ? where userId = ?;");
        $ret = $query->bind_param( 'ss', $status, $userId );
        $ret = $query->execute();
    }
    catch(mysqli_sql_exception $e) {
        logger("DB error {$e->getMessage()}");
        echo json_encode( array('status'=> _createStatus(999,"Technical error: {$e->getMessage()}")));
        exit;
    }
   
}

function _tokenIsValid($tokenInDb, $tokenInRequest, $tokenCreated) {
    //
    // check if correct token received
    //
    if ($tokenInDb <> $tokenInRequest) {
        logger("token invalid");
        return 1;
    }

            //
        // check token (exists/valid)
        //

        logger("token_erstellt is ".$tokenCreated);
        
        $date_utc = new DateTime("now", new DateTimeZone("UTC"));
        $interval = date_diff(new DateTime($tokenCreated), $date_utc);
        $hours = $interval->format('%a')*24+$interval->format('%h');

        logger("timediff is now_utc - token_utc is ".$tokenCreated." - ".$date_utc->format("Y-m-d\TH:i:s\Z")." = ".$hours." h");

        //
        // token ist nur 6 Stunden gültig
        //
        if($hours > 6) {
            return 2;
        }

    return 0;
}

function _deleteToken($email)
{ 
    $bestaetigt = $date_utc->format('Y-m-d H:i:s');
    try {
        $query = $db->prepare("Update user set token=null, tokenCreated=null, status=1, confirmed=? where email = ?" );
        $query->bind_param( 'ss', $bestaetigt, $email );
        $ret = $query->execute();
    }
    catch( Exception $e) {
        logger( $e->getMessage());
    }
}

function _insertUser($email, $passwordHash) {
    global $db;
    $userId = null;

    $query = $db->prepare( "insert into user (email, passwordHash) VALUES (?,?)");

    $query->bind_param( 'ss', $email, $passwordHash );
    $ret = $query->execute();
    $userId = $db->insert_id;

    return $userId;
}

function _insertToken($userId, $tokenType, $hours=24)
{
    global $db;

    // create token (hex)
    $token = bin2hex(random_bytes(16));
    $tokenCreated = new DateTime("now", new DateTimeZone("UTC"));
    $tokenExpires = new DateTime("now", new DateTimeZone("UTC"));;
    date_add($tokenExpires, new DateInterval("PT{$hours}H"));

    $tokenCreated = $tokenCreated->format('Y-m-d H:i:s');
    $tokenExpires = $tokenExpires->format('Y-m-d H:i:s');

    $query = $db->prepare( "insert into token (userId, token, tokenCreated, tokenExpires, tokenType) VALUES (?, ?,?,?,?)");

    $query->bind_param( 'sssss', $userId, $token, $tokenCreated, $tokenExpires, $tokenType);
    $ret = $query->execute();
    $tokenId = $db->insert_id;

    return $token;   
}   

    function _insertUserAccount($userId) {
        global $db;
          
            try {
                $query = $db->prepare( "insert into useraccount (userId, endOfContract, comment) VALUES (?, DATE_ADD( current_date(),INTERVAL 2 MONTH), 'Ab Bestätigung')");
        
                $query->bind_param( 's', $userId );
                $ret = $query->execute();
        
            }
            catch(Exception $e) {
                logger("DB error {$e->getMessage()}");
                echo json_encode( array('status'=> _createStatus(999,"Technical error: {$e->getMessage()}")));
                exit;
            }
            return true;
        }
    
    function _getResumes($userId) 
    {
    global $db;
        logger("get resumes for user {$userId}");
        
        $query = $db->prepare( "SELECT * from resume where userId = ? order by resumeId DESC");
        $ret = $query->bind_param('s', $userId );
        $ret = $query->execute();
    
        $result = $query->get_result();
        
        $newArr = array();
        while ( $db_field = $result->fetch_assoc() ) {
        
            $db_field['positions']= _getPositions($db_field['resumeId']);
            $newArr[] = $db_field;
        }
        $query->close();
    
        return $newArr;                                 
    }

    function _getPositions($resumeId) 
    {
    global $db;
        $query = $db->prepare( "SELECT * from position where resumeId = ? order by beginDate DESC");
        $ret = $query->bind_param('s', $resumeId );
        $ret = $query->execute();

        $result = $query->get_result();

        return $result->fetch_all(MYSQLI_ASSOC); 
    }
?>