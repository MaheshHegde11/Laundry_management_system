<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "project_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify reCAPTCHA first
if (!isset($_POST['g-recaptcha-response'])) {
    $_SESSION['login_error'] = "Please complete the CAPTCHA verification";
    header("Location: login.php");
    exit();
}

$secretKey = "6LdUtTgrAAAAANkwtKlOLwYqDLTIn4P8FhKctxBk"; // Your secret key
$captchaResponse = $_POST['g-recaptcha-response'];

// Verify CAPTCHA with Google
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => $secretKey,
    'response' => $captchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$response = json_decode($result, true);

if (!$response['success']) {
    $_SESSION['login_error'] = "CAPTCHA verification failed. Please try again.";
    header("Location: login.php");
    exit();
}

// Proceed with normal login validation if CAPTCHA passes
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';

// Validate credentials
$stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION["user_id"] = $user["customer_id"];
        $_SESSION["phone"] = $user["phone"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["logged_in"] = true;
        header("Location: place_order.php");
        exit();
    }
}

// If we get here, login failed
$_SESSION['login_error'] = "Invalid phone number or password";
header("Location: login.php");
exit();
