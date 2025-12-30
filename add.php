<!-- Inserting to the database -->

<!----------------- The Model ------------------------>
<?php
session_start();
// Including database connection code 
require_once "pdo.php";

// Handling Insert new Profile
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

    // Validating Positions   
    for ($i = 1; $i <= 9; $i++) {
      if (!isset($_POST['year' . $i])) continue;
      if (!isset($_POST['desc' . $i])) continue;
      $year = $_POST['year' . $i];
      $desc = $_POST['desc' . $i];
      if (strlen($year) == 0 || strlen($desc) == 0) {
        $_SESSION['error'] = "All fields are required";
        header("Location: add.php");
        return;
      }
      if (!is_numeric($year)) {
        $_SESSION['error'] = "Position year must be numeric";
        header("Location: add.php");
        return;
      }
    }

    // Validating Education   
    for ($i = 1; $i <= 9; $i++) {
      if (!isset($_POST['year' . $i])) continue;
      if (!isset($_POST['institution' . $i])) continue;
      $year = $_POST['year' . $i];
      $institution = $_POST['institution' . $i];
      if (strlen($year) == 0 || strlen($institution) == 0) {
        $_SESSION['error'] = "All fields are required";
        header("Location: add.php");
        return;
      }
      if (!is_numeric($year)) {
        $_SESSION['error'] = "Education year must be numeric";
        header("Location: add.php");
        return;
      }
    }

    // Inserting profile into the Database
    $sql = "INSERT INTO profile (user_id, first_name, last_name, email, headline, summary)
            VALUES (:user_id, :first_name, :last_name, :email, :headline, :summary)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':user_id'    => $_SESSION['user_id'],
      ':first_name' => $_POST['first_name'],
      ':last_name' => $_POST['last_name'],
      ':email' => $_POST['email'],
      ':headline' => $_POST['headline'],
      ':summary' => $_POST['summary']
    ));

    // Inserting Positions into the Database
    // We use the last ID with AUTO_INCREMENT inserted into the database
    $profile_id = $pdo->lastInsertId();
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
      if (!isset($_POST['year' . $i])) continue;
      if (!isset($_POST['desc' . $i])) continue;
      $year = $_POST['year' . $i];
      $desc = $_POST['desc' . $i];

      $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description)
                            VALUES (:pid, :rank, :year, :desc)');
      $stmt->execute(
        array(
          ':pid' => $profile_id,
          'rank' => $rank,
          ':year' => $year,
          ':desc' => $desc
        )
      );
      $rank++;
    }

    // Inserting Educations into the Database
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

        $stmt = $pdo->prepare('INSERT INTO institution (name) VALUES (:name)');
        $stmt->execute(array('name' => $institution));
        $institution_id = $pdo->lastInsertId();
      }
      // Inserting Educations
      $stmt = $pdo->prepare('INSERT INTO Education (profile_id, institution_id, rank, year)
                             VALUES (:pid, :iid, :rank, :year)');
      $stmt->execute(
        array(
          ':pid' => $profile_id,
          ':iid' => $institution_id,
          ':rank' => $rank,
          ':year' => $edu_year
        )
      );
      $rank++;
    }

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
  <!-- Bootstrap Library -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- jQuery UI CSS -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

  <!-- jQuery UI JS -->
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
        <!-- <input type="text" class="form-control" name="summary"> -->
        <textarea class="form-control" name="summary" rows="4"></textarea>
      </p>
      <p>Position:
        <input type="button" id="addPos" class="btn btn-secondary px-2 py-0" name="position" value="+">
      <div id="position-fields"></div>
      </p>
      <p>Education:
        <input type="button" id="addEdu" class="btn btn-secondary px-2 py-0" name="education" value="+">
      <div id="education-fields"></div>
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

    // When the plus button is being clicked, we take the #position-fields div and append the html code
    // for the positions.
    let countPos = 0;
    let countEdu = 0;
    $(document).ready(function() {

      // Adding positions
      // -----------------
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
              <input type="button" class="remove-pos btn btn-outline-secondary px-2 py-0 mt-1" data-id="${positionId}" value="-" />
            </p>
          </div>
        `);
      });
      // Remove Position (delegated handler for the "-" buttons)
      $('#position-fields').on('click', '.remove-pos', function() {
        let id = $(this).data('id');
        $('#position' + id).remove();
      });


      //  Adding education
      // -----------------
      $('#addEdu').click(function(event) {
        event.preventDefault();
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
              Institution: <input type="text" class="institution form-control" name="institution${eduId}"/>
              <input type="button" class="remove-edu btn btn-outline-secondary px-2 py-0 mt-1"
                    data-id="${eduId}" value="-" />
            </p>
          </div>
        `);

        // Apply autocomplete to all institution fields
        $(".institution").autocomplete({
          source: "autocomplete.php"
        });

      });

      // Remove Education (delegated handler for the "-" buttons)
      $('#education-fields').on('click', '.remove-edu', function() {
        let id = $(this).data('id');
        $('#education' + id).remove();
      });



    });


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