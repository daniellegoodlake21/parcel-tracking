<!DOCTYPE html>
    <head>
        <title>Shopping Cart</title>
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
            <a href="parceltracker_products.php" id="productsLink">Products</a>
            <a href="parceltracker_list_parcels.php" id="listParcelsLink">List Parcels</a>
            <a href="parceltracker_search_parcels.php" id="searchLink">Search Parcels</a>
        </div>
        <?php
        require "scripts/product_manager.php";
        require "scripts/user_manager.php";
        require "scripts/connection_manager.php";
        require "scripts/validation_manager.php";
        if (!empty($_SESSION['RegisteredUserID']))
        {
            echo "<div id=\"registeredUserSidebar\">
            <h2>Account</h2>
            <a href=\"parceltracker_my_parcels.php\" id=\"myParcelsLink\">My Parcels</a>
            <br>
            <br>
            <a href=\"parceltracker_shopping_cart.php\" id=\"shoppingCartLink\" class=\"currentPageLink\">Shopping Cart</a>
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
        $userSessionID = getSessionIDForUser($_SESSION['RegisteredUserID']);
        if (isset($_POST['btnAddToShoppingCart']))
        {
            $productID = $_POST['ProductID'];
            $productQuantity = $_POST['ProductQuantity'];
            addProductToShoppingCart($productID, $productQuantity, $userSessionID);
        }
        elseif (isset($_POST['btnRemoveProductFromShoppingCart']))
        {
            $productID = $_POST['ProductID'];
            removeProductFromShoppingCart($productID, $userSessionID);
        }
        $shoppingCartProducts = getShoppingCartProducts($userSessionID);
        if (count($shoppingCartProducts) > 0)
        {
            echo "
            <div id=\"shoppingCartProductsList\">
            
            "; 
            foreach ($shoppingCartProducts as $product)
            {
                if ($product['Stock'] > 0)
                {
                    echo "<div class=\"shoppingCartProduct\">
                        <h3>" . $product['ProductName'] . "</h3>
                        <p><strong>Product Description: </strong>" . $product['ProductDescription'] . "</p>
                        <p><strong>Total Price for this item: </strong>£" . $product['Price']*$product['ProductQuantity'] . "</p>
                        <p><strong>Company Name: </strong>" . $product['CompanyName'] . "</p>
                        <p><strong>Product Quantity: </strong>" . $product['ProductQuantity'] . "</p>
                        <form method=\"post\" action=\"parceltracker_shopping_cart.php\">
                            <input type=\"hidden\" name=\"ProductID\" value=\"" . $product['ProductID'] . "\"/>
                            <input type=\"submit\" name=\"btnRemoveProductFromShoppingCart\" value=\"Remove\"/>
                        </form>
                        <br>
                    </div>";
                }
                else
                {
                    echo "<p class=\"message\">" . $product['ProductName'] . " has been removed from your shopping cart as we have run out of stock. Sorry!</p>";
                }
            }
            echo "
                <form method=\"post\" action=\"parceltracker_place_order.php\">
                    <input type=\"submit\" id=\"btnGoToCheckout\" name=\"btnGoToCheckout\" value=\"Go To Checkout\"/>
                    <br>
                </form>
            </div>";
        }
        ?>
    </body>
</html>