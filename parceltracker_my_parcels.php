<!DOCTYPE html>
    <head>
        <title>My Parcels</title>
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
        require "scripts/parcel_manager.php";
        require "scripts/connection_manager.php";
        require "scripts/user_manager.php";
        require "scripts/validation_manager.php";
        if (!empty($_SESSION['RegisteredUserID']))
        {
            echo "<div id=\"registeredUserSidebar\">
            <h2>Account</h2>
            <a href=\"parceltracker_my_parcels.php\" id=\"myParcelsLink\" class=\"currentPageLink\">My Parcels</a>
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
        echo "<div id=\"parcelSelector\">";
        $parcelsInfo = getUserParcels($_SESSION['RegisteredUserID']);
        $parcels = $parcelsInfo[0];
        $parcelHistoryStatuses = $parcelsInfo[1];
       
        if (count($parcels) == 0)
        {
            echo "<p class=\"message\">You have no parcels. To order one visit products, add a product to shopping cart, and place the order</p>";
        }
        else
        {
        echo "<table id=\"myParcelsList\">
            <tr>
                <th colspan=\"2\" class=\"tableHeader\">Select A Parcel</th>
            </tr>
            <tr> 
                <th class=\"tableSubheader\">Parcel Tracking ID</th>
                <th class=\"tableSubheader\">Product Name</th>
            </tr>";
            $trackingNumbers = [];
        foreach ($parcels as $key => $value)
        { 
            $trackingNumber = $parcels[$key]['ParcelTrackingNumber'];
            $trackingNumbers[] = $key;
            $productName = $parcels[$key]['ProductName'];
            echo "<tr id=\"tableRowParcelID" . $trackingNumber . "\">
                  <td><a href=\"?trackingNumber=" . $trackingNumber . "\">" . $trackingNumber . "</td>
                  <td><a href=\"?trackingNumber=" . $trackingNumber . "\">" . $productName . "</td>
                  </tr>";
        }
        echo "        </table></div>";
    }
        // create and populate a table containing the information for the selected parcel
        $selectedParcelTrackingNumber = -1;
        if (!empty($_GET['trackingNumber']))
        {
            $selectedParcelTrackingNumber = $_GET['trackingNumber'];
            $parcelData;
            $parcelHistoryData;
            $parcelHistoryData = [];
            foreach ($trackingNumbers as $value)
            {
                if ($value == $selectedParcelTrackingNumber)
                {
                   $parcelData = $parcels[$value];
                   if (isset($parcelHistoryStatuses[$value]))
                   {
                    $parcelHistoryData = $parcelHistoryStatuses[$value];
                   }
                }
            }
            if (isset($parcelData) and isset($parcelHistoryData))
            {
                $hasParcelHistoryData = count($parcelHistoryData) > 0;
                $latestParcelStatusMessage = "None (not yet dispatched)";
                if ($hasParcelHistoryData)
                {
                    $latestParcelStatusMessage = current($parcelHistoryData)['ParcelLocationStatus'];
                }
                // highlight the selected row
                echo "<script>function selectRow(rowId, numberOfHeaderRows)
            {
                // used: https://www.w3schools.com/jsref/coll_table_rows.asp
                // used: https://www.w3schools.com/jsref/prop_node_parentnode.asp
                let row = document.getElementById(rowId);
                let rowIndex = row.rowIndex;
                let rows = row.parentNode.children;
                let i;
                for (i = numberOfHeaderRows; i < rows.length; i++)
                {
                    // i = numberOfHeaderRows because the table header and subheaders should not be included
                    let j;
                    if (rowIndex == i)
                    {
                        for (j = 0; j < rows.item(i).children.length; j++)
                        {
                            // for each cell in the row
                            rows.item(i).children.item(j).style.backgroundColor = \"#85B7CF\";
                        }
                    }
                    else
                    {
                        for (j = 0; j < rows.item(i).children.length; j++)
                        {
                            // for each cell in the row
                            rows.item(i).children.item(j).style.backgroundColor = \"#FFD39F\";
                        }
                    }
                }
            }
            selectRow(\"tableRowParcelID" . $selectedParcelTrackingNumber . "\", 2);</script>";
            $neighbourHouseNumber = $parcelData['NeighbourHouseNumber'];
            if (!isset($neighbourHouseNumber))
            {
                $neighbourHouseNumber = "Not Specified";
            }
            else
            {
                $neighbourHouseNumber = "House Number " . $neighbourHouseNumber;
            }
            $safePlace = $parcelData['SafePlace'];
            if (!isset($safePlace))
            {
                $safePlace = "Not Specified";
            }
            // display the parcel information for the selected parcel
            echo "<div id=\"registeredUserParcelData\">
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
                <th class=\"tableSubheader\">Delivery Address</th>
                <td>" . $parcelData['RecipientFirstName'] . " " . $parcelData['RecipientSurname'] . "<br>" . $parcelData['AddressLine1'] . "<br>" . $parcelData['AddressLine2'] . "<br>" . $parcelData['Town'] . "<br>". $parcelData['Postcode'] . "<s            </tr>
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
            <tr>
                <th class=\"tableSubheader\">Deliver To A Neighbour</th>
                <td>" . $neighbourHouseNumber. "</td>
            </tr> 
            <tr>
                <th class=\"tableSubheader\">Safe Place</th>
                <td>" . $safePlace . "</td>
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
            // create the Update Alternative Delivery Arrangements section
            echo "
            <div id=\"myAlternativeDeliveryArrangementsOuterSection\">
                <p class=\"message\">Not going to be home?</p>
                <form id=\"myAlternativeDeliveryArrangementsForm\" action=\"parceltracker_my_parcels.php\" method=\"post\">
                    <h3>Alternative Delivery Arrangements</h3>
                    <label for=\"neighbourHouseNumber\">Neighbour House Number</label>
                    <br>
                    <input type=\"number\" id=\"neighbourHouseNumber\" name=\"NeighbourHouseNumber\" value=\"" . $parcelData['NeighbourHouseNumber'] . "\"/>
                    <br>
                    <label for=\"safePlace\">Safe Place</label>
                    <br>
                    <input type=\"text\" id=\"safePlace\" name=\"SafePlace\" value=\"" . $parcelData['SafePlace'] . "\"/>
                    <br>
                    <br>
                    <input type=\"hidden\" name=\"ParcelTrackingNumber\" value=\"" . $parcelData['ParcelTrackingNumber'] . "\"/>
                    <input type=\"submit\" name=\"btnUpdateAlternativeDeliveryArrangements\" value=\"Save Changes\"/>
                </form>
            </div>";
        }
        }
        else
        {
            echo "<p id=\"noneSelectedMessage\">No parcel is currently selected.<br>Select a parcel tracking number or its product name to view its information.</p>";    
        }
        if (isset($_POST['btnUpdateAlternativeDeliveryArrangements']))
        {
            updateDeliveryArrangments();
        }
        ?>
    </body>
</html>