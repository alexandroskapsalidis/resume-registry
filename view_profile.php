<!------------------ The Model/Controller ------------------------>
<?php
session_start();
// Including database connection code
require_once "pdo.php";

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
      font-size: 1.2rem;
    }
  </style>

</head>

<body>

  <?php
  // Show welcome message if the user is logged in
  if (isset($_SESSION['name'])) {
    echo ("<p style='padding: 10px; text-align:right;'>");
    echo (" Hello <span style='color:blue; font-size: 1.2em;'>" . $_SESSION['name'] . "</span>");
    echo ("</p>");
  }
  ?>

  <main class="w-75 container bg-light my-5 p-5">

    <?php
    // No entrance if not logged in
    if (! isset($_SESSION["email"])) {
      die('<p style="color:red;font-size:1.3em;">Not logged in</p>');
    }
    ?>
    <h1 class="mb-5 text-center">Resume Registry</h1>

    <table class="table mt-4 mb-5 p-5">

      <?php

      // Fetching the specific Profile to show on the Table
      $stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :id");
      $stmt->execute(array(':id' => $_GET['profile_id']));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // Fetching the specific Positions of this Profile
      $stmt_pos = $pdo->prepare("SELECT * FROM position WHERE profile_id = :id ORDER BY rank");
      $stmt_pos->execute(array(':id' => $_GET['profile_id']));

      echo "<h2 class='text-center'>Profile: <span style='color:blue; font-size:larger;'>{$row["first_name"]} {$row["last_name"]}</span></h2>";
      // echo "<tr><th>Name</th><th>Headline</th><th>Action</th>";

      echo "<tr><th>";
      echo ("First Name");
      echo ("</th><td>");
      echo ($row['first_name']);
      echo ("</td></tr>\n");
      echo "<tr><th>";
      echo ("Last Name");
      echo ("</th><td>");
      echo ($row['last_name']);
      echo ("</td></tr>\n");
      echo "<tr><th>";
      echo ("Email");
      echo ("</th><td>");
      echo ($row['email']);
      echo ("</td></tr>\n");
      echo "<tr><th>";
      echo ("Headline");
      echo ("</th><td>");
      echo ($row['headline']);
      echo ("</td></tr>\n");
      echo "<tr><th>";
      echo ("Summary");
      echo ("</th><td>");
      echo ($row['summary']);
      echo ("</td></tr>\n");

      echo ("<tr><td colspan='2' class='m-3'></td></tr>");
      echo ("<tr><td colspan='2' class=' fs-4 p-2'>Positions</td></tr>");
      echo ("<tr><td colspan='2' class='m-3'></td></tr>");
      echo "<tr>";
      echo "<th>Year</th>";
      echo "<th>Description</th>";
      echo "</tr>\n";
      // Looping the Positions from the Database
      while ($pos_row = $stmt_pos->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$pos_row['year']}</td>";
        echo "<td>{$pos_row['description']}</td>";
        echo "</tr>\n";
      }

      ?>

    </table>

    <p class="d-flex justify-content-evenly pt-4">
      <a href="update.php?profile_id=<?= $_GET['profile_id']; ?>" class="btn btn-warning px-3">Update</a>
      <a href="app.php" class="btn btn-success mx-5 px-4">Done</a>
    </p>
  </main>


  <script>

  </script>

</body>

</html>