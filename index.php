<!DOCTYPE html>
    <head>
        <title>Parcel Tracker Home</title>
        <link rel="stylesheet" type="text/css" href="parcelTracker.css" media="screen"/>
    </head>
    <body>
        <div id="webpageHeader">
            <h1>Parcel Tracking System</h1>
        </div>
        <div id="navigationBar">
            <br>
            <a href="index.php" id="homeLink" class="currentPageLink">Home</a>
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
        ?>
        <div id="homepageMessage">
            <h2>Welcome to my Parcel Tracking System!</h2>
            <h3>Here's an overview of what you can do:</h3>
            <p><strong>Visitors: </strong>You can browse a list of all parcels, or search by either a tracking number and postcode or by searching the name of the product ordered.<br>You can also browse products but cannot add them to the shopping cart as a visitor.<br>Or feel free to register to gain registered user privileges!</p>
            <p><strong>Registered Users: </strong>You can view additional details on your own parcels such as your delivery address (unavailable to visitors).<br>You can also add products to your shopping cart and purchase them which in turn generates a parcel per product ordered.</p>
            <p><strong>Admin User: </strong>You can perform all above tasks and in addition you can:</p>
            <ul>
                <li>Create parcels, update their details or delete a parcel.</li>
                <li>Update user details or delete a user.</li>
                <li>Create products, update their details or delete a product.</li>
            </ul>
        </div>
    </body>
</html>