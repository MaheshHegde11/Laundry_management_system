<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category']; // Make sure your form uses category_id!
    $price_wash_fold = $_POST['price_wash_fold'] ?? 0;
    $price_wash_iron = $_POST['price_wash_iron'] ?? 0;
    $price_steam_iron = $_POST['price_steam_iron'] ?? 0;
    $price_dry_clean = $_POST['price_dry_clean'] ?? 0;

    // Handle image upload
    $image_url = 'images/default.png'; // fallback
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../images/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image_url = "images/" . $fileName; // store relative to project root
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO price_list (name, category_id, price_wash_fold, price_wash_iron, price_steam_iron, price_dry_clean, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidddds", $name, $category_id, $price_wash_fold, $price_wash_iron, $price_steam_iron, $price_dry_clean, $image_url);

    if ($stmt->execute()) {
        echo "Item added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
    exit;
}
?>

<form id="addItemForm" enctype="multipart/form-data" method="POST" action="add_item.php">
    <!-- form fields here -->
</form>
