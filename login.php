<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];

    // Validate username (you can add more validation if needed)
    if ($username === 'User') {
        $_SESSION['currentUser'] = $username;
        http_response_code(200); // OK
        echo "Login successful.";
    } else {
        http_response_code(401); // Unauthorized
        echo "Invalid username.";
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo "Method not allowed.";
}
?>
