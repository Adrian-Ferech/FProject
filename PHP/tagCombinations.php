<?php
function syncTagCombinations(mysqli $conn): void
{   
    // Reset tag combinations as it must be rebuilt each time for optimal tag creation
    $conn->query("DELETE FROM tag_combinations");

    $projectsResult = $conn->query("
        SELECT Project_ID
        FROM projects
        WHERE Status = 1
    ");

    while ($project = $projectsResult->fetch_assoc()) {
        $projectID = (int)$project['Project_ID'];

        $stmt = $conn->prepare("
            SELECT t.Tag_ID, t.Tag_Name
            FROM project_tags pt
            JOIN tags t ON pt.Tag_ID = t.Tag_ID
            WHERE pt.Project_ID = ?
            ORDER BY t.Tag_ID ASC
        ");

        $stmt->bind_param("i", $projectID);
        $stmt->execute();
        $result = $stmt->get_result();

        $tags = [];

        while ($row = $result->fetch_assoc()) {
            $tags[] = [
                'id' => (int)$row['Tag_ID'],
                'name' => $row['Tag_Name']
            ];
        }

        if (count($tags) < 2) {
            continue;
        }
        // Loops through each tag combination in alphabetical order so there are no repeat tag combinations
        // etc (Maths + Science -> Science + Maths)
        for ($i = 0; $i < count($tags); $i++) {
            for ($j = $i + 1; $j < count($tags); $j++) {

                $tag1ID = min($tags[$i]['id'], $tags[$j]['id']);
                $tag2ID = max($tags[$i]['id'], $tags[$j]['id']);

                $tag1Name = $tags[$i]['id'] < $tags[$j]['id'] ? $tags[$i]['name'] : $tags[$j]['name'];
                $tag2Name = $tags[$i]['id'] < $tags[$j]['id'] ? $tags[$j]['name'] : $tags[$i]['name'];

                $combinationName = $tag1Name . " + " . $tag2Name;

                $insert = $conn->prepare("
                    INSERT IGNORE INTO tag_combinations 
                    (Tag1_ID, Tag2_ID, Combination_Name)
                    VALUES (?, ?, ?)
                ");

                $insert->bind_param("iis", $tag1ID, $tag2ID, $combinationName);
                $insert->execute();
            }
        }
    }
}
?>