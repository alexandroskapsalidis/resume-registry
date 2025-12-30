<!-- Updating a row -->

<!----------------- The Model ------------------------>
<?php
// Start session and include database connection
session_start();
require_once "pdo.php";

// Procesing UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (
    isset($_POST['update']) && isset($_POST['first_name']) && isset($_POST['last_name'])
    && isset($_POST['email']) && isset($_POST['headline'])
  ) {

    // // Validate required fields
    if (
      strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1
      || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1
    ) {
      $_SESSION['error'] = "All the fields are required";
      $profile_id = $_POST['profile_id'];
      // urlencode(): convert special characters into a safe format for use in URLs.
      header("Location: update.php?profile_id=" . urlencode($profile_id));
      return;
    }

    // Email validation (checking sympol @)
    if (strpos($_POST['email'], '@') === false) {
      $_SESSION['error'] = "Email must have an at-sign (@)";
      $profile_id = $_POST['profile_id'];
      header("Location: update.php?profile_id=" . urlencode($_POST['profile_id']));
      return;
    }

    // Validating Positions   
    for ($i = 1; $i <= 9; $i++) {
      if (!isset($_POST['year' . $i])) continue;
      if (!isset($_POST['desc' . $i])) continue;
      $year = $_POST['year' . $i];
      $desc = $_POST['desc' . $i];
      if (strlen($year) == 0 || strlen($desc) == 0) {
        $_SESSION['error'] = "All fields are required";
        header("Location: update.php?profile_id=" . urlencode($_POST['profile_id']));
        return;
      }
      if (!is_numeric($year)) {
        $_SESSION['error'] = "Position year must be numeric";
        header("Location: update.php?profile_id=" . urlencode($_POST['profile_id']));
        return;
      }
    }

    // Validating Education   
    for ($i = 1; $i <= 9; $i++) {
      if (!isset($_POST['edu_year' . $i])) continue;
      if (!isset($_POST['institution' . $i])) continue;
      $year = $_POST['edu_year' . $i];
      $institution = $_POST['institution' . $i];
      if (strlen($year) == 0 || strlen($institution) == 0) {
        $_SESSION['error'] = "All fields are required";
        header("Location: update.php?profile_id=" . urlencode($profile_id));
        return;
      }

      if (!is_numeric($year)) {
        $_SESSION['error'] = "Education year must be numeric";
        header("Location: update.php?profile_id=" . urlencode($profile_id));
        return;
      }
    }

    // Update the profile row in the database
    $sql = "UPDATE profile 
        SET first_name = :first_name,
            last_name = :last_name,
            email = :email,
            headline = :headline,
            summary = :summary
        WHERE profile_id = :profile_id
          AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':first_name' => $_POST['first_name'],
      ':last_name' => $_POST['last_name'],
      ':email' => $_POST['email'],
      ':headline' => $_POST['headline'],
      ':summary' => $_POST['summary'],
      ':profile_id' => $_POST['profile_id'],
      ':user_id' => $_SESSION['user_id']
    ));

    // Updating Positions
    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM position WHERE profile_id=:pid');
    $stmt->execute(array(':pid' => $_POST['profile_id']));
    // Insert the new position entries
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
      if (!isset($_POST['year' . $i])) continue;
      if (!isset($_POST['desc' . $i])) continue;
      $year = $_POST['year' . $i];
      $desc = $_POST['desc' . $i];
      $stmt = $pdo->prepare('INSERT INTO Position
        (profile_id, rank, year, description)
        VALUES ( :pid, :rank, :year, :desc )');
      $stmt->execute(
        array(
          ':pid' => $_POST['profile_id'],
          ':rank' => $rank,
          ':year' => $year,
          ':desc' => $desc
        )
      );
      $rank++;
    }

    // Updating Educations
    // Clear out the old educations
    $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
    $stmt->execute(array(':pid' => $_POST['profile_id']));

    // Insert the new educations
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
      if (!isset($_POST['edu_year' . $i])) continue;
      if (!isset($_POST['institution' . $i])) continue;
      $edu_year = $_POST['edu_year' . $i];
      $institution = $_POST['institution' . $i];

      // Lookup the institution if it is there.
      $institution_id = false;
      $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
      $stmt->execute(array(':name' => $institution));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row !== false) $institution_id = $row['institution_id'];

      // If there was no institution, insert it
      if ($institution_id === false) {

        $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
        $stmt->execute(array('name' => $institution));
        $institution_id = $pdo->lastInsertId();
      }
      // Inserting Educations
      $stmt = $pdo->prepare('INSERT INTO Education (profile_id, institution_id, rank, year)
                             VALUES (:pid, :iid, :rank, :year)');
      $stmt->execute(
        array(
          ':pid' => $_POST['profile_id'],
          ':iid' => $institution_id,
          ':rank' => $rank,
          ':year' => $edu_year
        )
      );
      $rank++;
    }

    $_SESSION["addMessage"] = "Profile updated succesfully.";
    header("Location: app.php");
    return;
  }
}

