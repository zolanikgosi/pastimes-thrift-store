<?php
session_start();
include 'DBConn.php';


// Initialize the cart and discount if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['discount'])) {
    $_SESSION['discount'] = 0;
}

$valid_discount_code = 'DISCOUNT123'; // Default discount code
$discount_percentage = 10; // Discount percentage (10%)

// Add item to cart function
function addToCart($item_id, $quantity)
{
    global $conn;

    // If item exists in the cart, increase the quantity
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] += $quantity;
    } else {
        // If item doesn't exist, fetch from the database and add to the cart
        $stmt = $conn->prepare("SELECT item_name, price, stock_quantity FROM tbl_item WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $_SESSION['cart'][$item_id] = [
                'item_name' => $item['item_name'],
                'price' => $item['price'],
                'quantity' => $quantity,
                'stock_quantity' => $item['stock_quantity']
            ];
        }
    }
}

// Remove item from cart function with a message when item is removed or not found
function removeFromCart($item_id)
{
    if (isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
        $_SESSION['cart_message'] = "Item has been removed from your cart.";
    } else {
        $_SESSION['cart_message'] = "Item not found in your cart.";
    }
}

// Empty the entire cart function with a message if the cart is already empty
function emptyCart()
{
    if (empty($_SESSION['cart'])) {
        $_SESSION['cart_message'] = "Your cart is already empty.";
    } else {
        $_SESSION['cart'] = [];
        $_SESSION['cart_message'] = "Your cart has been emptied.";
    }
}

// Generate a unique order number
function generateOrderNumber()
{
    return 'ORD-' . strtoupper(uniqid());
}

// Checkout function
function checkout()
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        header("Location: user_login.php?message=Please log in to complete your purchase.");
        exit();
    }

    $order_number = generateOrderNumber();
    $user_id = $_SESSION['user_id'];
    $total_price = calculateDiscountedTotal();

    $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_price, order_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sid", $order_number, $user_id, $total_price);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    foreach ($_SESSION['cart'] as $item_id => $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $item_id, $item['quantity'], $item['price']);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE tbl_item SET stock_quantity = stock_quantity - ? WHERE item_id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item_id);
        $stmt->execute();
    }

    emptyCart();
    header("Location: order_confirmation.php?order_number=" . $order_number);
    exit();
}

// Calculate the total price of items in the cart
function calculateTotal()
{
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['stock_quantity'];  // Changed to multiply by quantity
    }
    return $total;
}

