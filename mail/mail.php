<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['from'];
    $to = $_POST['to'];

    // get the most recent pdf
    $folder = 'uploadsPdf'; // I've removed the leading slash as it might cause issues
    $files = scandir($folder, SCANDIR_SORT_DESCENDING);

    // Check if there are files
    if ($files !== false && count($files) > 2) {  // We use 2 because scandir includes . and .. as files
        $newest_file = $files[0];
        $pdf_path = $folder . '/' . $newest_file;

        //send the email
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();                                            
            $mail->Host       = 'smtp.example.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                 
            $mail->Username   = 'user@example.com';  // SMTP username
            $mail->Password   = 'password';          // SMTP password
            $mail->SMTPSecure = 'tls';         
            $mail->Port       = 587;                 

            //Recipients
            $mail->setFrom($from, 'Mailer');
            $mail->addAddress($to, 'Receiver');   

            // Attachments
            $mail->addAttachment($pdf_path);        

            // Content
            $mail->isHTML(true);                                  
            $mail->Subject = 'Here is your PDF';
            $mail->Body    = 'Please find attached the PDF you requested.';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Aucun fichier trouvé dans le dossier.";
    }
}
?>
