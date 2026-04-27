<?php
require_once(__DIR__ . '/../PHP/db.php'); // Require DB connection to load all data the page needs.

$tags = []; // Load tags for students to filter with
$result = $conn->query("SELECT Tag_ID, Tag_Name FROM tags ORDER BY Tag_Name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
}

$tagCombinations = []; // Load tag combinations for students to also filter with
$result = $conn->query("SELECT Combination_ID, Combination_Name FROM tag_combinations ORDER BY Combination_Name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tagCombinations[] = $row;
    }
}

// FIlter variables
$filterType = $_GET['filter_type'] ?? '';
$filterId = $_GET['filter_id'] ?? '';

$projects = [];
// Base sql query
$sql = "
    SELECT DISTINCT p.Project_ID, p.Title, p.Description
    FROM projects p
";

$params = [];
$types = "";
// Filter projects by tags they have
if ($filterType === 'tag' && !empty($filterId)) {
    $sql .= "
        JOIN project_tags pt ON p.Project_ID = pt.Project_ID
        WHERE p.Status = 1
        AND pt.Tag_ID = ?
    ";

    $params[] = (int)$filterId;
    $types .= "i";
// filter project by tag-combinations they have
} elseif ($filterType === 'combo' && !empty($filterId)) {
    $sql .= "
        JOIN project_tags pt ON p.Project_ID = pt.Project_ID
        JOIN tag_combinations tc 
            ON pt.Tag_ID IN (tc.Tag1_ID, tc.Tag2_ID)
        WHERE p.Status = 1
        AND tc.Combination_ID = ?
        GROUP BY p.Project_ID, p.Title, p.Description
        HAVING COUNT(DISTINCT pt.Tag_ID) = 2
    ";

    $params[] = (int)$filterId;
    $types .= "i";
// If no filter then all active projects (1) are displayed
} else {
    $sql .= "
        WHERE p.Status = 1
    ";
}
// Order alphabetically
$sql .= " ORDER BY p.Title ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $projects[] = [
        'Project_ID' => $row['Project_ID'],
        'title' => $row['Title'],
        'description' => $row['Description']
    ];
}

$currentType = $_GET['filter_type'] ?? '';
$currentId = $_GET['filter_id'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../CSS/stylesheet1.css">
  <title>Project Navigation</title>
</head>
<body>
<!-- Navbar code -->
<ul class="navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="Main.php">Project listing</a></li>
  <li><a href="login.php">Login</a></li>
  <li><a href="lecturerPage.php">Lecturers</a></li>
</ul>

<div>
  <!-- Main page flexbox -->
  <div class="split">
    <!-- Applies filter for projects -->
    <form method="get" action="Main.php">
      <label for="projectFilter">Project categories:</label>

      <select id="projectFilter" onchange="applyFilter(this.value)">
        <option value="">All Projects</option>
        <!-- Displays all normal tags -->
        <optgroup label="Tags">
          <?php foreach ($tags as $tag): ?>
            <?php
              $value = 'tag-' . $tag['Tag_ID'];
              $selected = ($currentType === 'tag' && (string)$currentId === (string)$tag['Tag_ID']) ? 'selected' : '';
            ?>
            <option value="<?php echo $value; ?>" <?php echo $selected; ?>>
              <?php echo htmlspecialchars($tag['Tag_Name']); ?>
            </option>
          <?php endforeach; ?>
        </optgroup>
        <!-- Displays all tag-combos in the tag-combination table -->
        <optgroup label="Tag Combinations">
          <?php foreach ($tagCombinations as $combo): ?>
            <?php
              $value = 'combo-' . $combo['Combination_ID'];
              $selected = ($currentType === 'combo' && (string)$currentId === (string)$combo['Combination_ID']) ? 'selected' : '';
            ?>
            <option value="<?php echo $value; ?>" <?php echo $selected; ?>>
              <?php echo htmlspecialchars($combo['Combination_Name']); ?>
            </option>
          <?php endforeach; ?>
        </optgroup>
      </select>
    </form>

    <h1>Final Year Project Listing</h1>

  </div>

  <?php if (!empty($projects)): ?>

  <div class="projectsWrapper">

    <?php foreach ($projects as $project): ?>
      <div class="container">
        <div class="projectTitle">
          <?php echo htmlspecialchars($project['title']); ?>
        </div>

        <div class="projectDescription">
          <?php echo htmlspecialchars($project['description']); ?>
        </div>

        <div class="projectLink">
          <a href="../PHP/projectDetails.php?id=<?php echo $project['Project_ID']; ?>">
            View Project
          </a>
        </div>
      </div>
    <?php endforeach; ?>

  </div>

  <?php else: ?>
    <p>No projects found.</p>
  <?php endif; ?>

</div>

<script>
function applyFilter(value) {
    // If empty it will reload same page, (clear page)
    if (value === "") {
        window.location.href = "Main.php";
        return;
    }

    const parts = value.split("-");
    const type = parts[0];
    const id = parts[1];
    // Reloads page with filtertype and filterID, encodeURIComponent allows for safe inputs only
    window.location.href = "Main.php?filter_type=" + encodeURIComponent(type) + "&filter_id=" + encodeURIComponent(id);
}
</script>

</body>
</html>