// Fetching the specific Profile only if bellongs to the logged-in user
$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :xyz AND user_id = :user_id");
$stmt->execute(array(':xyz' => $_GET['profile_id'], ':user_id' => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
  $_SESSION['error'] = 'Bad value for profile_id';
  header('Location: app.php');
  return;
}

// Fetching the specific Positions of this Profile
$stmt_pos = $pdo->prepare("SELECT * FROM position WHERE profile_id = :id ORDER BY rank");
$stmt_pos->execute(array(':id' => $_GET['profile_id']));

// Fetching the specific Educations of this Profile
$stmt_edu = $pdo->prepare("SELECT e.year AS edu_year, i.name AS institution_name
                           FROM education e JOIN institution i ON e.institution_id = i.institution_id
                           WHERE e.profile_id = :id ORDER BY rank;");
$stmt_edu->execute(array(':id' => $_GET['profile_id']));

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
  <!-- Bootstrap Library -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- jQuery UI CSS (required for the .autocomplete())-->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
  <!-- jQuery UI JS (required for the .autocomplete()) -->
  <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

  <title>Profiles</title>

  <style>
    h2 {
      font-size: 1.4rem;
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
  <main class="w-50 container bg-light my-5 p-5">
    <h1 class="mb-5 text-center">Resume Registry</h1>

    <?php
    // Ban the entry if not logged in
    if (! isset($_SESSION["email"])) {
      die('<p class="text-danger text-center fs-5">Not logged in</p>');
    }
    // Guardian: Make sure that profile_id is present
    if (!isset($_GET['profile_id'])) {
      $_SESSION['error'] = "Missing profile_id";
      header('Location: app.php');
      return;
    }
    // Flash error message for updating
    if (isset($_SESSION["error"])) {
      echo ('<p style="color:red">' . $_SESSION["error"] . "</p>\n");
      unset($_SESSION["error"]);
    }
    ?>

    <h2 class="mt-5 text-center">Update Profile: <span class="text-primary fs-4"><?= htmlentities($row['first_name']) . ' ' . htmlentities($row['last_name']) ?></span></h2>

    <!-- Update Profile Form -->
    <form method="POST" class="mt-4" id="update-profile-form">
      <p>First Name:
        <input type="text" class="form-control" name="first_name" value="<?= htmlentities($row['first_name']) ?>">
      </p>
      <p>Last Name:
        <input type="text" class="form-control" name="last_name" value="<?= htmlentities($row['last_name']) ?>">
      </p>
      <p>Email:
        <input type="text" class="form-control" name="email" value="<?= htmlentities($row['email']) ?>">
      </p>
      <p>Headline:
        <input type="text" class="form-control" name="headline" value="<?= htmlentities($row['headline']) ?>">
      </p>
      <p>Summary:
        <!-- <input type="text" class="form-control" name="summary" value="<?= htmlentities($row['summary']) ?>"> -->
        <textarea class="form-control" name="summary" rows="4"><?= htmlentities($row['summary']) ?></textarea>
      </p>
      <p>Position:
        <input type="button" id="addPos" class="btn btn-secondary px-2 py-0" name="position" value="+">
      </p>
      <div id="position-fields">
        <!-- Showing the possitions from the Database -->
        <?php
        $countPos = 0;
        while ($pos_row = $stmt_pos->fetch(PDO::FETCH_ASSOC)) {
          $countPos++;
          $year = htmlentities($pos_row['year']);
          $description = htmlentities($pos_row['description']);
          echo '<div id="position' . $countPos . '">';
          echo  '<p>';
          echo 'Year: <input type="text" name="year' . $countPos . '" value="' . $year . '" />';
          echo '<textarea name="desc' . $countPos . '" rows="6" cols="80" placeholder="Description">' . $description . '</textarea>';
          echo '<input type="button" class="remove-pos btn btn-outline-secondary px-2 py-0 mt-1" data-id="' . $countPos . '" value="-" />';
          echo  '</p>';
          echo '</div>';
        }
        ?>
      </div>

      <p>Education:
        <input type="button" id="addEdu" class="btn btn-secondary px-2 py-0" name="education" value="+">
      </p>
      <div id="education-fields">
        <!-- Showing the educations from the Database -->
        <?php
        $countEdu = 0;
        while ($edu_row = $stmt_edu->fetch(PDO::FETCH_ASSOC)) {
          $countEdu++;
          $year = htmlentities($edu_row['edu_year']);
          $institution = htmlentities($edu_row['institution_name']);
          echo '<div id="education' . $countEdu . '">';
          echo '<p>';
          echo 'Year: <input type="text" name="edu_year' . $countEdu . '" class="form-control" value="' . $year . '" />';
          echo 'Institution: <input type="text" name="institution' . $countEdu . '" class="institution form-control" value="' . $institution . '" />';
          echo '<input type="button" class="remove-edu btn btn-outline-secondary px-2 py-0 mt-1"
                 data-id="' . $countEdu . '" value="-" />';
          echo '</p>';
          echo '</div>';
        }
        ?>
      </div>

      <!-- Buttons -->
      <p class="d-flex justify-content-center mt-5">
        <input type="hidden" name="profile_id" value="<?= htmlentities($row['profile_id']) ?>">
        <input type="submit" class="btn btn-success" value="Update" name="update" />
        <a href="app.php" class="btn btn-warning mx-3 px-4">Cancel</a>
        <a href="logout.php" class="btn btn-danger px-3">Log Out</a>
      </p>

    </form>

  </main>

  <script>
    // Confirm logout
    const logoutLink = document.querySelector('a[href="logout.php"]');
    if (logoutLink) {
      logoutLink.addEventListener('click', function(event) {
        const confirmed = confirm("Are you sure you want to log out?");
        if (!confirmed) {
          event.preventDefault(); // Prevents the submit
        }
      });
    }

    // When the plus button is being clicked, we take the #position-fields and education-fields divs
    // and append the html codefor the positions and educations respectively.
    let countPos = <?= $countPos ?>;
    let countEdu = <?= $countEdu ?>;
    $(document).ready(function() {

      // Adding positions
      $('#addPos').click(function() {
        if (countPos >= 9) {
          alert("Maximum of nine position entries exceeded");
          return;
        }
        countPos++;
        let positionId = countPos;

        $('#position-fields').append(`
          <div id="position${positionId}">
            <p>
              Year: <input type="text" name="year${positionId}" class="form-control"/>
              Position: <textarea name="desc${positionId}" rows="6" cols="80" class="form-control"></textarea>
              <input type="button" class="remove-pos btn btn-outline-secondary px-2 py-0 mt-1"
                    data-id="${positionId}" value="-" />
            </p>
          </div>
        `);
      });

      $('#position-fields').on('click', '.remove-pos', function() {
        let id = $(this).data('id');
        $('#position' + id).remove();
      });

      // Adding education
      $('#addEdu').click(function(e) {
        e.preventDefault();

        if (countEdu >= 9) {
          alert("Maximum of nine education entries exceeded");
          return;
        }

        countEdu++;
        let eduId = countEdu;

        $('#education-fields').append(`
          <div id="education${eduId}">
            <p>
              Year: <input type="text" name="edu_year${eduId}" class="form-control"/>
              Institution: <input type="text" name="institution${eduId}" class="institution form-control"/>
              <input type="button" class="remove-edu btn btn-outline-secondary px-2 py-0 mt-1"
                    data-id="${eduId}" value="-" />
            </p>
          </div>
        `);

        // Apply autocomplete to all institution fields
        $(".institution").autocomplete({
          source: "autocomplete.php",
          minLength: 1
        });

      });

      // Remove Education (delegated handler for the "-" buttons)
      $('#education-fields').on('click', '.remove-edu', function() {
        let id = $(this).data('id');
        $('#education' + id).remove();
      });

    });


    // Form Validation (Client-side)
    document.getElementById('update-profile-form').addEventListener('submit', function(event) {
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