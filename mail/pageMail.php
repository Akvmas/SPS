<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Récupérer le PDF le plus récent basé sur la date de modification
$folder = '../RenduPdf';
$files = array_diff(scandir($folder), array('..', '.'));  // Obtenez tous les fichiers du répertoire

// Trie les fichiers par date de modification, du plus récent au plus ancien
usort($files, function($a, $b) use ($folder) {
    return filemtime($folder . '/' . $b) - filemtime($folder . '/' . $a);
});

$newest_file = $files[0];
$pdf_path = $folder . '/' . $newest_file;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['from'];
    $to = $_POST['to'];

    //send the email
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.office365.com';// Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                                 
        $mail->Username   = 'relay-smtp@eau17.fr';// SMTP username
        $mail->Password   = 'H75az_q_8pSy2e';// SMTP password
        $mail->SMTPSecure = 'tsl';         
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
}
?>

<!DOCTYPE html>
<html>
    <body>
        <h2>Formulaire d'envoi de mail</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="from">De:</label><br>
            <input type="text" id="from" name="from"><br>
            <label for="to">A:</label><br>
            <input type="text" id="to" name="to"><br>
            <input type="submit" value="Envoyer">
            <?php if (isset($newest_file)) {
                echo "Le PDF séléctioné est : " . $newest_file . "<br>"; 
            } ?>
            
        </form>
    </body>
</html>
