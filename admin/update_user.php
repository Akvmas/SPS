<!DOCTYPE html>
<?php
  // Initialiser la session
  session_start();
  // Vï¿½rifiez si l'utilisateur est connectï¿½, sinon redirigez-le vers la page de connexion
  if(!isset($_SESSION["username"])){
    header("Location: login.php");
    exit(); 
  }
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body class="img js-fullheight" style="background-image: url(../images/bg.jpeg);">
        <?php
        require('../config.php');
        if (isset($_REQUEST['username'],$_REQUEST['type'], $_REQUEST['password'])){
            // rï¿½cupï¿½rer le nom d'utilisateur 
            $username = stripslashes($_REQUEST['username']);
            // rï¿½cupï¿½rer le mot de passe 
            $password = stripslashes($_REQUEST['password']);
            // rï¿½cupï¿½rer le type (user | admin)
            $type = stripslashes($_REQUEST['type']);
            $stmt = $pdo->prepare("UPDATE `user` SET `type` = :type, password=SHA2(:password,256) WHERE `username` = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount()){
                echo "
                <body class='img js-fullheight' style='background-image: url(../images/bg.jpeg);'>
                <div class='ftco-section'>
                <div class='container'>
                <div class='row justify-content-center'>
                <div class='col-md-6 text-center mb-5'>                
                <h3 class='heading-section'>L'utilisateur a été mis à  jour avec succés.</h3>
                <div class='form-group'>
                <p>Cliquez <a href='home.php'>ici</a> pour retourner Ã  la page d'accueil</p>
                <script src='../js/jquery.min.js'></script>
                <script src='../js/popper.js'></script>
                <script src='../js/bootstrap.min.js'></script>
                <script src='../js/main.js'></script>
                </div>
                </div>
                </div>
                </div>
                </div>";
            }
        }else{
            ?>
            <section class="ftco-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center mb-5">
                            <h2 class="heading-section">Update User</h2>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-4">
                            <div class="login-wrap p-0">
                                <form class="signin-form"  action="" method="post" name="login">
                                    <div class="form-group">
                                    <input type="text" class="form-control" name="username" placeholder="username" required>
                                </div>
                                <div class="form-group">
                                    <select class="form-control" name="type" id="type" >
                                        <option value="" disabled selected>Type</option>
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input id="password-field" type="password" name="password" class="form-control" placeholder="password" required>
                                    <span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                </div>
                                <div class="form-group">
                                    <button type="submit" value="Connexion " name="submit" class="form-control btn btn-primary submit px-3">Sign In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script src="../js/jquery.min.js"></script>
        <script src="../js/popper.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/main.js"></script>
        <?php 
        } ?>
    </body>
</html>
