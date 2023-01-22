<!DOCTYPE html>
    <head>
        <title>Manage Parcels</title>
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
        $parcelsInfo = selectParcels(true);
        if ($parcelsInfo != false)
        {
        $parcels = $parcelsInfo[0];
        $parcelHistoryStatuses = $parcelsInfo[1];
        if (count($parcels) == 0)
        {
            echo "<p class=\"message\">No users currently have any ordered parcels.</p>";
        }
        else
        {
        echo "<div id=\"parcelSelector\">
            <table id=\"manageParcelsList\">
                <tr>
                    <th colspan=\"3\" class=\"tableHeader\">Select A Parcel</th>
                </tr>
                <tr>
                    <th class=\"tableSubheader\">Parcel Tracking ID</th>
                    <th class=\"tableSubheader\">Product Name</th>
                    <th class=\"tableSubheader\">Recipient Name</th>
                </tr>";
        foreach ($parcels as $parcel)
        { 
            $trackingNumber = $parcel['ParcelTrackingNumber'];
            $trackingNumbers[] = $trackingNumber;
            $productName = $parcel['ProductName'];
            $recipientFirstName = $parcel['RecipientFirstName'];
            $recipientSurname = $parcel['RecipientSurname'];
            echo "<tr id=\"tableRowParcelID" . $trackingNumber . "\">
                  <td><a href=\"?trackingNumber=" . $trackingNumber . "\">" . $trackingNumber . "</td>
                  <td><a href=\"?trackingNumber=" . $trackingNumber . "\">" . $productName . "</td>
                  <td><a href=\"?trackingNumber=" . $trackingNumber . "\">" . $recipientFirstName . " " . $recipientSurname . "</tr>";
        }
        echo "        </table></div>";
     }
        
        // create and populate divs from the selected parcel
        if (isset($_GET['trackingNumber']))
        {
            $selectedParcelTrackingNumber = $_GET['trackingNumber'];
            if (!empty($selectedParcelTrackingNumber))
            {
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
            }
            // find the parcel data for the selected parcel
            $parcelData;
            $parcelHistoryData;
            $parcelHistoryData = [];
            foreach ($trackingNumbers as $value)
            {
                if ($selectedParcelTrackingNumber == $value)
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
                echo "
                <div id=\"manageParcelDataOuterSection\">
                    <p class=\"message\">Update Parcel Details</p>
                    <form method=\"post\" action=\"parceltracker_manage_parcels.php\" id=\"manageParcelData\">
                        
                        <div id=\"manageRecipientAddressDetails\" class=\"manageParcelSubSection\">
                            <h4>Recipient Address Details</h4>
                            <br>
                            <label for=\"recipientFirstName\">Recipient First Name</label>
                            <br>
                            <input type=\"text\" id=\"recipientFirstName\" name=\"RecipientFirstName\" required value=\"" . $parcelData['RecipientFirstName'] . "\"/>
                            <br>
                            <label for=\"recipientSurname\">Recipient Surname</label>
                            <br>
                            <input type=\"text\" id=\"recipientSurname\" name=\"RecipientSurname\" required value=\"" . $parcelData['RecipientSurname'] . "\"/>
                            <br>
                            <label for=\"addressLine1\">Address Line 1</label>
                            <br>
                            <input type=\"text\" id=\"addressLine1\" name=\"AddressLine1\" value=\"" . $parcelData['AddressLine1'] . "\" required/>
                            <br>
                            <label for=\"addressLine2\">Address Line 2</label>
                            <br>
                            <input type=\"text\" id=\"addressLine2\" name=\"AddressLine2\" value=\"" . $parcelData['AddressLine2'] . "\"/>
                            <br>
                            <label for=\"town\">Town</label>
                            <br>
                            <input type=\"text\" id=\"town\" name=\"Town\"  value=\"" . $parcelData['Town'] . "\" required/>
                            <br>
                            <label for=\"postcode\">Postcode</label>
                            <br>
                            <input type=\"text\" id=\"postcode\" name=\"Postcode\"  value=\"" . $parcelData['Postcode'] . "\" required/>
                        </div>
                        <div id=\"manageMiscellaneousParcelDetails\" class=\"manageParcelSubSection\">
                            <h4>Miscellaneous Parcel Details</h4>
                            <label for=\"weight\">Weight (KG)</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" id=\"weight\" name=\"Weight\" value=\"" . $parcelData['Weight'] . "\"/>
                            <br>
                            <label for=\"weight\">Product Quantity</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" id=\"weight\" name=\"ProductQuantity\" value=\"" . $parcelData['ProductQuantity'] . "\"/>
                        </div>
                        <div id=\"manageDeliveryPriorityDetails\" class=\"manageParcelSubSection\">
                            <h4>Delivery Priority</h4>
                            <label for=\"deliveryOption\">Delivery Option</label>
                            <br>
                            <select name=\"DeliveryOption\" id=\"deliveryOption\">";
                            if ($parcelData['DeliveryOption'] == "Next Day Delivery")
                            {
                                echo "<option value=\"Next Day Delivery\" selected=\"selected\">Next Day Delivery</option>";
                            }
                            else
                            {
                                echo "<option value=\"Next Day Delivery\">Next Day Delivery</option>";
                            }
                            if ($parcelData['DeliveryOption'] == "2-Day Tracked")
                            {
                                echo "<option value=\"2-Day Tracked\" selected=\"selected\">2-Day Tracked</option>";
                            }
                            else
                            {
                                echo "<option value=\"2-Day Tracked\">2-Day Tracked</option>";
                            }
                            if ($parcelData['DeliveryOption'] == "7-Day Delivery")
                            {
                                echo "<option value=\"7-Day Delivery\" selected=\"selected\">7-Day Delivery</option>";
                            }
                            else
                            {
                                echo "<option value=\"7-Day Delivery\">7-Day Delivery</option>";
                            }
                            if ($parcelData['DeliveryOption'] == "Standard Delivery")
                            {
                                echo "<option value=\"Standard Delivery\" selected=\"selected\">Standard Delivery</option>";
                            }
                            else
                            {
                                echo "<option value=\"Standard Delivery\">Standard Delivery</option>";
                            }
                            echo "
                            </select>
                            <br>
                            <label for=\"preferredTimeSlot\">Preferred Time Slot</label>
                            <br>
                            <select name=\"PreferredTimeSlot\" id=\"preferredTimeSlot\">";
                            if ($parcelData['PreferredTimeSlot'] == '9.00-12.00')
                            {
                                echo "<option value=\"9.00-12.00\" selected=\"selected\">9.00-12.00</option>";
                            }
                            else
                            {
                                echo "<option value=\"9.00-12.00\">9.00-12.00</option>";
                            }
                            if ($parcelData['PreferredTimeSlot'] == '12.00-15.00')
                            {
                                echo "<option value=\"12.00-15.00\" selected=\"selected\">12.00-15.00</option>";
                            }
                            else
                            {
                                echo "<option value=\"12.00-15.00\">12.00-15.00</option>";
                            }
                            if ($parcelData['PreferredTimeSlot'] == '15.00-18.00')
                            {
                                echo "<option value=\"15.00-18.00\" selected=\"selected\">15.00-18.00</option>";
                            }
                            else
                            {
                                echo "<option value=\"15.00-18.00\">15.00-18.00</option>";
                            }
                            if ($parcelData['PreferredTimeSlot'] == '18.00-21.00')
                            {
                                echo "<option value=\"18.00-21.00\" selected=\"selected\">18.00-21.00</option>";
                            }
                            else
                            {
                                echo "<option value=\"18.00-21.00\">18.00-21.00</option>";
                            }
                            echo "
                            </select>   
                        </div>
                        <div id=\"manageAlternativeDeliveryArrangments\" class=\"manageParcelSubSection\">
                            <h4>Alternative Delivery Arrangements</h4>
                            <label for=\"neighbourHouseNumber\">Neighbour House Number</label>
                            <br>
                            <input type=\"number\" id=\"neighbourHouseNumber\" name=\"NeighbourHouseNumber\" value=\"" . $parcelData['NeighbourHouseNumber'] . "\"/>
                            <br>
                            <label for=\"safePlace\">Safe Place</label>
                            <br>
                            <input type=\"text\" id=\"safePlace\" name=\"SafePlace\" value=\"" . $parcelData['SafePlace'] . "\"/>
                        </div>
                        <input type=\"hidden\" type=\"text\" name=\"SelectedParcel\" value=\"" . $selectedParcelTrackingNumber . "\"/>
                        <div class=\"manageParcelSubSection\">
                        <input type=\"submit\" name=\"btnSaveChanges\" value=\"Save Changes\"/>
                        </div>
                        <div class=\"manageParcelSubSection\">
                        <input type=\"submit\" name=\"btnDeleteParcel\" value=\"Delete Parcel\"/>
                        </div>
                    </form>
                    </div>";
                    echo "
                    <div id=\"manageParcelHistoryOuterSection\">
                    <p class=\"message\">Create New Parcel History Status Update</p>
                    <form action=\"parceltracker_manage_parcels.php\" method=\"post\" id=\"manageParcelHistoryData\">
                    <table id=\"manageParcelHistoryTable\" class=\"manageParcelSubSection\">
                    <tr>
                        <th class=\"tableHeader\" colspan=\"2\">Existing Parcel History</th>
                    </tr>";
                    $hasParcelHistoryData = count($parcelHistoryData) > 0;
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
                    <div id=\"newParcelHistoryStatusUpdate\" class=\"manageParcelSubSection\">
                        <h3>New Status Update</h3>
                        <label for=\"statusDescription\">Status</label>
                        <br>
                        <textarea rows=\"2\" cols=\"50\" name=\"StatusDescription\" id=\"statusDescription\">Please enter status here</textarea>
                        <br>
                        <label for=\"date\">Date</label>
                        <br>
                        <input type=\"date\" name=\"Date\" id=\"date\"/>
                        <br>
                        <label for=\"time\">Time</label>
                        <br>
                        <input type=\"time\" name=\"Time\" id=\"time\"/>
                        <input type=\"hidden\" name=\"ParcelTrackingNumber\" id=\"parcelTrackingNumber\" value=\"" . $parcelData['ParcelTrackingNumber'] . "\"/>
                        </div>
                        <input type=\"hidden\" type=\"text\" name=\"SelectedParcel\" value=\"" . $selectedParcelTrackingNumber . "\"/>
                        <div class=\"manageParcelSubSection\">
                            <input type=\"submit\" name=\"btnCreateHistoryStatusUpdate\" value=\"Save New Update To Parcel History\"/>
                        </div>
                    </form>
                    </div>";
            }
        }
        else
        {
            echo "<p id=\"noneSelectedMessage\">No parcel is currently selected.<br>Select a parcel tracking number, its product name or its recipient to modify its information.</p>";
        }
        if (isset($_POST['btnDeleteParcel']))
        {
            deleteParcel($_POST['SelectedParcel']);
        }
        elseif (isset($_POST['btnSaveChanges']))
        {
            updateParcel($_POST['SelectedParcel']);
        }
        elseif (isset($_POST['btnCreateHistoryStatusUpdate']))
        {
            createParcelStatusUpdate($_POST['SelectedParcel']);  
        }
    }
        ?>
    </body>
</html>