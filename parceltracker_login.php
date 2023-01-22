<!DOCTYPE html>
    <head>
        <title>Parcel Tracker Login</title>
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
            require "scripts/user_manager.php";
            require "scripts/connection_manager.php";
            require "scripts/validation_manager.php";
            if (isset($_POST['btnLogin']))
            {                
                $userID = loginUser();
                if ($userID != false)
                {
                    $_SESSION['RegisteredUserID'] = $userID;
                }
            }
            if (!empty($_SESSION['RegisteredUserID']))
            {
                echo "<a href=\"parceltracker_logout.php\" id=\"loginLink\" class=\"currentPageLink\">Logout</a>";
            }
            else
            {
                echo "<a href=\"parceltracker_login.php\" id=\"loginLink\" class=\"currentPageLink\">Login</a>";
                echo "<a href=\"parceltracker_register.php\" id=\"registerLink\">Register</a>";
            }
            ?>
            <a href="parceltracker_products.php" id="productsLink">Products</a>
            <a href="parceltracker_list_parcels.php" id="listParcelsLink">List Parcels</a>
            <a href="parceltracker_search_parcels.php" id="searchLink">Search Parcels</a>
        </div>
        <?php
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
        <div id="loginDetails">
            <form method="post" action="parceltracker_login.php" id="loginForm">
                <h2>Login Details:</h2>
                <br>
                <label for="emailInput">Email Address:</label>
                <br>
                <input type="text" id="emailInput" name="Email" required/>
                <br>
                <label for="passwordInput">Password:</label>
                <br>
                <input type="password" id="passwordInput" name="Password" required/>
                <br>
                <br>
                <input type="submit" name="btnLogin" value="Login"/>
            </form>
        </div>
    </body>
</html>