<?php
/*
//  Project Management
*/  


function insertProject()
{
    header('Content-type: application/json');

    _sanitizeRequest(['resumeId', 'title', 'description', 'beginDate', 'endDate', 'company', 'industry']);

    _insertProject($_POST['resumeId'], $_POST['title'], $_POST['description'], $_POST['beginDate'], $_POST['endDate'], $_POST['company'], $_POST['industry']);
   
    echo json_encode( array('status'=> _createStatus(0,'project')
    ,'resume' => _getResume($_POST['resumeId']) ));
}

function _insertProject($resumeId, $title, $description, $beginDate, $endDate, $company, $industry)
{
global $db;

try {
    $query = $db->prepare("insert into Project (resumeId, title, description, beginDate, endDate, company, industry) values (?,?,?,?,?,?,?)");
    $query->bind_param( 'sssssss', $resumeId, $title, $description, $beginDate, $endDate, $company, $industry );
    
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


function updateProject()
{

    header('Content-type: application/json');

    _sanitizeRequest(['resumeId', 'projectId', 'jobTitle', 'roleDescription', 'beginDate', 'endDate', 'company', 'industry']);

    _updateProject($_POST['resumeId'], $_POST['projectId'], $_POST['title'], $_POST['description'], 
                        $_POST['beginDate'], $_POST['endDate'], $_POST['company'], $_POST['industry']);
    
    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'resume' => _getResume($_POST['resumeId']) ));
}

function _updateProject($resumeId, $ProjectId, $title, $description, $beginDate, $endDate, $company, $industry)
{
global $db;

    try {
        $query = $db->prepare("update Project set jobTitle=?, roleDescription=?, beginDate=?, endDate=?, company=?, industry=? where resumeId=? and projectId=?");
        $query->bind_param( 'ssssssss', $title, $description, $beginDate, $endDate, $company, $industry, $resumeId, $ProjectId );
        
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

function deleteProject()
{
    global $db;

    header('Content-type: application/json');

    _sanitizeRequest(['resumeId', 'projectId']);

    _deleteProject($_POST['resumeId'], $_POST['projectId']);
    
    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'resume' => _getResume($_POST['resumeId']) ));
}
function _deleteProject($resumeId, $projectId)
{
global $db;

    try {
        $query = $db->prepare("Delete from project where resumeId = ? and projectId = ?");
        $query->bind_param( 'ss', $resumeId, $projectId );
        
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
function _getProjects($resumeId) 
{
global $db;
    $query = $db->prepare( "SELECT * from project where resumeId = ? order by beginDate DESC");
    $ret = $query->bind_param('s', $resumeId );
    $ret = $query->execute();

    $result = $query->get_result();

    return $result->fetch_all(MYSQLI_ASSOC); 
}
?>