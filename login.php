<!----------------- The Model ------------------------>
<?php
session_start();
// Including database connection code 
require_once "pdo.php";

// Redirect if already logged in
if (isset($_SESSION['email'])) {
  header("Location: app.php");
  return;
}

// Handling login credentials
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (isset($_POST["email"]) && isset($_POST["password"])) {

    // Checking if they're empty
    if (strlen($_POST['email']) < 1 || strlen($_POST['password']) < 1) {
      $_SESSION['error'] = "Email and password are required";
      header("Location: login.php");
      return;
    }

    // Email validation (checking sympol @)
    if (strpos($_POST['email'], '@') === false) {
      $_SESSION['error'] = "Email must have an at-sign (@)";
      header("Location: login.php");
      return;
    }

    // Checking hashed password
    $salt = 'XyZzy12*_';
    $email = $_POST['email'];
    $check = hash('md5', $salt . $_POST['password']);

    // Fetching the user
    $stmt = $pdo->prepare("SELECT user_id, name, hashed_password FROM users WHERE email = :em");
    $stmt->execute(array(':em' => $email));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: Display the computed password hash to check against the database and stop execution
    // echo '<pre>';
    // echo "Computed hash: " . $check . "\n";
    // echo "Stored hash:   " . $row['hashed_password'] . "\n";
    // echo '</pre>';
    // die();

    // Email not found
    if ($row === false) {
      $_SESSION['error'] = "Email not found";
      header("Location: login.php");
      return;
    }

    // If we reach here, email exists. Now check password:
    $stored_hash = $row['hashed_password'];

    if ($check === $stored_hash) {
      $_SESSION['email'] = $email;
      $_SESSION['name'] = $row['name'];
      $_SESSION['user_id'] = $row['user_id'];
      $_SESSION['success'] = "Logged in";
      header("Location: app.php");
      return;
    } else {
      $_SESSION['error'] = "Incorrect password";
      header("Location: login.php");
      return;
    }
  }
}

?>

<!------------------ The View ------------------------>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="Alexandros">
  <meta name="description" content="Resume Registry management project built with PHP and MySQL.">
  <meta name="keywords" content="PHP, MySQL, Resume Registry, management, project">
  <link rel="icon" type="image/x-icon" href="profiles.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Profiles</title>

  <style>

  </style>

</head>

<body>

  <?php
  // A welcome message if we are loged in
  if (isset($_SESSION['name'])) {
    echo ("<p style='padding: 10px; text-align:right;'>");
    echo (" Hello <span style='color:blue; font-size: 1.2em;'>" . $_SESSION['name'] . "</span>");
    echo ("</p>");
  }
  ?>

  <main class="w-50 container bg-light my-5 p-5">
    <h1 class="mb-5 text-center">Resume Registry</h1>
    <p>Please Login</p>
    <form method="post" id="login-form">
      <p>Email:
        <input type="text" size="40" name="email" class="form-control">
      </p>
      <p>Password:
        <input type="password" size="40" name="password" class="form-control">
      </p>
      <p class="d-flex justify-content-center my-5">
        <input type="submit" value="Login" class="btn btn-success mx-3">
        <a href="<?php echo ($_SERVER['PHP_SELF']); ?>" class="btn btn-warning mx-3">Refresh</a>
        <a href="index.php" class="btn btn-primary mx-3">Home</a>
      </p>
    </form>

    <?php

    // Flash error message
    if (isset($_SESSION["error"])) {
      echo ('<p style="color:red">' . $_SESSION["error"] . "</p>\n");
      unset($_SESSION["error"]);
    }

    ?>

  </main>

  <script>
    // JavaScript Form Validation 

    document.getElementById('login-form').addEventListener('submit', function(event) {
      const email = document.querySelector('[name="email"]').value.trim();
      const password = document.querySelector('[name="password"]').value.trim();

      if (email === '' || password === '') {
        alert('Both fields must be filled out');
        event.preventDefault(); // Prevents submit
        return;
      }

      // Email format check
      if (!email.includes('@')) {
        alert('Invalid email address');
        event.preventDefault();
        return;
      }
    });
  </script>
</body>

</html>