<?php
/*
//  User Management
//  startRegistration
//  202 - user (email) already exists.
*/  

$switchboard += ['user' => array (
    'signup' => 'startRegistration',
    'confirm' => 'confirmRegistration',
    'signin' => 'loginUser',
    'validate' => 'validateUser',
    'logout' => 'logoutUser',
    'changePassword' => 'changePassword',
    'requestChangePasswordURL' => 'requestChangePasswordURL',
    'deleteUser' => 'deleteUser',
    'update' => 'updatePersonalData'
  
    //,'updateUserFilter' => 'updateUserFilter'
    )];

 

require_once "_concierge.php";
require_once "_user.php";

/************************************************************************
*
*   startRegistration
*   V1.0: Best practices
*   --------------------
*
*   0 - Start der Registrierung erfolgreich, Bestätigungslink geschickt
*   202 - Fehler bei der Registrierung (meist aktiver Benutzer existiert schon)
*
*   _getUserByEmail (returns $user or null)
*   999 - Technical error
*
*   _deleteUserByEmail
*   999 - Technical error
*
*   _insertUser (returns id)
*   999 - Technical error
*
*   _sendConfirmationMail
*
*************************************************************************/
function startRegistration()
{
    global $db;
    session_destroy();
    error_log("Start SignUp");

    header('Content-type: application/json');

    _sanitizeRequest(['username', 'password']);

    $user = _getUserByEmail($_POST['username']);
    if ($user) {
        logger("UM: 201 - Start registration FAILED");
        echo json_encode( array('status'=> _createStatus(201,'register')));  
        exit();  
    }
    else {

        // create token (hex)
        $token = bin2hex(random_bytes(16));
        
        // create password hash
        $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        //logger("********************************************");
        logger("UM: Starting registration for ".$_POST['username']);
        //logger("UM: Password is               ".$_POST['password']);
        //logger("UM: Hash is                   ".$passwordHash);
        //logger("********************************************");
        
        /*------------------------------------
        // inserting user
        // and c (confirmation) token 
        //
        //------------------------------------*/
        $userId = null;
        $token = null;

        try {
            $db->begin_transaction();
        
            $userId = _insertUser($_POST['username'], $passwordHash);
            $token = _insertToken($userId, 'c');

            $db->commit();
        }
        catch( Exception $e ) {
            logger($e->getMessage());
            $db->rollback();
            echo json_encode( array('status'=> _createStatus(203,'register')));
            exit();
        }
        
        $YOUR_DOMAIN = 'http://localhost:5173'; //explode('/', $_SERVER['SERVER_PROTOCOL'])[0]."://".$_SERVER['SERVER_NAME'].':';     // 'http://localhost:5173';

        _sendConfirmationMail($_POST['username'], BASE_URL.'confirm?&t='.$token.'&m=c');   
        
        logger("UM: Start registration OK");
        echo json_encode( array('status'=> _createStatus(0,'register')));
    }
}

/************************************************************************
*
*   confirmRegistration
*   -----------------
*
*   0 - Bestätigung der Registrierung erfolgreich
*   211 - Fehler bei der Bestätigung der Registrierung
*   212 - Token falsch/ nicht in Datenbank
*   213 - Token veraltet
*
*   _getUserByEmail (returns $user or null)
*   999 - Technical error
*
*   _tokenIsValid
*
*
*   _deleteUserByEmail
*   999 - Technical error
*
*   _confirmRegistration
*   999 - Technical error
*
*************************************************************************/
function confirmRegistration()
{

    header('Content-type: application/json');
    $token = $_POST['token'];

    
    try {
        _confirmRegistration($_POST['token']);
    
    }
    catch(error) {
        logger(error);
    }

    echo json_encode( array('status'=> _createStatus(0,'success')));

}

function validateUser()
{
    global $db;

    header('Content-type: application/json');

    _sanitizeRequest(['username', 'password']);
    $user = _getUserByEmail($_POST['username']);
    //$jwt = null;

    if ($user) {
        
        if ( ($user['status'] == 1) && password_verify( $_POST['password'], $user['passwordHash'])) {
            logger("VALIDATE USER setting userid to ".$user['id']);
            echo json_encode( array('status'=> _createStatus(0,''), //'jwt'=>$jwt, 
                'token' => $user['id'],  
                'user'=> array('id'=>$user['id'], 'token'=>$user['token'], 'isAdmin'=>$user['isAdmin'])
                )
            );
        }
        else {
            logger("password hashes do not match for {$_POST['password']} {$user['passwordHash']}");
            echo json_encode( array('status'=> _createStatus(200,'Invalid password')));
        }
    }
    else {
        error_log("user not set");
        echo json_encode( array('status'=> _createStatus(200,'User does not exist.')));
    }
}

