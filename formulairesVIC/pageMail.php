<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$folder = 'PDF';
$files = array_diff(scandir($folder), array('..', '.'));

usort($files, function ($a, $b) use ($folder) {
    return filemtime($folder . '/' . $b) - filemtime($folder . '/' . $a);
});

$newest_file = $files[0];
$pdf_path = $folder . '/' . $newest_file;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['from'];
    $to = $_POST['to'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = '';
        $mail->SMTPAuth   = true;
        $mail->Username   = '';
        $mail->Password   = '';
        $mail->SMTPSecure = 'tsl';
        $mail->Port       = 587;

        $mail->setFrom($from, 'Mailer');
        $mail->addAddress($to, 'Receiver');

        $mail->addAttachment($pdf_path);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'PDF du formulaire FON';
        $mail->Body    = 'Bonjour,
			<br><br>
			Bonne réception d’une fiche de chantier en PJ.
            <br><br> Cordialement,
			<br>
			  <img alt="PHPMailer" src="cid:my-attach">';
        $mail->AddEmbeddedImage("", "my-attach", "");
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
        <input type="hidden" id="from" name="from" value="csps@eau17.fr"><br>
        <label for="to">A:</label><br>
        <input type="text" id="to" name="to"><br>
        <input type="submit" value="Envoyer">
        <?php if (isset($newest_file)) {
            echo "Le PDF séléctioné est : " . $newest_file . "<br>";
        } ?>

    </form>
</body>

</html>
