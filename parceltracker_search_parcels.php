<!DOCTYPE html>
    <head>
        <title>Search Parcels By Tracking Number and Postcode</title>
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
            <a href="parceltracker_search_parcels.php" id="searchLink" class="currentPageLink">Search Parcels</a>
        </div>
        <?php
        require "scripts/parcel_manager.php";
        require "scripts/connection_manager.php";
        require "scripts/user_manager.php";
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
        <div id="searchParcelsSidebar">
            <h2>Search Method</h2>
            <a href="parceltracker_search_parcels.php" class="currentPageLink">By Tracking Number and Postcode</a>
            <br>
            <a href="parceltracker_search_parcels_by_product.php">By Product Name</a>
        </div>
        <form action="parceltracker_search_parcels.php" method="post" id="searchParcelsForm">
            <h3>Search Parcels</h3>
            <br>
            <label for="trackingNumber">Tracking Number</label>
            <br>
            <input type="text" id="trackingNumber" name="ParcelTrackingNumber"/>
            <br>
            <label for="postcode">Postcode</label>
            <br>
            <input type="text" id="postcode" name="Postcode"/>
            <br>
            <br>
            <input type="submit" name="btnSearchParcels" id="btnSearchParcels" value="Search"/>
            <br>
            <br>
        </form>
        <?php
            // retrieve the parcel data from all existing parcels in the database
            $parcelsInfo = selectParcels();
            $parcels = $parcelsInfo[0];
            $parcelHistoryStatuses = $parcelsInfo[1];
            $trackingNumbersAndPostcodes = [];      
            if (isset($_POST['btnSearchParcels']))
            {
                $foundParcelData = searchParcelByTrackingNumberAndPostcode();
                if ($foundParcelData != false)
                {

                    $parcelData = $foundParcelData[0];
                    $parcelHistoryData = $foundParcelData[1];
                    $hasParcelHistoryData = count($parcelHistoryData) > 0;
                    if ($hasParcelHistoryData)
                    {
                        $latestParcelStatusMessage = $parcelHistoryData[count($parcelHistoryData)-1]['ParcelLocationStatus'];
                    }
                    else
                    {
                        $latestParcelStatusMessage = "None (Not yet dispatched)";
                    }
    
                        // display the relevant (limited) parcel information for the selected parcel
                        echo "<div id=\"visitorParcelData\">
                        <p class=\"message\">Latest Parcel Status: " . $latestParcelStatusMessage .  "</p>
                        <table id=\"registeredUserParcelInformationTable\">
                        <tr>
                            <th class=\"tableHeader\" colspan=\"2\">Parcel " . $parcelData['ParcelTrackingNumber'] . "</th>
                        </tr>
                        <tr>
                            <th class=\"tableSubheader\">Product Name</th>
                            <td>" . $parcelData['ProductName'] . "</td>
                        </tr>
                        <tr>
                            <th class=\"tableSubheader\">Company Name</th>
                            <td>" . $parcelData['CompanyName'] . "</td>
                        </tr>
                        <tr>
                            <th class=\"tableSubheader\">Weight</th>
                            <td>" . $parcelData['Weight'] . "KG</td>
                        </tr>
                        <tr>
                            <th class=\"tableSubheader\">Product Quantity</th>
                            <td>" . $parcelData['ProductQuantity'] . "</td>
                        </tr>   
                        <tr>
                            <th class=\"tableSubheader\">Delivery Option</th>
                            <td>" . $parcelData['DeliveryOption'] . "</td>
                        </tr>
                        <tr>
                            <th class=\"tableSubheader\">Preferred Time Slot</th>
                            <td>" . $parcelData['PreferredTimeSlot'] . "</td>
                        </tr>              
                        </table>";
                        echo "<table id=\"registeredUserParcelHistoryTable\">
                        <tr>
                            <th class=\"tableHeader\" colspan=\"2\">Parcel History</th>
                        </tr>";
                        if (!$hasParcelHistoryData)
                        {
                            echo "
                            <tr>
                                <td colspan=\"2\">There are currently no status updates as the product has not yet been dispatched. Please check back later.</td>
                            </tr>";
                        }
                        else
                        {
                            echo "         <tr>
                            <th class=\"tableSubheader\">Status</th>
                            <th class=\"tableSubheader\">Date and Time</th>
                        </tr>";
                            foreach ($parcelHistoryData as $statusUpdate)
                            {
                                echo "
                                <tr>
                                    <td>" . $statusUpdate['ParcelLocationStatus'] . "</td>
                                    <td>" . $statusUpdate['Date'] . " at ". $statusUpdate['Time'] . "</td>
                                </tr>";
                            }
                        }
                        echo "
                        </table>   
                        </div>";        
                }
            }
            
        ?>
    </body>
</html>