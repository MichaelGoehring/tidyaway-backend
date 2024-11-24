<?php
/*
//  User Management
//  startRegistration
//  202 - user (email) already exists.
*/  

$switchboard += ['resume' => array (
    'add' => 'addResume'
    ,'load' => 'loadResume'
    ,'delete' => 'deleteResume'
    ,'update' => 'updateResume'
    ,'letter' => 'createResumeLetter'
    ,'insertPosition' => 'insertPosition'
    ,'updatePosition' => 'updatePosition'
    ,'deletePosition' => 'deletePosition'
    ,'createProject' => 'insertProject'
    ,'updateProject' => 'updateProject'
    ,'deleteProject' => 'deleteProject'
    )];

require_once('project.php');


function insertPosition()
{
    header('Content-type: application/json');

    _sanitizeRequest(['resumeId', 'jobTitle', 'roleDescription', 'beginDate', 'endDate', 'company', 'industry']);

    _insertPosition($_POST['resumeId'], $_POST['jobTitle'], $_POST['roleDescription'], $_POST['beginDate'], $_POST['endDate'], $_POST['company'], $_POST['industry']);
   
    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'resume' => _getResume($_POST['resumeId']) ));
}

function _insertPosition($resumeId, $jobTitle, $roleDescription, $beginDate, $endDate, $company, $industry)
{
global $db;

try {
    $query = $db->prepare("insert into position (resumeId, jobTitle, roleDescription, beginDate, endDate, company, industry) values (?,?,?,?,?,?,?)");
    $query->bind_param( 'sssssss', $resumeId, $jobTitle, $roleDescription, $beginDate, $endDate, $company, $industry );
    
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


function updatePosition()
{

    header('Content-type: application/json');

    _sanitizeRequest(['resumeId', 'positionId', 'jobTitle', 'roleDescription', 'beginDate', 'endDate', 'company', 'industry']);

    _updatePosition($_POST['resumeId'], $_POST['positionId'], $_POST['jobTitle'], $_POST['roleDescription'], 
                        $_POST['beginDate'], $_POST['endDate'], $_POST['company'], $_POST['industry']);
    
    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'resume' => _getResume($_POST['resumeId']) ));
}

