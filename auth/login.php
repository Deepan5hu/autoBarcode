<?php
// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
  header("location: index.php");
  exit;
}

// Include config file
require_once "../db/db_connect.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Check if username is empty
  if (empty(trim($_POST["username"]))) {
    $username_err = "Please enter username.";
  } else {
    $username = trim($_POST["username"]);
  }

  // Check if password is empty
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter your password.";
  } else {
    $password = trim($_POST["password"]);
  }

  // Validate credentials
  if (empty($username_err) && empty($password_err)) {
    // Prepare a select statement
    $sql = "SELECT id, username, password, role, store_id FROM users WHERE username = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_username);

      // Set parameters
      $param_username = $username;

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        // Store result
        mysqli_stmt_store_result($stmt);

        // Check if username exists, if yes then verify password
        if (mysqli_stmt_num_rows($stmt) == 1) {
          // Bind result variables
          mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role, $store_id); // Add $store_id here
          if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $hashed_password)) {
              // Password is correct, so start a new session
              session_start();

              // Store data in session variables
              $_SESSION["loggedin"] = true;
              $_SESSION["username"] = $username;
              $_SESSION["role"] = $role;
              $_SESSION["store_id"] = $store_id; // Store store_id in session

              if ($_SESSION["role"] == "autobarcode") {
                header("location: ../barcode/index.php");
                exit;
              } else {
                $login_err = "Invalid username or password.";
              }
            } else {
              // Password is not valid, display a generic error message
              $login_err = "Invalid username or password.";
            }
          }
        } else {
          // Username doesn't exist, display a generic error message
          $login_err = "Invalid username or password.";
        }
      } else {
        echo "Oops! Something went wrong. Please try again later.";
      }

      // Close statement
      mysqli_stmt_close($stmt);
    }

  }

  // Close connection
  mysqli_close($conn);
}
?>

<!doctype html>
<html lang="en">

<head>
  <title>Log In</title>
  <link rel="icon" href="/mrp-in/assets/images/title.png" type="image/icon">
  <!-- Required meta tags -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <!-- Bootstrap CSS v5.2.1 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />

  <style>
    @import url(https://fonts.googleapis.com/css?family=Open+Sans:100,300,400,700);
    @import url(//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css);

    html,
    body {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #222;
      /* Set background color */
    }

    #logo {
      width: 200px;
      height: auto;
      position: absolute;
      /* top: 50%; */
      left: 50%;
      transform: translate(-50%, -50%);
    }


    @media (max-width: 767px) {
      #logo {
        width: 150px;
      }
    }

    .login-box {
      position: relative;
      max-width: 400px;
      width: 100%;
      padding: 20px;
      border-radius: 5px;
      text-align: center;
      margin-top: 50px;

      -webkit-backdrop-filter: blur(1px) saturate(180%);
      background-color: rgba(255, 255, 255, 0.07);
      border-radius: 12px;
      border: 1px solid rgba(209, 213, 219, 0.1);
    }

    .login-form {
      margin-bottom: 20px;
    }

    .login-text {
      color: white;
      font-size: 1.5rem;
      margin-bottom: 20px;

      //text-shadow: 1px -1px 0 rgba(0,0,0,0.3);
      .fa-stack-1x {
        color: black;
      }
    }

    .login-username,
    .login-password {
      background: transparent;
      border: 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.5);
      color: white;
      margin: 10px;
      padding: 10px;
      width: calc(100% - 20px);
    }

    .login-username:focus,
    .login-password:focus {
      background: white;
      color: black;
    }

    .login-submit {
      border: 1px solid transparent;
      /* Change border color to transparent */
      background: transparent;
      color: white;
      display: inline-block;
      padding: 10px 20px;
      margin-top: 20px;
      border-radius: 5px;
      transition: background-color 0.3s;
      box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
      /* Add transparent shadow */
    }


    .login-submit:hover,
    .login-submit:focus {
      box-shadow: 0 0 5px rgba(255, 255, 255, 1);
      color: black;
    }

    .alert-danger {
      color: #721c24;
      background-color: #f8d7da;
      border-color: #f5c6cb;
      padding: .75rem 1.25rem;
      margin-top: 20px;
    }

    [class*=underlay] {
      left: 0;
      min-height: 100%;
      min-width: 100%;
      position: fixed;
      top: 0;
    }


    .underlay-photo {
      animation: hue-rotate 6s infinite;
      background: linear-gradient(#a20000, transparent), url('https://images.pexels.com/photos/6601008/pexels-photo-6601008.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
      background-size: cover;
      -webkit-filter: grayscale(30%);
      z-index: -1;
    }


    .underlay-black {
      background: rgba(0, 0, 0, 0.7);
      z-index: -1;
    }

    @keyframes hue-rotate {
      from {
        -webkit-filter: grayscale(30%) hue-rotate(0deg);
      }

      to {
        -webkit-filter: grayscale(30%) hue-rotate(360deg);
      }
    }

    .sub {
      background: none;
      color: white;
      border: none;
    }
  </style>


</head>

<body>

  <main>

    <div class="nav">
      <img id="logo" src="/mrp-in/assets/images/Logo-white.png" alt="Logo">
    </div>
    <div class="login-box">

      <?php
      if (!empty($login_err)) {
        echo '<div class="alert alert-danger">' . $login_err . '</div>';
      }
      ?>

      <form class="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <p class="login-text">
          <span class="fa-stack fa-lg">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-lock fa-stack-1x"></i>
          </span>
        </p>
        <!-- <div class="form-group"> -->
        <div class="login-username">
          <label>Username</label>
          <input type="text" name="username"
            class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
            value="<?php echo $username; ?>">
          <span class="invalid-feedback">
            <?php echo $username_err; ?>
          </span>
        </div>
        <div class="login-password">
          <label>Password</label>
          <input type="password" name="password"
            class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
          <span class="invalid-feedback">
            <?php echo $password_err; ?>
          </span>
        </div>
        <div class="login-submit">
          <input class="sub" type="submit" value="Login">
        </div>
      </form>
      <div class="underlay-photo"></div>
      <div class="underlay-black"></div>
    </div>

  </main>
  <!-- Bootstrap JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
    crossorigin="anonymous"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
    integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
    crossorigin="anonymous"></script>
</body>

</html>