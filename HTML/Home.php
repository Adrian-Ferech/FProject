<?php require_once '../PHP/sortableData.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
  <link rel="stylesheet" href="../CSS/stylesheet1.css">
  <title>Home page</title>
</head>
<body>
  <!-- Navbar code -->
<ul class = "navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="Main.php">Project listing</a></li>
  <li><a href="login.php">Login</a></li>
  <li><a href="lecturerPage.php">Lecturers</a></li>
</ul>

<div>
  <div class = "homeTitle">
    <h1> Home Page </h1>
  </div>
  <!-- Start of main content and acts as vertical flexbox -->
  <div class = "projectRanking">
    <!-- Left column -->
    <div class = "projectPreferences">
      <h2 class = "par1"> rank your top 3 project preferences:</h2>

      <ul id="rankingList">

      <?php foreach ($projects as $project): // Loop and display each project ?>

      <li data-id="<?php echo $project['Project_ID']; ?>">
        <?php echo htmlspecialchars($project['title']); ?>
    
        <form action="../PHP/removePreference.php" method="post" style="display:inline;">
            <input type="hidden" name="project_id" value="<?php echo $project['Project_ID']; ?>">
            <button type="submit">Remove</button>
        </form>
      </li>

      <?php endforeach; ?>

      </ul>

      <button class="preferenceButton" onclick="getOrder()">Save Ranking</button>

      <script src="../JS/sortable.js"></script>
    </div>
    <!-- Right column -->
    <div class = "supportSection">
      <div>
        <h2 class = "title1"> Support: </h2>
      </div>
      <div>
        <h3 class = "title2"> Tips for selecting a Final Year Project: </h3>
        <p class = "desc1"> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. </p>
      </div>
      <div>
        <h3 class = "title3"> Relevant links / Support material</h3>
        <p class = "desc2"> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. </p>
      </div>
    </div>
  </div>
</div>


</body>
</html>