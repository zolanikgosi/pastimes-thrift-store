<?php
session_start();
include 'DBConn.php';
?>

<?php
// Fetch all items from the database
$query = "SELECT * FROM tbl_item";
$result = $dbConnection->query($query);

// Initialize items array
$items = [];
if ($result->num_rows > 0) {
    // Fetch each row as an associative array and push it into the $items array
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'item_id' => $row['item_id'], // Add item_id here
            'item_name' => $row['item_name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'image_path' => $row['image_path'],
            'category' => $row['category']
        ];
    }
} else {
    echo "No items found!";
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart function
if (isset($_GET['add_to_cart'])) {
    $item_id = $_GET['add_to_cart'];

    // Query to fetch item details from the database
    $query = "SELECT * FROM tbl_item WHERE item_id = ?";
    $stmt = $dbConnection->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();

        // Check if the item is already in the cart
        $item_exists_in_cart = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['item_id'] == $item['item_id']) {
                // If item is already in the cart, increase the quantity by 1
                $cart_item['quantity'] += 1;
                $item_exists_in_cart = true;
                break;
            }
        }

        // If item doesn't exist in cart, add it with quantity 1
        if (!$item_exists_in_cart) {
            // Add item to the cart with initial quantity of 1
            $item['quantity'] = 1;
            $_SESSION['cart'][] = $item;
        }

        $_SESSION['cart_message'] = $item_exists_in_cart ? "Item quantity updated in cart!" : "Item successfully added to cart!";
    } else {
        $_SESSION['cart_message'] = "Item not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes Catalogue</title>
    <link rel="stylesheet" href="style.css">
</head>

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
<!-- Display cart message if an item is added -->
<?php
if (isset($_SESSION['cart_message'])) {
    echo "<p>{$_SESSION['cart_message']}</p>";
    unset($_SESSION['cart_message']); // Clear message after showing it
}
?>


<body>

    <h1>Catalogue</h1>

    <!-- Table Display -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Image</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td>R <?php echo htmlspecialchars($item['price']); ?></td>
                    <td>
                        <!-- Display image, fallback to default image if image path is invalid -->
                        <img src="<?php echo file_exists($item['image_path']) ? htmlspecialchars($item['image_path']) : 'path/to/default-image.jpg'; ?>"
                            alt="<?php echo htmlspecialchars($item['item_name']); ?>" width="100" height="100">
                    </td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td>
                        <!-- Add to Cart button with link to add item -->
                        <a href="?add_to_cart=<?php echo $item['item_id']; ?>">
                            <button class="add-to-cart-btn">Add to Cart</button>
                            <!-- Show Cart Button -->
                            <p><a href="cart.php"><button>Show Cart</button></a></p>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>