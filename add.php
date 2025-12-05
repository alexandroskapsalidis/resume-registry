<!-- Inserting to the database -->

<!----------------- The Model ------------------------>
<?php
session_start();
// Including database connection code 
require_once "pdo.php";

// Handling Insert new Auto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (
    isset($_POST['add']) && isset($_POST['first_name']) && isset($_POST['last_name'])
    && isset($_POST['email']) && isset($_POST['headline'])
  ) {
    // Data validation (Checking if it's empty)
    if (
      strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1
      || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1
    ) {
      $_SESSION['error'] = 'Missing data';
      header("Location: add.php");
      return;
    }
    // Email validation (checking sympol @)
    if (strpos($_POST['email'], '@') === false) {
      $_SESSION['error'] = "Email must have an at-sign (@)";
      header("Location: add.php");
      return;
    }

    $_SESSION["addMessage"] = "";

    $sql = "INSERT INTO profile (user_id, first_name, last_name, email, headline, summary)
            VALUES (:user_id, :first_name, :last_name, :email, :headline, :summary)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':first_name' => $_POST['first_name'],
      ':last_name' => $_POST['last_name'],
      ':email' => $_POST['email'],
      ':headline' => $_POST['headline'],
      ':summary' => $_POST['summary']
    ));

    // Adding a success message to the SESSION to show after insertion
    $_SESSION["addMessage"] = "The row inserted succesfully.";
    header("Location: app.php");
    return;
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
    h2 {
      font-size: 1.4rem;
    }
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
    <?php
    // No entrance if not logged in 
    if (! isset($_SESSION["email"])) {
      die('<p class="text-danger text-center fs-5">Not logged in</p>');
    }

    // Flash Error message executed with the GET request
    if (isset($_SESSION["error"])) {
      echo ('<p style="color:red">' . $_SESSION["error"] . "</p>\n");
      unset($_SESSION["error"]);
    }

    ?>

    <!-- Add New Profile form -->
    <h2 class="pt-3 text-center">Add New Profile</h2>
    <form method="post" class="mt-4" id="add-profile-form">
      <p>First Name:
        <input type="text" class="form-control" name="first_name">
      </p>
      <p>Last Name:
        <input type="text" class="form-control" name="last_name">
      </p>
      <p>Email:
        <input type="text" class="form-control" name="email">
      </p>
      <p>Headline:
        <input type="text" class="form-control" name="headline">
      </p>
      <p>Summary:
        <input type="text" class="form-control" name="summary">
      </p>
      <p class="d-flex justify-content-center mt-5">
        <input type="submit" class="btn btn-success px-3" value="Add" name="add" />
        <a href="app.php" class="btn btn-warning mx-5 px-4">Cancel</a>
        <a href="logout.php" class="btn btn-danger px-3">Log Out</a>
      </p>

    </form>

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

    // Form Validation 
    document.getElementById('add-profile-form').addEventListener('submit', function(event) {
      const firstName = document.querySelector('input[name="first_name"]').value.trim();
      const lastName = document.querySelector('input[name="last_name"]').value.trim();
      const email = document.querySelector('input[name="email"]').value.trim();
      const headline = document.querySelector('input[name="headline"]').value.trim();
      // Empty Fields
      if (firstName === '' || lastName === '' || email === '' || headline === '') {
        alert('Fields must be filled out');
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