// Calculate total price after discount
function calculateDiscountedTotal()
{
    $total = calculateTotal();
    if (isset($_SESSION['discount']) && $_SESSION['discount'] > 0) {
        $total -= ($total * $_SESSION['discount'] / 100);
    }
    return $total;
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['apply_discount'])) {
        $entered_code = trim($_POST['discount_code']);
        if ($entered_code === $valid_discount_code) {
            $_SESSION['discount'] = $discount_percentage;
            $message = "Discount code applied! You get 10% off.";
        } else {
            $_SESSION['discount'] = 0;
            $message = "Invalid discount code. Please try again.";
        }
    }
    if (isset($_POST['add'])) {
        $item_id = intval($_POST['item_id']);
        $quantity = intval($_POST['quantity']);
        addToCart($item_id, $quantity);
    }
    if (isset($_POST['remove'])) {
        $item_id = intval($_POST['item_id']);
        removeFromCart($item_id);
    }
    if (isset($_POST['checkout'])) {
        checkout();
    }
    if (isset($_POST['empty_cart'])) {
        emptyCart();
    }
    if (isset($_POST['continue_shopping'])) {
        header("Location: catalogue_table_form.php");
        exit();
    }

    header("Location: cart.php"); // Redirect to avoid form resubmission
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and process the form data
    $address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '';
    $postcode = isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : '';
    $city = isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '';

    // Basic validation: Ensure that none of the fields are empty
    if (empty($address) || empty($postcode) || empty($city)) {
        echo "All fields are required. Please fill out the address, postcode, and city.";
    } else {
        // Proceed with further processing (e.g., storing in the database or displaying)
        echo "Address: $address<br>";
        echo "Postal Code: $postcode<br>";
        echo "City: $city<br>";
    }
}

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
    <title>Pastimes Cart Page</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
            <img src="_images/pastimes_logo.jpg" alt="Pastimes Logo" width="150">
            </div>

            <div class="search-container">
                <input type="text" placeholder="Search for items..." class="search-bar" id="search-input">
                <button class="search-button" id="search-button">Search</button>
            </div>
            <script>
                document.getElementById('search-button').addEventListener('click', function() {
                    const query = document.getElementById('search-input').value;
                    if (query) {
                        // Perform the search action here. For example, you can redirect to a search results page:
                        alert('Searching for: ' + query); // Replace this line with your search functionality
                        // window.location.href = 'search-results.html?query=' + encodeURIComponent(query);
                    } else {
                        alert('Please enter a search term.');
                    }
                });

                // Optional: Enable searching by pressing Enter in the input field
                document.getElementById('search-input').addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        document.getElementById('search-button').click();
                    }
                });
            </script>

            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="catalogue.php">Catalogue</a></li>
                    <li class="currentPage"> Cart</li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </nav>
        </div>
    </header>


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

    <?php
    if (isset($_SESSION['cart_message'])) {
        echo "<p>{$_SESSION['cart_message']}</p>";
        unset($_SESSION['cart_message']); // Clear the message after displaying it
    }
    ?>


    <div class="cart-container">
        <h2>Your Cart</h2>
        <form method="post" action="cart.php">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($_SESSION['cart'])): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No items in cart.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($_SESSION['cart'] as $item_id => $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td>R<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['stock_quantity']; ?></td>
                                <td>R<?php echo number_format($item['price'] * $item['stock_quantity'], 2); ?></td>
                                <td>
                                    <button type="submit" name="remove" value="<?php echo $item_id; ?>">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="cart-buttons">
                <button type="submit" name="empty_cart">Empty Cart</button>
                <button type="submit" name="continue_shopping">Continue Shopping</button>
            </div>

        </form>

        <h3>Shipping Details</h3>
        <form method="post" action="checkout.php">
            <div class="shipping-form">
                <input
                    type="text"
                    name="address"
                    placeholder="Shipping Address"
                    required
                    value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                <input
                    type="text"
                    name="postcode"
                    placeholder="Postal Code"
                    required
                    value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>">
                <input
                    type="text"
                    name="city"
                    placeholder="City"
                    required
                    value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
            </div>

            <h3>Payment Method</h3>
            <select name="payment_method" id="payment_method" required>
                <option value="" disabled selected>Select Payment Method</option>
                <option value="paypal">PayPal</option>
                <option value="credit_card">Credit Card</option>
                <option value="debit_card">Debit Card</option>
                <option value="eft">EFT</option>
            </select>

            <!-- Payment Details Sections -->
            <div id="paypal_details" style="display: none;">
                <label>PayPal Email:</label>
                <input type="email" name="paypal_email" placeholder="Enter your PayPal email">
            </div>

            <div id="credit_card_details" style="display: none;">
                <label>Credit Card Number:</label>
                <input type="text" name="credit_card_number" placeholder="Enter your credit card number">
                <label>Expiration Date:</label>
                <input type="text" name="credit_card_expiry" placeholder="MM/YY">
                <label>CVV:</label>
                <input type="text" name="credit_card_cvv" placeholder="CVV">
            </div>

            <div id="debit_card_details" style="display: none;">
                <label>Debit Card Number:</label>
                <input type="text" name="debit_card_number" placeholder="Enter your debit card number">
                <label>Expiration Date:</label>
                <input type="text" name="debit_card_expiry" placeholder="MM/YY">
                <label>CVV:</label>
                <input type="text" name="debit_card_cvv" placeholder="CVV">
            </div>

            <div id="eft_details" style="display: none;">
                <label>Bank Name:</label>
                <input type="text" name="bank_name" placeholder="Enter your bank name">
                <label>Account Number:</label>
                <input type="text" name="bank_account" placeholder="Enter your account number">
                <label>Branch Code:</label>
                <input type="text" name="branch_code" placeholder="Enter branch code">
            </div>

            <script>
                document.getElementById('payment_method').addEventListener('change', function() {
                    const paypalDetails = document.getElementById('paypal_details');
                    const creditCardDetails = document.getElementById('credit_card_details');
                    const debitCardDetails = document.getElementById('debit_card_details');
                    const eftDetails = document.getElementById('eft_details');

                    paypalDetails.style.display = 'none';
                    creditCardDetails.style.display = 'none';
                    debitCardDetails.style.display = 'none';
                    eftDetails.style.display = 'none';

                    switch (this.value) {
                        case 'paypal':
                            paypalDetails.style.display = 'block';
                            break;
                        case 'credit_card':
                            creditCardDetails.style.display = 'block';
                            break;
                        case 'debit_card':
                            debitCardDetails.style.display = 'block';
                            break;
                        case 'eft':
                            eftDetails.style.display = 'block';
                            break;
                    }
                });
            </script>

            <!-- Discount Code Section -->
            <h3>Discount Code</h3>
            <p>Do you have a discount code? (Only one discount per order can be applied.)</p>
            <input type="text" name="discount_code" placeholder="Enter Discount Code">
            <button type="submit" name="apply_discount">Apply Discount</button>

            <?php if (isset($message)): ?>
                <p style="color:green;"><?php echo $message; ?></p>
            <?php endif; ?>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <p>Subtotal: R<?php echo number_format(calculateTotal(), 2); ?></p>
                <p>Discount: R<?php echo number_format(calculateTotal() - calculateDiscountedTotal(), 2); ?></p>
                <p>Shipping: FREE</p>
                <p>Total: R<?php echo number_format(calculateDiscountedTotal(), 2); ?></p>
            </div>


            <button type="submit" name="checkout">Proceed to Checkout</button>
        </form>
    </div>

    <footer>
        <!-- Menu -->
        <div id="footer-menu" class="footer-section">
            <!-- Bottom Navigation Menu -->

            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="catalogue.php">Catalogue</a></li>
                    <li class="currentPage">Cart</li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </nav>
        </div>


        <div id="footer-content">
            <!-- Lets Connect -->
            <div id="social-media-footer" class="footer-section">
                <h4>Lets Connect</h4>
                <div>
                    <a href="https://www.instagram.com/moni_que360" target="_blank">
                        <img src="_images/instagram_icon.jpg" alt="Instagram Icon" width="3%"> @kgosi.makuva</a>
                </div>
                
                <!-- Email -->
                <div>
                    <p><a href="mailto:tafi.makuva@gmail.com">tafi.makuva@gmail.com</a></p>
                </div>
            </div>
            <!-- Contact Information -->
            <div id="contact-info-footer-left" class="footer-section">
                <!-- Address -->
                <div class="address">
                    <h4><u>Address</u></h4>
                    <p><img src="_images/location_icon.jpg" alt="Address Icon" width="5%"> honeydew,s <br>
                        Eagle Canyon, <br>
                        Honeydew, Johannesburg, 2169</p>
                </div>
                <!-- Working Hours -->
                <div class="working-hours">
                    <h4><u>Working hours:</u></h4>
                    <p class="no-break">Monday to Saturday: Open 24 hours</p>
                    <p class="no-break">Sunday: Closed</p>
                </div>
            </div>
        </div>
    </footer>

</html>