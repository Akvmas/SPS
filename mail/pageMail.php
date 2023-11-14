<?php
// Initialiser la session
session_start();
// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}
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
        $mail->Username   = 'csps@eau17.fr';// SMTP username
        $mail->Password   = '6jZAhEp9YrHkmURoR2g2';// SMTP password
        $mail->SMTPSecure = 'tsl';         
        $mail->Port       = 587;                 

        //Recipients
        $mail->setFrom($from, 'Mailer');
        $mail->addAddress($to, 'Receiver');   

        // Attachments
        $mail->addAttachment($pdf_path);        

        // Content
        $mail->isHTML(true);                                  
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'PDF du formulaire FON';
        $mail->Body    = 'Bonjour,
			<br><br>
			Bonne réception d’une fiche de chantier en PJ.
            <br><br> Cordialement,
			<br>
			  <img alt="PHPMailer" src="cid:my-attach">';
	$mail->AddEmbeddedImage("signature-gael.png", "my-attach", "signature-gael.png");
        $mail->send();
        echo 'Le mail a bien  été envoyé';
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
            <input  type="hidden" id="from" name="from" value="csps@eau17.fr"><br>
            <label for="to">A:</label><br>
            <input type="text" id="to" name="to"><br>
            <input type="submit" value="Envoyer">
            <?php if (isset($newest_file)) {
                echo "Le PDF séléctioné est : " . $newest_file . "<br>"; 
            } ?>
            
        </form>
    </body>
</html>
