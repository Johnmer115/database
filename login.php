<?php 
if(isset($_POST['btnsubmit'])) {
    require_once "config.php";

    $sql = "SELECT * FROM tblaccounts WHERE username = ? AND password = ? AND status = 'ACTIVE'";
   
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $_POST['txtusername'], $_POST['txtpassword']);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result) > 0){
                $accounts = mysqli_fetch_array($result, MYSQLI_ASSOC);

                session_start();
                $_SESSION['username'] = $accounts['username'];
                $_SESSION['usertype'] = strtoupper($accounts['usertype']);

                // Redirect based on user type
                switch($_SESSION['usertype']) {
                    case 'ADMINISTRATOR':
                        header("location: index.php");
                        break;
                    case 'TECHNICAL':
                        header("location: index.php");
                        break;
                    case 'STAFF':
                        header("location: index.php");
                        break;
                    default:
                        header("location: index.php");
                        break;
                }
                exit;
            } else {
                $error = "Incorrect login details or account is inactive.";
            }
        }
    } else {
        $error = "Database error. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - AU Technical Support Management System</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    /* Body background and center alignment */
    body {
      background-color: rgba(81, 145, 209, 0.56);
      justify-content: center;
      align-items: center;
    }
    /* Container box styles with flex:1 to allow footer to stick to the bottom */
    .container-box {
      background: #fff;
      display: flex;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      max-width: 800px;
      max-height: 800px;
      width: 100%;
      transform: scale(0.8);
      flex: 1;
    }
    .left-section, .right-section {
      padding: 20px;
      width: 50%;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .right-section {
      background: rgba(169, 178, 186, 0.66);
    }
    .login-container {
      width: 100%;
      max-width: 300px;
    }
    .logo {
      max-width: 100px;
      margin-bottom: 10px;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 8px;
    }
    /* Footer styling: small font, full-width background in blue */
    .footer {
      font-size: 0.8rem;
      background-color: rgb(69, 96, 229);
      color: #fff;
      text-align: center;
      padding: 10px 0;
      /* Full-width background hack */
      width: 100vw;

    
    }
    /* Ensure main content takes up available space */
    .content-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
    }
  </style>
</head>
<body>
  <!-- Main Content Wrapper -->
  <div class="content-wrapper">
    <div class="container-box">
      <!-- Left Section: Login Form -->
      <div class="left-section">
        <img src="picture/Arellano_University_logo.png" class="logo" alt="AU Logo">
        <h3 class="text-center mb-4">AU Login</h3>

        <?php if(!empty($error)): ?>
          <p class="error"><?= $error; ?></p>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="login-container">
          <!-- Username Field -->
          <div class="mb-3">
            <input type="text" name="txtusername" id="txtusername" class="form-control" placeholder="Username:" required>
          </div>

          <!-- Password Field -->
          <div class="mb-3">
            <input type="password" name="txtpassword" id="txtpassword" class="form-control" placeholder="Password:" required>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" id="remember">
              <label class="form-check-label" for="remember">Remember Me</label>
            </div>
            <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
          </div>

          <!-- Login Button -->
          <div class="d-grid mb-3">
            <button type="submit" name="btnsubmit" class="btn btn-primary">Login</button>
          </div>

          <!-- Sign-Up Link -->
          <div class="text-center">
            <p>Don't have an account? <a href="signup.php" class="text-decoration-none">Sign Up</a></p>
          </div>
        </form>
      </div>

      <!-- Right Section: System Name & Logo -->
      <div class="right-section">
        <img src="picture/Tech_Support_Logo.png" class="logo" alt="Technical Support Logo">
        <h4>Technical Support Management System</h4>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer" style="margin-top: 10px;">
    &copy;CopyRight 2025, Tanqui-on, Johnmer
  </footer>

  <!-- Bootstrap JS (Optional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
