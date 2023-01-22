<!DOCTYPE html>
    <head>
        <title>Products</title>
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
        require "scripts/product_manager.php";
        require "scripts/connection_manager.php";
        require "scripts/validation_manager.php";
        require "scripts/user_manager.php";
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
        $numberOfPages = getNumberOfPages();
        if (!array_key_exists('productPage', $_GET))
        {
            $_GET['productPage'] = 1;
        }
        $currentPage = $_GET['productPage'];
        if (!empty($currentPage))
        {
            if (($currentPage < 1) or ($currentPage > $numberOfPages))
            {
                // invalid page, therefore set to default which is page 1
                $currentPage = 1;
            }
        }
        else
        {
            // set to default page (page 1)
            $currentPage = 1;
        }
        $products = [];
        if (!empty($_SESSION['RegisteredUserID']))
        {
            $sessionID = getSessionIDForUser($_SESSION['RegisteredUserID']);
            $existingShoppingCart = getShoppingCartProducts($sessionID);
            $products = selectProductsOnPage($currentPage, $existingShoppingCart);  
        }
        else
        {
            $products = selectProductsOnPage($currentPage);
        }
        if (count($products) == 0)
        {
            echo "<p class=\"message\">No products currently exist.</p>";
        }
        else
        {
            echo "
            <div id=\"productShoppingSelector\">
                <h2 class=\"message\">Select A Product</h2>
                <div id=\"productDataList\">
                ";
            foreach ($products as $product)
            {
                echo "<br><br><br>
                <div class=\"product\">
                    <h3 class=\"fullWidthMessage\">". $product['ProductName'] . "</h3>
                    <form method=\"post\" action=\"parceltracker_shopping_cart.php\" class=\"productDataForm\">
                    <p><strong>Product Description: </strong>" . $product['ProductDescription'] . "</p>
                    <p><strong>Price: </strong>£" . $product['Price'] . "</p>
                    <p><strong>Company Name: </strong>" . $product['CompanyName'] . "</p>
                    <input type=\"hidden\" name=\"ProductID\" value=\"" . $product['ProductID'] . "\"/>
                    <label for=\"productQuantity\">Quantity:</label>
                    <br>
                    <input type=\"number\" name=\"ProductQuantity\" id=\"productQuantity\" value=\"1\"/>
                    <br>
                    <br>";
                    if ($product['Stock'] > 0 and !$product['ExistsInShoppingCart'] and !empty($_SESSION['RegisteredUserID']))
                    {
                        echo "<input type=\"submit\" name=\"btnAddToShoppingCart\" value=\"Add To Shopping Cart\"/>
                        <br>
                        <br>";
                    }
                    elseif ($product['Stock'] > 0 and !empty($_SESSION['RegisteredUserID']))
                    {
                        echo "<p class=\"message\">Product already exists in your shopping cart.</p>";
                    }
                    elseif (!empty($_SESSION['RegisteredUserID']))
                    {
                        echo "<p class=\"message\"><strong>Out of Stock.</strong></p>";
                    }
                    else
                    {
                        echo "<p class=\"message\"><strong>Please log in to add to shopping cart.</strong></p>";
                    }
                    echo "<br>
                    </form>
                </div>";
            }
            echo "</div><br><br><br>
                <div id=\"productPageSelector\">";
            for ($i = 1; $i <= $numberOfPages; $i++)
            {
                if ($currentPage == $i)
                {
                    echo "<a href=\"?productPage=" . $i . "\" class=\"currentPageLink\">" . $i . "</a>";
                }
                else
                {
                    echo "<a href=\"?productPage=" . $i . "\">" . $i . "</a>";
                }
            }
            echo "
                </div>
            </div>";
        }
        
        ?>
    </body>
</html>