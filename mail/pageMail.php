<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$folder = '../RenduPdf';
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
        $mail->Host       = 'smtp.office365.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'csps@eau17.fr';
        $mail->Password   = '6jZAhEp9YrHkmURoR2g2';
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

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="img js-fullheight" style="background-image: url(../images/bg.jpeg);">
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Choix du destinataires</h2>
                </div>
            </div>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" id="from" name="from" value="csps@eau17.fr"><br>
                <label for="to"></label><br>
                <input class="form-control btn btn-primary submit px-3" type="text" id="to" name="to">
                <br>
                <br>
                <input class=" btn btn-primary submit px-3" type="submit" value="Envoyer">
                <?php if (isset($newest_file)) {
                    echo "Le PDF séléctioné est : " . $newest_file . "<br>";
                } ?>

            </form>
        </div>
    </section>
    <script src='../js/jquery.min.js'></script>
    <script src='../js/popper.js'></script>
    <script src='../js/bootstrap.min.js'></script>
    <script src='../js/main.js'></script>
</body>

</html>