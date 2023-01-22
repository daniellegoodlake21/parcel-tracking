<!DOCTYPE html>
    <head>
        <title>Parcel Tracker Logout</title>
        <link rel="stylesheet" type="text/css" href="parcelTracker.css" media="screen"/>
    
    </head>
    <body>
        <div id="webpageHeader">
            <h1>Parcel Tracking System</h1>
        </div>
        <div id="navigationBar">
            <br>
            <a href="index.php" id="homeLink">Home</a>
            <a href="parceltracker_login.php" id="loginLink">Login</a>
            <a href="parceltracker_register.php" id="registerLink">Register</a>
            <a href="parceltracker_products.php" id="productsLink">Products</a>
            <a href="parceltracker_list_parcels.php" id="listParcelsLink">List Parcels</a>
            <a href="parceltracker_search_parcels.php" id="searchLink">Search Parcels</a>
        </div>
        <?php
            $sessionName = session_name("ParcelTrackingSystemSession");
            session_start();
            session_destroy();
            echo "<p class=\"message\">You are now logged out.<br><a href=\"index.php\">Return to Homepage</a></p>";
        ?>
    </body>
</html>