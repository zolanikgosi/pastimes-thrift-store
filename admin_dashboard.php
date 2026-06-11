<?php
session_start();

// Include database connection
include 'DBConn.php';

// Prepared statement for adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $email = $_POST['email'];
    $status = 'pending'; // Default status for new users is 'pending'

    $stmt = $dbConnection->prepare("INSERT INTO tbluser (name, username, password, email, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $username, $password, $email, $status);

    if ($stmt->execute()) {
        echo "User added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Prepared statement for adding a new seller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_seller'])) {
    $name = $_POST['seller_name'];
    $username = $_POST['seller_username'];
    $password = password_hash($_POST['seller_password'], PASSWORD_BCRYPT); // Hash the password
    $email = $_POST['seller_email'];
    $status = 'pending'; // Default status for new sellers is 'pending'

    $stmt = $dbConnection->prepare("INSERT INTO tblseller (name, username, password, email, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $username, $password, $email, $status);

    if ($stmt->execute()) {
        echo "Seller added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Update user status
if (isset($_GET['action'], $_GET['userID']) && ($_GET['action'] === 'verify' || $_GET['action'] === 'reject')) {
    $userID = intval($_GET['userID']);
    $newStatus = $_GET['action'] === 'verify' ? 'verified' : 'rejected';

    $stmt = $dbConnection->prepare("UPDATE tbluser SET status=? WHERE userID=?");
    $stmt->bind_param("si", $newStatus, $userID);

    if ($stmt->execute()) {
        echo "User status updated to $newStatus!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Update seller status
if (isset($_GET['action'], $_GET['sellerID']) && ($_GET['action'] === 'verify_seller' || $_GET['action'] === 'reject_seller')) {
    $sellerID = intval($_GET['sellerID']);
    $newStatus = $_GET['action'] === 'verify_seller' ? 'verified' : 'rejected';

    $stmt = $dbConnection->prepare("UPDATE tblseller SET status=? WHERE sellerID=?");
    $stmt->bind_param("si", $newStatus, $sellerID);

    if ($stmt->execute()) {
        echo "Seller status updated to $newStatus!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deleting a user
if (isset($_GET['action'], $_GET['userID']) && $_GET['action'] === 'delete') {
    $userID = intval($_GET['userID']);
    $stmt = $dbConnection->prepare("DELETE FROM tbluser WHERE userID=?");
    $stmt->bind_param("i", $userID);

    if ($stmt->execute()) {
        echo "User deleted!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deleting a seller
if (isset($_GET['action'], $_GET['sellerID']) && $_GET['action'] === 'delete_seller') {
    $sellerID = intval($_GET['sellerID']);
    $stmt = $dbConnection->prepare("DELETE FROM tblseller WHERE sellerID=?");
    $stmt->bind_param("i", $sellerID);

    if ($stmt->execute()) {
        echo "Seller deleted!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Filter users by status
$statusFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT * FROM tbluser";
if ($statusFilter !== 'all') {
    $query .= " WHERE status=?";
}
$stmt = $dbConnection->prepare($query);
if ($statusFilter !== 'all') {
    $stmt->bind_param("s", $statusFilter);
}
$stmt->execute();
$result = $stmt->get_result();

// Filter sellers by status
$sellerFilter = isset($_GET['seller_filter']) ? $_GET['seller_filter'] : 'all';
$sellerQuery = "SELECT * FROM tblseller";
if ($sellerFilter !== 'all') {
    $sellerQuery .= " WHERE status=?";
}
$sellerStmt = $dbConnection->prepare($sellerQuery);
if ($sellerFilter !== 'all') {
    $sellerStmt->bind_param("s", $sellerFilter);
}
$sellerStmt->execute();
$sellerResult = $sellerStmt->get_result();

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
    $itemDescription = $_POST['item_description'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $stockQuantity = $_POST['stock_quantity'];
    $category = $_POST['category'];
    $clothesCondition = $_POST['clothes_condition'];
    $itemID = isset($_POST['item_id']) ? $_POST['item_id'] : null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'C:\\wamp\\www\\pastimes\\_Images\\'; // Directory to store images
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
        $updateQuery = "UPDATE tbl_item SET item_name = '$itemName', item_description = '$itemDescription', price = '$price', size = '$size', color = '$color', stock_quantity = '$stockQuantity', category = '$category', clothesCondition = '$clothesCondition', image_path = '$imagePath' WHERE item_id = $itemID";
        if ($dbConnection->query($updateQuery)) {
            echo "Item updated successfully!";
        } else {
            echo "Error: " . $dbConnection->error;
        }
    } else {
        // Insert new item
        $insertQuery = "INSERT INTO tbl_item (item_name, item_description, price, size, color, stock_quantity, category, clothesCondition, image_path) VALUES ('$itemName', '$itemDescription', '$price', '$size', '$color', '$stockQuantity', '$category', '$clothesCondition', '$imagePath')";
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
    $deleteQuery = "DELETE FROM tbl_item WHERE item_id=$itemID";
    if ($dbConnection->query($deleteQuery)) {
        echo "Item deleted successfully!";
    } else {
        echo "Error: " . $dbConnection->error;
    }
}

// Query to get clothing items
$itemQuery = "SELECT * FROM tbl_item";
$itemResult = $dbConnection->query($itemQuery);




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
    <title>Pastimes Admin Management </title>
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
    <div class="admin-container">
        <h1 class="admin-heading">Admin Dashboard</h1>
        <h2>Users</h2>
        <a href="logout.php" class="logout-btn">Logout</a>

        <!-- Filter Users -->
        <div class="admin-filter-links">
            <a href="?filter=all">All Users</a>
            <a href="?filter=pending">Pending</a>
            <a href="?filter=verified">Verified</a>
            <a href="?filter=rejected">Rejected</a>
        </div>

        <!-- Table of Users -->
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['userID']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="?action=verify&userID=<?php echo $row['userID']; ?>">Verify</a>
                                <a href="?action=reject&userID=<?php echo $row['userID']; ?>" class="delete">Reject</a>
                            <?php endif; ?>
                            <a href="?action=edit&userID=<?php echo $row['userID']; ?>">Edit</a>
                            <a href="?action=delete&userID=<?php echo $row['userID']; ?>" class="delete">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Add User Form -->
        <h2>Add User</h2>
        <form action="" method="POST">
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_user">Add User</button>
        </form>


        <!-- Filter Sellers -->
        <div class="admin-filter-links">
            <h2>Sellers</h2>
            <a href="?seller_filter=all">All Sellers</a>
            <a href="?seller_filter=pending">Pending</a>
            <a href="?seller_filter=verified">Verified</a>
            <a href="?seller_filter=rejected">Rejected</a>
        </div>

        <!-- Table of Sellers -->
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($sellerRow = $sellerResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sellerRow['sellerID']); ?></td>
                        <td><?php echo htmlspecialchars($sellerRow['name']); ?></td>
                        <td><?php echo htmlspecialchars($sellerRow['username']); ?></td>
                        <td><?php echo htmlspecialchars($sellerRow['email']); ?></td>
                        <td><?php echo htmlspecialchars($sellerRow['status']); ?></td>
                        <td>
                            <?php if ($sellerRow['status'] == 'pending'): ?>
                                <a href="?action=verify_seller&sellerID=<?php echo $sellerRow['sellerID']; ?>">Verify</a>
                                <a href="?action=reject_seller&sellerID=<?php echo $sellerRow['sellerID']; ?>" class="delete">Reject</a>
                            <?php endif; ?>
                            <a href="?action=delete_seller&sellerID=<?php echo $sellerRow['sellerID']; ?>" class="delete">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>


        <!-- Add Seller Form -->
        <h2>Add Seller</h2>
        <form action="" method="POST">
            <input type="text" name="seller_name" placeholder="Seller Name" required>
            <input type="text" name="seller_username" placeholder="Seller Username" required>
            <input type="email" name="seller_email" placeholder="Seller Email" required>
            <input type="password" name="seller_password" placeholder="Password" required>
            <button type="submit" name="add_seller">Add Seller</button>
        </form>

        <h2>Clothing Items List</h2>

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
            <th>Condition</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($item = $itemResult->fetch_assoc()): ?>
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
    </tbody>
</table>


<!-- Add New Item Form -->
<h2>Add New Item</h2>
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
    <!-- Image upload input -->
    <input type="file" name="image" accept="image/*" required>

    <button type="submit" name="<?php echo isset($itemToEdit) ? 'update_item' : 'add_item'; ?>">
        <?php echo isset($itemToEdit) ? 'Update Item' : 'Add Item'; ?>
    </button>
</form>


    </div>
</body>

</html>