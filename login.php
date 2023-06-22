<!DOCTYPE html>
<html>
  <head>
  	<title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
	</head>
  <body class="img js-fullheight" style="background-image: url(images/bg.jpeg);">
    <?php
    require('config.php');
    session_start();
    if (isset($_POST['username'])){
      $username = stripslashes($_REQUEST['username']);
      $username = mysqli_real_escape_string($conn, $username);
      $_SESSION['username'] = $username;
      $password = stripslashes($_REQUEST['password']);
      $password = mysqli_real_escape_string($conn, $password);
      $query = "SELECT * FROM `user` WHERE username='$username' and password='".hash('sha256', $password)."'";
      print_r($query);
      $result = mysqli_query($conn,$query) or die(mysqli_error($conn));
      print_r($result);
      if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // vérifier si l'utilisateur est un administrateur ou un utilisateur
        if ($user['type'] == 'admin') {
          header('location: admin/home.php');      
        }else{
          header('location: index.php');
        }
      }else{
        $message = "Le nom d'utilisateur ou le mot de passe est incorrect.";
      }
    }
    ?>
    <section class="ftco-section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6 text-center mb-5">
            <h2 class="heading-section">Login</h2>
          </div>
        </div>
        <div class="row justify-content-center">
          <div class="col-md-6 col-lg-4">
            <div class="login-wrap p-0">
              <form class="signin-form"  action="" method="post" name="login">
                <div class="form-group">
                  <input type="text" class="form-control" name="username" placeholder="username">
                </div>
                <div class="form-group">
                  <input id="password-field" type="password" name="password" class="form-control" placeholder="password">
                  <span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                </div>
                <div class="form-group">
                  <button type="submit" value="Connexion " name="submit" class="form-control btn btn-primary submit px-3">Sign In</button>
                </div>
                <?php if (! empty($message)) { ?>
                  <p class="errorMessage"><?php echo $message; ?></p>
                  <?php } ?>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>