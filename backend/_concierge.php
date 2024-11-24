<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

DEFINE('TEMPLATEDIR', $_SERVER['DOCUMENT_ROOT'].'/backend/internal/');

DEFINE('SMTP_HOST', 'smtp.goneo.de');

function _sendConfirmationMail($u, $l) {
    logger("*** Sending confirmation mail for {$u}, {$l}");
  
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                    //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = SMTP_HOST;                              //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = CONCIERGE_ACCOUNT;                      //SMTP username
        $mail->Password   = CONCIERGE_PASSWORD;                     //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->CharSet   = 'UTF-8';

        //Recipients
        $mail->setFrom(CONCIERGE_ACCOUNT, 'Concierge');
        $mail->addAddress($u);     //Add a recipient
        //$mail->addAddress('ellen@example.com');               //Name is optional
        $mail->addReplyTo(CONCIERGE_ACCOUNT, 'Concierge');
        //$mail->addCC('cc@example.com');
        $mail->addBCC(CONCIERGE_ACCOUNT);

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $vars = array(
            '$link' => $l
          );

        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Deine Anmeldung auf mainkiez.de';

   
        $mail->Body = strtr(file_get_contents(TEMPLATEDIR."confirmation.html.php"), $vars);
       
        //$mail->Body    = "Für die Bestätigung bitte hier klicken:<br>$l";
        $mail->AltBody = "Please confirm singing up with mainkiez.\r\n$l";

        $mail->send();
        //echo 'Message has been sent';
    } catch (Exception $e) {
        logger("_sendConfirmationMail error {$e->getMessage()}");
        //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function _sendPasswordResetMail($u, $l) {
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                    //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = SMTP_HOST;                              //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = CONCIERGE_ACCOUNT;                      //SMTP username
        $mail->Password   = CONCIERGE_PASSWORD;                     //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->CharSet   = 'UTF-8';
        
        //Recipients
        $mail->setFrom(CONCIERGE_ACCOUNT, 'Concierge');
        $mail->addAddress($u);     //Add a recipient
        //$mail->addAddress('ellen@example.com');               //Name is optional
        $mail->addReplyTo(CONCIERGE_ACCOUNT, 'Concierge');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        //Content
        $vars = array(
        '$link' => $l
        );

        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Reset your password - tidyaway.de';

   
        $mail->Body = strtr(file_get_contents(TEMPLATEDIR."reset.html"), $vars);
        $mail->AltBody = "This is the body in plain text for non-HTML mail clients\n\r$l";

        $mail->send();
        //echo 'Message has been sent';
    } catch (Exception $e) {
        logger("_sendPasswordMail error {$e->getMessage()}");
    }
}

?>
