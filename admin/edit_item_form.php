<?php
include 'db.php';

$id = $_GET['id'];
$service = $_GET['service'] ?? 'wash_fold';

$column_map = [
  'wash_fold' => 'price_wash_fold',
  'wash_iron' => 'price_wash_iron',
  'steam_iron' => 'price_steam_iron',
  'dry_clean' => 'price_dry_clean',
];
$price_column = $column_map[$service];

$query = "SELECT * FROM price_list WHERE id = $id";
$result = mysqli_query($conn, $query);
$item = mysqli_fetch_assoc($result);

echo '<form method="POST" action="update_item.php" enctype="multipart/form-data">';
echo '<input type="hidden" name="id" value="'. $item['id'] .'">';
echo '<input type="hidden" name="service" value="'. $service .'">';
echo '<label class="block mb-2">Item Name</label>';
echo '<input type="text" name="name" value="'. htmlspecialchars($item['name']) .'" class="w-full border p-2 mb-4">';
echo '<label class="block mb-2">Price</label>';
echo '<input type="number" step="0.01" name="price" value="'. $item[$price_column] .'" class="w-full border p-2 mb-4">';
echo '<div>';
echo '<label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>';
echo '<img src="'. htmlspecialchars($item['image_url'] ?? 'image/default.png') .'" alt="Current Image" class="h-12 w-12 object-cover rounded mb-2" />';
echo '<input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">';
echo '</div>';
echo '<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>';
echo '</form>';
?>
