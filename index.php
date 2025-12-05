<!-- The initial page of the project  -->

<!----------------- The Model ------------------------>
<?php
session_start();
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
  <main class="w-60 container bg-light my-5 p-5">
    <h1 class="mb-5 text-center">Welcome to Resume Registry</h1>
    <?php
    // Inform if logged in
    if (isset($_SESSION['name'])) {
      echo ("<p class='text-success text-center'>");
      echo (" Logged in as: <span style='color:blue; font-size: 1.2em;'>" . $_SESSION['name'] . "</span>");
      echo ("</p>");
    }
    // Check if we are logged in!
    if (! isset($_SESSION["email"])) { ?>
      <p class="text-center">Please log in to edit profiles or view them without log in.</p>
      <p class="d-flex justify-content-center my-4">
        <a href="login.php" class="btn btn-success">Log In</a>
        <a href="view_nologin.php" class="btn btn-primary mx-3">View Profiles</a>
      </p>
    <?php } else { ?>
      <p class=" d-flex justify-content-center my-4">
        <a href="app.php" class="btn btn-primary mx-3">View Profiles</a>
        <a href="logout.php" class="btn btn-danger">Log Out</a>
      </p>
    <?php } ?>
  </main>

  <script>
    // Confirm Log out
    const logoutLink = document.querySelector('a[href="logout.php"]');
    if (logoutLink) {
      logoutLink.addEventListener('click', function(event) {
        const confirmed = confirm("Are you sure you want to log out?");
        if (!confirmed) {
          event.preventDefault(); // Prevents the submit
        }
      });
    }
  </script>

</body>

</html>