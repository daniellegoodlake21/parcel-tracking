<!DOCTYPE html>
    <head>
        <title>Place Order</title>
        <link rel="stylesheet" type="text/css" href="parcelTracker.css" media="screen"/>
    </head>
    <body>
    <div id="webpageHeader">
            <h1>Parcel Tracking System</h1>
        </div>
    <div id="navigationBar">
            <br>
            <a href="index.php" id="homeLink">Home</a>
            <?php
            $sessionName = session_name("ParcelTrackingSystemSession");
            session_start();
            if (!empty($_SESSION['RegisteredUserID']))
            {
                echo "<a href=\"parceltracker_logout.php\" id=\"loginLink\">Logout</a>";
            }
            else
            {
                echo "<a href=\"parceltracker_login.php\" id=\"loginLink\">Login</a>";
                echo "<a href=\"parceltracker_register.php\" id=\"registerLink\">Register</a>";
            }
            ?>
            <a href="parceltracker_products.php" id="productsLink" class="currentPageLink">Products</a>
            <a href="parceltracker_list_parcels.php" id="listParcelsLink">List Parcels</a>
            <a href="parceltracker_search_parcels.php" id="searchLink">Search Parcels</a>
        </div>
        <?php
        require "scripts/parcel_manager.php";
        require "scripts/user_manager.php";
        require "scripts/product_manager.php";
        require "scripts/connection_manager.php";
        require "scripts/validation_manager.php";
        if (!empty($_SESSION['RegisteredUserID']))
        {
            echo "<div id=\"registeredUserSidebar\">
            <h2>Account</h2>
            <a href=\"parceltracker_my_parcels.php\" id=\"myParcelsLink\">My Parcels</a>
            <br>
            <br>
            <a href=\"parceltracker_shopping_cart.php\" id=\"shoppingCartLink\">Shopping Cart</a>
            <br>";
            if (userIsAdmin())
            {
                echo "<h3>Parcel Tracking System Management</h3>
                <br>
                <br>
                <a href=\"parceltracker_manage_users.php\" id=\"manageUsersLink\">Manage Users</a>
                <br>
                <br>
                <a href=\"parceltracker_manage_products.php\" id=\"manageProductsLink\">Manage Products</a>
                <br>
                <br>
                <a href=\"parceltracker_manage_parcels.php\" id=\"manageParcelsLink\">Manage Parcels</a>
            </div>";
            }
            else
            {
                echo "</div>";
            }
        }
            // retrieve a list of products from the database
            $sessionID = getSessionIDForUser($_SESSION['RegisteredUserID']);
            $products = getShoppingCartProducts($sessionID);
            if (isset($_POST['btnPlaceOrder']) and isCollectivePlaceOrderDataValid())
            {
                foreach ($products as $product)
                {
                    $productID = $product['ProductID'];
                    $productQuantity = $product['ProductQuantity'];
                    $weight = $product['Weight'] * $productQuantity;
                    $addressID = createAddress();
                    createParcel($_SESSION['RegisteredUserID'], $addressID, $productID, $weight, $productQuantity);
                }
                resetShoppingCart($_SESSION['RegisteredUserID']);
            }
            else
            {
                $totalPrice = 0;
                // display the products information
                echo "
                <div id=\"productDetails\">
                    <h3 class=\"fullWidthMessage\">Order Products</h3>
                    <ol>";
                    foreach ($products as $product)
                    {
                        $totalPrice += $product['Price'] * $product['ProductQuantity'];
                        echo "<li><strong>" . $product['ProductName'] . ": </strong><br>Quantity: " . $product['ProductQuantity'] . "<br>Total Price for this item: £" . $product['Price']*$product['ProductQuantity'] . "</li>";
                    }
                    echo "</ol>
                </div>";
                // display the form to fill out with address details, delivery priority and preferred time slot
                $userNameDetails = getUserName($_SESSION['RegisteredUserID']);
                echo "
                <form id=\"orderProductsForm\" action=\"parceltracker_place_order.php\" method=\"post\">
                    <h3>Address Details</h3>    
                    <label for=\"recipientFirstName\">Recipient First Name</label>
                    <br>
                    <input type=\"text\" name=\"RecipientFirstName\" id=\"recipientFirstName\" value=\"" . $userNameDetails['FirstName'] . "\" required/>
                    <br>
                    <label for=\"recipientSurname\">Recipient Surname</label>
                    <br>
                    <input type=\"text\" name=\"RecipientSurname\" id=\"recipientSurname\" value=\"" . $userNameDetails['Surname'] . "\" required/>
                    <br>
                    <label for=\"addressLine1\">Address Line 1</label>
                    <br>
                    <input type=\"text\" name=\"AddressLine1\" id=\"addressLine1\" required/>
                    <br>
                    <label for=\"addressLine2\">Address Line 2</label>
                    <br>
                    <input type=\"text\" name=\"AddressLine2\" id=\"addressLine2\"/>
                    <br>
                    <label for=\"town\">Town</label>
                    <br>
                    <input type=\"text\" name=\"Town\" id=\"town\" required/>
                    <br>
                    <label for=\"postcode\">Postcode</label>
                    <br>
                    <input type=\"text\" name=\"Postcode\" id=\"postcode\" required/>
                    <br>
                    <h3>Delivery Options</h3>
                    <label for=\"deliveryOption\">Select A Delivery Priority:</label>
                    <br>
                    <br>
                    <select name=\"DeliveryOption\" id=\"deliveryOption\">
                        <option value=\"Next Day Delivery\">Next Day Delivery</option>
                        <option value=\"2-Day Tracked\">2-Day Tracked</option>
                        <option value=\"7-Day Delivery\">7-Day Delivery</option>
                        <option value=\"Standard Delivery\">Standard Delivery</option>
                    </select>
                    <br>
                    <br>
                    <label for=\"preferredTimeSlot\">Select A Preferred Time Slot:</label>
                    <br>
                    <br>
                    <select name=\"PreferredTimeSlot\" id=\"preferredTimeSlot\">
                        <option value=\"9.00-12.00\">9.00-12.00</option>
                        <option value=\"12.00-15.00\">12.00-15.00</option>
                        <option value=\"15.00-18.00\">15.00-18.00</option>
                        <option value=\"18.00-21.00\">18.00-21.00</option>
                    </select>
                    <br>
                    <br>
                    <label for=\"safePlace\">Safe Place:</label>
                    <br>
                    <input type=\"text\" name=\"SafePlace\" id=\"safePlace\"/>
                    <br>
                    <br>
                    <label for=\"neighbourHouseNumber\">Neighbour House Number:</label>
                    <br>
                    <input type=\"number\" name=\"NeighbourHouseNumber\" id=\"neighbourHouseNumber\"/>
                    <br>
                    <br>
                    <p class=\"message\"><strong>Total Price: £" . $totalPrice . "</strong></p>
                    <input type=\"hidden\" type=\"text\" name=\"Weight\" id=\"weight\" value=\"1\"/>
                    <input type=\"hidden\" type=\"text\" name=\"ProductQuantity\" id=\"productQuantity\" value=\"1\"/>
                    <input type=\"submit\" name=\"btnPlaceOrder\" id=\"btnPlaceOrder\" value=\"Place Order\"/>
                    <br>
                    <br>
                </form>";
            }
        ?>
    </body>
</html>