function loginUser()
{
    global $db;

    session_unset();
    session_destroy();
    
    header('Content-type: application/json');

    _sanitizeRequest(['username', 'password']);
    $user = _getUserByEmail($_POST['username']);

    if ($user) {

        logger("********************************************");
        logger("LO: login for                 ".$_POST['username']);
        logger("LO: Password is               ".$_POST['password']);
        //logger("LO: UserDbHash                ".$user['passwordHash']);
        logger("********************************************");

        if ( ($user['status'] == 1) && password_verify( $_POST['password'], $user['passwordHash'])) {
        
            session_start();
            $_SESSION['user'] = $user;

            logger("session updated in Login");
            echo json_encode( array('status'=> _createStatus(0,''), //'jwt'=>$jwt, 
              
                                    'user'=> array( 'userId'=>$user['userId']
                                                   /*  ,'name'=>$user['name'], 
                                                    'workingFrom'=>$user['workingFrom'],
                                                      'url1'=>$user['url1'],
                                                      'url2'=>$user['url2'],
                                                    'isAdmin'=>$user['isAdmin'],
                                                    'image' => handleImage( "read", "dataURI", "user", $user['userId']),
                                    'resumes' => _getResumes($user['userId']) */
                                    )
                )
            );
        }
        else {
            logger("password hashes do not match for {$_POST['password']} {$user['passwordHash']}");
            echo json_encode( array('status'=> _createStatus(200,'Invalid password')));
        }
    }
    else {
        error_log("user not set");
        echo json_encode( array('status'=> _createStatus(200,'User does not exist.')));
    }
}

function logoutUser()
{
    session_destroy();
    header('Content-type: application/json');
    
    echo json_encode( array('status'=> _createStatus(0,'')));
}
function changePassword()
{
    header('Content-type: application/json');
    _sanitizeRequest(['e', 'i', 't']);

    $username = _decrypt($_POST['e'], $_POST['i']);

    logger("changePassword for $username");

    $user = _getUserByEmail($username);

    if ( $user ) {
        
        switch( _tokenIsValid($user['token'], $_POST['t'], $user['token_created'] ))
        {
            case 1:     // token ok and in time
                //
                // Registrierung ok, Benutzer entsperren und token Felder auf NULL setzen
                //
                // Update Benutzer set token=null, token_erstellt=null, gesperrt=0 where email = ?
                //
                _updatePassword($username, $_POST['password']);
                break;
            case 2:     // token not correct
                _deleteToken($username);
                echo json_encode( array('status'=> _createStatus(212,'registration failed (token)')));
                exit;
            case  3:    // token expired
                _deleteToken($username);
                echo json_encode( array('status'=> _createStatus(213,'registration failed (time)')));
                exit;
            default:    // incorrect return
                break;
        }

    }
    else {
        echo json_encode( array('status'=> _createStatus(211,'register')));    
        exit;    
    }
 
    echo json_encode( array('status'=> _createStatus(100,'success')));
}

function deleteUser()
{
    error_log("USER: Delete user");

    session_destroy();
    header('Content-type: application/json');
    _sanitizeRequest(['username']);

    _deleteUserByEmail($_POST['username']);
}

function requestChangePasswordURL()
{
    global $db;

    logger("*** Request Change Password ***");

    session_destroy();
    
    header('Content-type: application/json');
    _sanitizeRequest(['username']);

    try {
        if (  ($user = _getUserByEmail($_POST['username'])) && ($user['status'] == 1)  ) { 
              
            $token = _insertToken($user['userId'], 'p');
            
            _sendPasswordResetMail($_POST['username'],$user['name'], BASE_URL.'reset?&t='.$token.'&m=p');   
        }
    }
    catch(error) {
        logger("unknown token");
    }

    echo json_encode( array('status'=> _createStatus(0,'reset link request')));
}

function _createTokenURL($username, $type)
{
    global $db;
    //error_log("user not existing ".$_POST['username']);
    // register user
    //
    // $token aready in hex notation (16*2=32 bytes)
    //
    $token = bin2hex(random_bytes(16));

    /*---------------------------
    //  generating iv
    //  and encrypiing email
    ---------------------------*/

    list($username_enc, $iv) = _encrypt($username);
    

    $link = BASE_URL.'index.html'.'?e='.$username_enc.'&i='.$iv.'&t='.$token.'&m='.$type;
    error_log('link='.$link);
    try {
        error_log("preparing insert for ".$_POST['username']);
        $query = $db->prepare( "update user set token=?, tokenCreated=now(), tokenType = ? where email=?");
        error_log("binding params to ".$db->error);
        
        $query->bind_param( 'sss', $token, $type, $username );
        
        $ret = $query->execute();
    }
    catch( Exception $e) {
        echo json_encode( array('status'=> _createStatus(208,'unknown technical error.'))); 
        exit; 
    }
    
    return $link;
}

function updatePersonalData()
{

    header('Content-type: application/json');
    _sanitizeRequest(['userId', 'name', 'workingFrom', 'url1', 'url2', 'education', 'language', 'email2', 'video']);
 
    _updatePersonalData($_POST['userId'], $_POST['name'], $_POST['workingFrom'], $_POST['url1'], $_POST['url2'], $_POST['education'],$_POST['language'],$_POST['email2'],$_POST['video'], "image");
    handleImage( "store", "png", "user",$_POST['userId'] );
    
    echo json_encode( array('status'=> _createStatus(0,'OK'))); 
}

function _updatePersonalData($userId, $name, $workingFrom, $url1, $url2, $education, $language, $email2, $video, $image)
{
global $db;
    try {
        $query = $db->prepare( "update user set name=?, workingFrom=?, url1 = ?, url2 = ?, education=?, language=?, email2=?, video=? where userId=?");
        
        $query->bind_param( 'sssssssss', $name, $workingFrom, $url1, $url2, $education, $language, $email2, $video,  $userId );
        
        $ret = $query->execute();
    }
    catch( Exception $e) {
        logger($e->getMessage());
        echo json_encode( array('status'=> _createStatus(208,'unknown technical error.'))); 
        exit; 
    }
}
?>