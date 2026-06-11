<?php
session_start();

// Include database connection
include 'DBConn.php';

// Initialize variables
$itemToEdit = null; // Default, no item to edit

// Handle fetching the item to edit
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['itemID'])) {
    $itemID = $_GET['itemID'];
    $editQuery = "SELECT * FROM tbl_item WHERE item_id=$itemID";
    $result = $dbConnection->query($editQuery);
    $itemToEdit = $result->fetch_assoc(); // Fetch the item details to pre-populate the form
}


// Handle adding/updating item with image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = $_POST['item_name'];
    $itemDescription = $_POST['description'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $stockQuantity = $_POST['stock_quantity'];
    $category = $_POST['category'];
    $itemID = isset($_POST['item_id']) ? $_POST['item_id'] : null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "C:\wamp\wamp\www\pastimes\_images"; // Directory to store images
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . uniqid() . '_' . $imageName; // Prevent overwriting files

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file to the server
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo "Image uploaded successfully!";
        } else {
            echo "Image upload failed!";
        }
    }

    // Insert or update item
    if ($itemID) {
        // Update existing item
        $updateQuery = "UPDATE tbl_item SET item_name = '$itemName', description = '$Description', price = '$price', size = '$size', color = '$color', stock_quantity = '$stockQuantity', category = '$category', image_path = '$imagePath' WHERE item_id = $itemID";
        if ($dbConnection->query($updateQuery)) {
            echo "Item updated successfully!";
        } else {
            echo "Error: " . $dbConnection->error;
        }
    } else {
        // Insert new item
        $insertQuery = "INSERT INTO tbl_item (item_name,description, price, size, color, stock_quantity, category, image_path) VALUES ('$itemName', '$Description', '$price', '$size', '$color', '$stockQuantity', '$category', '$imagePath')";
        if ($dbConnection->query($insertQuery)) {
            echo "Item added successfully!";
        } else {
            echo "Error: " . $dbConnection->error;
        }
    }
}

// Handle deleting item
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $itemID = $_GET['itemID'];
    $deleteQuery = "DELETE FROM tbl_item WHERE item_id = $itemID";
    if ($dbConnection->query($deleteQuery)) {
        echo "Item deleted!";
    } else {
        echo "Error: " . $dbConnection->error;
    }
}

// Fetch all items
$query = "SELECT * FROM tbl_item";
$result = $dbConnection->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pastimes is your go-to online store for high-quality branded used clothing, offering top-notch fashion at affordable prices.">
    <meta name="keywords" content="clothing store, online shopping, used clothing, affordable fashion, Pastimes, fashion">
    <meta name="revisit" content="30 days">
    <meta http-equiv="refresh" content="30">
    <meta name="robots" content="noindex, nofollow">
    <title>Pastimes Seller Dashboard </title>
    <link rel="stylesheet" href="style.css">
   
</head>

<div class="container">

    <!-- Display logged-in user's name -->
    <?php
    if (isset($_SESSION['name'])) {
        // Display the logged-in message
        echo "<p>User " . htmlspecialchars($_SESSION['name']) . " is logged in.</p>";
    } else {
        // Display a message for users who are not logged in
        echo "<p>Welcome, guest! Please log in to access more features.</p>";
    }
    ?>
</div>

<body>
    <div class="seller-container">
        <h1 class="seller-heading">Seller Dashboard</h1>
        <a href="logout.php" class="logout-btn">Logout</a>

        <!-- Form to Add or Update an Item -->
        <form method="POST" action="" enctype="multipart/form-data" class="seller-form">
            <input type="hidden" name="item_id" value="<?php echo isset($itemToEdit) ? $itemToEdit['item_id'] : ''; ?>">
            <input type="text" name="item_name" placeholder="Item Name" value="<?php echo isset($itemToEdit) ? $itemToEdit['item_name'] : ''; ?>" required>
            <textarea name="description" placeholder="Item Description" required><?php echo isset($itemToEdit) ? $itemToEdit['description'] : ''; ?></textarea>
            <input type="number" name="price" placeholder="Price" step="0.01" value="<?php echo isset($itemToEdit) ? $itemToEdit['price'] : ''; ?>" required>
            <input type="text" name="size" placeholder="Size" value="<?php echo isset($itemToEdit) ? $itemToEdit['size'] : ''; ?>" required>
            <input type="text" name="color" placeholder="Color" value="<?php echo isset($itemToEdit) ? $itemToEdit['color'] : ''; ?>" required>
            <input type="number" name="stock_quantity" placeholder="Stock Quantity" value="<?php echo isset($itemToEdit) ? $itemToEdit['stock_quantity'] : ''; ?>" required>
            <select name="category" required>
                <option value="Men" <?php echo (isset($itemToEdit) && $itemToEdit['category'] === 'Men') ? 'selected' : ''; ?>>Men</option>
                <option value="Women" <?php echo (isset($itemToEdit) && $itemToEdit['category'] === 'Women') ? 'selected' : ''; ?>>Women</option>
             </select>

            <!-- Image upload input -->
            <input type="file" name="image" accept="image/*" required>

            <button type="submit" name="<?php echo isset($itemToEdit) ? 'update_item' : 'add_item'; ?>">
                <?php echo isset($itemToEdit) ? 'Update Item' : 'Add Item'; ?>
            </button>
        </form>


        <!-- Display List of Items -->
        <table class="seller-table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($item = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $item['item_id']; ?></td>
                            <td><?php echo htmlspecialchars($item['item_name'], ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($item['description'], ENT_QUOTES); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($item['size'], ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($item['color'], ENT_QUOTES); ?></td>
                            <td><?php echo $item['stock_quantity']; ?></td>
                            <td><?php echo htmlspecialchars($item['category'], ENT_QUOTES); ?></td>
                            <td>
                                <?php if ($item['image_path']): ?>
                                    <a href="<?php echo htmlspecialchars($item['image_path'], ENT_QUOTES); ?>" target="_blank">
                                        <img src="<?php echo htmlspecialchars($item['image_path'], ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($item['item_name'], ENT_QUOTES); ?>" style="width: 50px; height: 50px;">
                                    </a>
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&itemID=<?php echo $item['item_id']; ?>" class="edit-btn">Edit</a>
                                <a href="?action=delete&itemID=<?php echo $item['item_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

</html>