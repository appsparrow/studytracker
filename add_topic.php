<?php
session_start();

// Check if the user is logged in (you might have a different way to check this)
if (!isset($_SESSION['currentUser'])) {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

$currentUser = $_SESSION['currentUser'];
$topicsFile = "data/" . $currentUser . "_topics.json";

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $subject = $_POST["subject"];
    $importance = $_POST["importance"];
    $topicName = $_POST["topic-name"];
    $subtopics = isset($_POST["subtopics"]) ? array_map('trim', explode(',', $_POST["subtopics"])) : [];

    // Validate the data (you can add more validation if needed)
    if (empty($subject) || empty($topicName)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Subject and Topic Name are required."]);
        exit;
    }

    // Read the existing JSON data
    $jsonData = file_exists($topicsFile) ? file_get_contents($topicsFile) : '{"DAT_Topics": []}';
    $data = json_decode($jsonData, true);

    if ($data === null) {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Error decoding JSON."]);
        exit;
    }

    // Create the new topic
    $newTopic = [
        "name" => $topicName,
        "subtopics" => $subtopics
    ];

    // Find the subject index
    $subjectIndex = -1;
    foreach ($data['DAT_Topics'] as $index => $s) {
        if ($s['subject'] === $subject) {
            $subjectIndex = $index;
            break;
        }
    }

    // Add the new topic to the existing subject or create a new subject
    if ($subjectIndex !== -1) {
        $data['DAT_Topics'][$subjectIndex]['topics'][] = $newTopic;
    } else {
        $newSubject = [
            "subject" => $subject,
            "importance" => $importance,
            "topics" => [$newTopic]
        ];
        $data['DAT_Topics'][] = $newSubject;
    }

    // Encode the updated data back to JSON
    $updatedJsonData = json_encode($data, JSON_PRETTY_PRINT);

    // Write the updated JSON data back to the file
    if (file_put_contents($topicsFile, $updatedJsonData)) {
        http_response_code(200); // OK
        echo json_encode(["message" => "Topic added successfully."]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Error writing to JSON file."]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Method not allowed."]);
}
?>
