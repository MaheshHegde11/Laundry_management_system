<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "project_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables to hold user input
$name = isset($_POST["username"]) ? $_POST["username"] : '';
$email = isset($_POST["email"]) ? $_POST["email"] : '';
$phone = isset($_POST["phone"]) ? $_POST["phone"] : '';
$password = isset($_POST["password"]) ? $_POST["password"] : '';
$confirm_password = isset($_POST["confirmPassword"]) ? $_POST["confirmPassword"] : '';

// Build query string for preserving user input
$queryString = http_build_query([
    'username' => $name,
    'email' => $email,
    'phone' => $phone
]);

// Validate POST data exists
if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
    header("Location: sign_in.html?error=All fields are required&" . $queryString);
    exit();
}

// Verify CAPTCHA first
if (!isset($_POST['g-recaptcha-response'])) {
    header("Location: sign_in.html?error=CAPTCHA verification failed&" . $queryString);
    exit();
}

$secretKey = "6LdUtTgrAAAAANkwtKlOLwYqDLTIn4P8FhKctxBk";
$captchaResponse = $_POST['g-recaptcha-response'];

// Verify CAPTCHA with Google
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => $secretKey,
    'response' => $captchaResponse
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
$responseKeys = json_decode($result, true);

if (!$responseKeys["success"]) {
    header("Location: sign_in.html?error=CAPTCHA verification failed. Please try again.&" . $queryString);
    exit();
}

function validateUserInput($conn, $name, $email, $phone, $password, $confirm_password)
{
    // Trim inputs
    $name = trim($name);
    $email = trim($email);
    $phone = trim($phone);
    $password = trim($password);
    $confirm_password = trim($confirm_password);

    // Validate Name
    if (empty($name) || !preg_match("/^[a-zA-Z ]+$/", $name)) {
        return "Invalid name. Only letters and spaces are allowed.";
    }

    // Validate Email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }

    // Validate Phone
    if (empty($phone) || !preg_match("/^\d{10}$/", $phone)) {
        return "Invalid phone number. It must be 10 digits long.";
    }

    // Check for existing phone number
    $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return "Phone number already exists";
    }

    // Validate Password
    if (empty($password) || !preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,12}$/', $password)) {
        return "Password must be 8-12 characters long, with at least one letter and one number.";
    }

    // Validate Confirm Password
    if ($password !== $confirm_password) {
        return "Passwords do not match.";
    }

    return true;
}

$res = validateUserInput($conn, $name, $email, $phone, $password, $confirm_password);

if ($res === true) {
    // Secure password hashing
    $pass = password_hash($password, PASSWORD_DEFAULT);

    // Prepared statement for insertion
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $pass);

    if ($stmt->execute()) {
        header("Location: login.php?success=Registration successful! You can now login.&" . $queryString);
        exit();
    } else {
        header("Location: sign_in.php?error=Database error: " . urlencode($conn->error) . "&" . $queryString);
        exit();
    }
} else {
    header("Location: sign_in.php?error=" . urlencode($res) . "&" . $queryString);
    exit();
}

$conn->close();
?>