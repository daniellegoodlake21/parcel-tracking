<!DOCTYPE html>
    <head>
        <title>Search Parcels By Product Name</title>
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
            <a href="parceltracker_search_parcels.php">By Tracking Number and Postcode</a>
            <br>
            <br>
            <a href="parceltracker_search_parcels_by_product.php" class="currentPageLink">By Product Name</a>
        </div>
        <form action="parceltracker_search_parcels_by_product.php" method="post" id="searchParcelsForm">
            <h3>Search Parcels</h3>
            <br>
            <label for="productName">Product Name</label>
            <br>
            <input type="text" id="productName" name="ProductName"/>
            <br>
            <br>
            <input type="submit" name="btnSearchParcels" id="btnSearchParcels" value="Search"/>
            <br>
            <br>
        </form>
        <?php
        if (isset($_POST["btnSearchParcels"]))
        {

            // retrieve the relevant parcel data from all existing parcels in the database
            $parcelsData = searchByProductName();
            if ($parcelsData != false)
            {
                $parcels = $parcelsData[0];
                $trackingNumbers = [];
                if (count($parcels) == 0)
                {
                    echo "<p class=\"message\">No parcels could be found with a product name containing the text \"" . $_POST['ProductName'] . "\".</p>";
                }
                else
                {
                // create the table header and subheaders
                echo "<div id=\"parcelSelector\">
                <table id=\"parcelsList\">
                <tr>
                    <th colspan=\"2\" class=\"tableHeader\">Select A Parcel</th>
                </tr>
                <tr>
                    <th class=\"tableSubheader\">Parcel Tracking ID</th>
                    <th class=\"tableSubheader\">Product Name</th>
                </tr>";
                // get the parcels retrieved from the database wherever a match was found in the product name in the product contained in the parcel
                foreach ($parcels as $parcel)
                {
                    $trackingNumber = $parcel['ParcelTrackingNumber'];
                    $trackingNumbers[] = $trackingNumber;
                    $productName = $parcel['ProductName'];
                    echo "<tr id=\"tableRowParcelID" . $trackingNumber . "\">
                    <td><a href=\"?trackingNumber=" . $trackingNumber . "\">" . $trackingNumber . "</a></td>
                    <td><a href=\"?trackingNumber=" . $trackingNumber . "\">" . $productName . "</a></td>
                    </tr>";
                }
                echo "
                    </table>
                </div>";
            }
        }
    }
    // create and populate divs from the selected parcel

    if (!empty($_GET['trackingNumber']))
    {
        $parcelsData = selectParcels();
        $selectedParcelTrackingNumber = $_GET['trackingNumber'];
        $parcelData = $parcelsData[0][$selectedParcelTrackingNumber];
        $parcelHistoryData = [];
        if (!empty($parcelsData[1][$selectedParcelTrackingNumber]))
        {
            $parcelHistoryData = $parcelsData[1][$selectedParcelTrackingNumber];
        }
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
        echo "<p id=\"accountTip\"><strong>Have an account?</strong><br>Navigate to My Parcels once logged in to view more details about your parcels.</p>";
        // find the parcel data for the selected parcel
        
        if (isset($parcelData) and isset($parcelHistoryData))
        {
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
    if (empty($_GET['trackingNumber']) and isset($_POST['btnSearchParcels']))
    {
        echo "<p id=\"noneSelectedMessage\">No parcel is currently selected.<br>Select a parcel tracking number or its product name to view its information.</p>";    
    }

        ?>
    </body>
</html>