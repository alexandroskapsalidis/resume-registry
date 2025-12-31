<!------------------ The Model ------------------------>
<?php
session_start();
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
      font-size: 1.4rem;
    }
  </style>

<body>
  <div class="row">
    <main class="w-75 container bg-light my-5 p-5">
      <h1 class="mb-5 text-center">Welcome to Resume Registry</h1>

      <table class="table mt-4 mb-5 p-5">
        <?php

        // Fetch joined profiles with user names
        $stmt = $pdo->query("
                              SELECT p.first_name, p.last_name, p.email, p.headline, p.summary, u.name
                              FROM profile AS p
                              JOIN users AS u ON p.user_id = u.user_id
                              ORDER BY u.name
                          ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2 class='text-center'>All Profiles</h2>";
        echo "<tr><th>User</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Headline</th></tr>";
        foreach ($rows as $row) {
          echo "<tr><td>";
          echo htmlentities($row['name']);
          echo "</td><td>";
          echo htmlentities($row['first_name']);
          echo ("</td><td>");
          echo htmlentities($row['last_name']);
          echo ("</td><td>");
          // echo htmlentities($row['email']);
          echo "email";
          echo ("</td><td>");
          echo htmlentities($row['headline']);
          echo ("</td></tr>\n");
        }
        ?>
      </table>
      <p class="d-flex justify-content-center my-4">
        <a href="login.php" class="btn btn-success">Log In</a>
        <a href="index.php" class="btn btn-primary mx-5 px-3">Home</a>
      </p>
    </main>
  </div>

</body>

</html>