function _updatePosition($resumeId, $positionId, $jobTitle, $roleDescription, $beginDate, $endDate, $company, $industry)
{
global $db;

    try {
        $query = $db->prepare("update position set jobTitle=?, roleDescription=?, beginDate=?, endDate=?, company=?, industry=? where resumeId=? and positionId=?");
        $query->bind_param( 'ssssssss', $jobTitle, $roleDescription, $beginDate, $endDate, $company, $industry, $resumeId, $positionId );
        
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

function deletePosition()
{
    global $db;

    header('Content-type: application/json');

    _sanitizeRequest(['resumeId', 'positionId']);

    _deletePosition($_POST['resumeId'], $_POST['positionId']);
    
    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'resume' => _getResume($_POST['resumeId']) ));
}
function _deletePosition($resumeId, $positionId)
{
global $db;

try {
    $query = $db->prepare("Delete from position where resumeId = ? and positionId = ?");
    $query->bind_param( 'ss', $resumeId, $positionId );
    
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
function addResume()
{

    header('Content-type: application/json');

    _sanitizeRequest(['userId', 'name']);

    _addResume( $_POST['userId'], $_POST['name']);

        
    echo json_encode( array('status'=> _createStatus(0,'register')
                            ,'resumes' => _getResumes($userId)
    ));
}

function _addResume($userId, $name)
{
    global $db;


    try {
        $query = $db->prepare("insert into resume (userId, name) values (?,?)");
        $query->bind_param( 'ss', $userId, $name );
        
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

function updateResume()
{
    global $db;

    header('Content-type: application/json');

    _sanitizeRequest(['resumeId', 'name', 'openingStatement', 'skills', 'contract']);

    _updateResume($_POST['resumeId'], $_POST['name'], $_POST['openingStatement'], $_POST['skills'], $_POST['contract']);
        

    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'resume' => _getResume($_POST['resumeId']) ));
}
function _updateResume($resumeId, $name, $openingStatement, $skills, $contract)
{
global $db;

    try {
        logger("prepare updateresume {$resumeId} ");
        $query = $db->prepare("update resume set name=?, openingStatement=?, skills=?, contract=? where resumeId=?");
        $query->bind_param( 'sssss', $name, $openingStatement, $skills, $contract, $resumeId );
        
        logger("execute updateresume");
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
function deleteResume()
{
    header('Content-type: application/json');

    _sanitizeRequest(['userId', 'resumeId']);

    _deleteResume($_POST['resumeId']);
        
    echo json_encode( array('status'=> _createStatus(0,'resume') 
    ,'resumes' => _getResumes($_SESSION['user']['userId']) ));
}
function _deleteResume($resumeId)
{
global $db;

    try {
        
        $query = $db->prepare("delete from resume where resumeId=?");
        $query->bind_param( 's', $resumeId );
        
        logger("execute updateresume");
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
function loadResume()
{
    header('Content-type: application/json');

    _sanitizeRequest(['resumeId']);

    logger("load resume for user ".$_SESSION['user']['userId']);

    echo json_encode( array('status'=> _createStatus(0,'resume')
    ,'resume' => _getResume($_POST['resumeId']) 
    ));
}

function _getResume($resumeId)
{
global $db;

    logger("loading resume {$resumeId}");
    try {
        $query = $db->prepare("select * from resume where resumeId = ?");
        $query->bind_param( 's', $resumeId );
        
        $ret = $query->execute();
        $result = $query->get_result();
        
      
        if ( $db_field = $result->fetch_assoc() ) {
        
            $db_field['positions']= _getPositions($db_field['resumeId']);
            $db_field['projects']= _getProjects($db_field['resumeId']);
        }
        $query->close();

        return $db_field;
    }
    catch( Exception $e ) {
        logger($e->getMessage());
        echo json_encode( 
            array('status'=> _createStatus(203,'register')
        ));
        exit();
    }
        
}

function createResumeLetter() {

    _sanitizeRequest(['userId', 'resumeId']);
  
    //logger("Session user".$_SESSION['user']['userId']);
    logger("Request user". $_REQUEST['userId']);

    if(1) { //$_SESSION['user']['userId'] == $_REQUEST['userId']) {
       
        //$resume = _getResume($_REQUEST['resumeId']);
        $resume = _getResume($_REQUEST['resumeId']);
      try {

        //PHPWord_Settings::setZipClass(Settings::PCLZIP);
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
       //$footer=$phpWord->addFooter();
        $paper = new \PhpOffice\PhpWord\Style\Paper();
       $paper->setSize('A4');  // or 'Legal', 'A4' ...
/*
       $section = $phpWord->addSection([
           'pageSizeW' => $paper->getWidth(),
           'pageSizeH' => $paper->getHeight(),
           'marginLeft' => 600, 
           'marginRight' => 600,
            'marginTop' => 600, 
            'marginBottom' => 600
       ]); */

        $section = $phpWord->addSection(
            [
                'marginLeft' => 200,
                'marginRight' => 200,
                'marginTop' => 200,
                'marginBottom' => 200,
                'headerHeight' => 50,
                'footerHeight' => 50,
            ]
        );
        //$section->addText('Hello World!');
        
        
        $cellWidth = \PhpOffice\PhpWord\Shared\Converter::cmToTwip(9.5);
        $table = $section->addTable( );
        $table->addRow( null );
        $cell_left = $table->addCell( \PhpOffice\PhpWord\Shared\Converter::cmToTwip(6), ['bgColor'=>'#d3d3d3']);
        $cell_right = $table->addCell( \PhpOffice\PhpWord\Shared\Converter::cmToTwip(14));
        
       
        
        $imageStyle = array(
            'width' => 144,
            'height' => 144*1.33
            //,'wrappingStyle' => 'square',
            //'positioning' => 'absolute',
            //'posHorizontalRel' => 'margin',
            //'posVerticalRel' => 'line',
        );
        $cell_left->addImage( handleImage("read","png","user", "21"), $imageStyle);
        $cell_left->addText(isset($_SESSION['user']['name'])?$_SESSION['user']['name']:'');
        $cell_left->addText(isset($_SESSION['user']['workingFrom'])?$_SESSION['user']['workingFrom']:'');
        $cell_left->addText("Education", ['bold'=>true, 'size'=>12]);
        $cell_left->addText("Languages", ['bold'=>true, 'size'=>12]);

        $cell_right->addText("Professional summary", ['bold'=>true, 'size'=>12]);
        $cell_right->addText(isset($resume['openingStatement'])?$resume['openingStatement']:'');
        $cell_right->addText(isset($resume['skills'])?$resume['skills']:'');

        $pStyle = [
            'spaceAfter'=>  \PhpOffice\PhpWord\Shared\Converter::pointToTwip(2)
        ];

        $cell_right->addText("Experience", ['bold'=>true, 'size'=>12]);
        foreach ($resume['positions'] as $position) {
            $cell_right->addText( (isset( $position['jobTitle'])?$position['jobTitle']:"not found"), 
                    ['bold'=>true], 
                    $pStyle 
                );

            logger($position['jobTitle']);
            $cell_right->addText( $position['roleDescription']);
        }

        foreach ($resume['projects'] as $project) {
            $cell_right->addText( (isset( $project['title'])?$project['title']:"not found"), 
                    ['bold'=>true], 
                    $pStyle 
                );

            //logger($position['jobTitle']);
            $cell_right->addText( $project['description']);
        }

        $footer = $section->addFooter();
        $footer->addText("Opportunity - Name - CUNOPTIMA.COM", ['size => 8']);

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
    catch(err) {
        logger(err);
    } 
    }
}
?>