<?php
    // gets the current user's parcels
    function getUserParcels($userID)
    {
        if (empty($userID))
        {
            echo "<p class=\"message\">You must be logged in to view your parcels.</p>";
            return;
        }
        $connection = establishConnection();
        $query = "SELECT
        ParcelTrackingNumber,
        ProductQuantity,
        CompanyName,
        Parcel.Weight,
        DeliveryOption,
        SafePlace,
        PreferredTimeSlot,
        NeighbourHouseNumber,
        Parcel.RecipientFirstName,
        Parcel.RecipientSurname,
        AddressLine1,
        AddressLine2,
        Town,
        Postcode,
        ProductName
        FROM
        Parcel,
        Product,
        Address
        WHERE
        Parcel.RegisteredUserID_FK = " . $userID . "
        AND Parcel.ProductID_FK = Product.ProductID
        AND Parcel.RecipientAddressID_FK = Address.AddressID;";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to retrieve parcels from the database.</p>";
            mysqli_close($connection);
            return false;
        }
        else
        {      
            $parcels = [];
            $parcelsHistory = [];
            foreach ($result as $parcel)
            {
                $query2 = "SELECT * FROM ParcelHistoryStatusUpdate WHERE ParcelTrackingNumber_FK = " . $parcel['ParcelTrackingNumber'] . " ORDER BY Date DESC, Time DESC";
                $result2 = mysqli_query($connection, $query2);
                if (!$result2)
                {
                    echo "<p class=\"message\">Failed to get parcel's history status updates.</p>";
                }
                $parcels[$parcel['ParcelTrackingNumber']] = $parcel;
                foreach ($result2 as $statusUpdate)
                {
                    $parcelsHistory[$statusUpdate['ParcelTrackingNumber_FK']][] = $statusUpdate;
                }
            }
            mysqli_close($connection);
            return [$parcels, $parcelsHistory];       
        }
    }
    // gets all parcels in the database
    function selectParcels($adminUserRequired=false)
    {
        if ($adminUserRequired and !userIsAdmin())
        {
            echo "<p class=\"message\">You must be an admin to access parcel information in the Manage Parcels page.<br><a href=\"index.php\">Return to Homepage</a><br></p>";
            return false;
        }
        $connection = establishConnection();
        $query = "SELECT ParcelTrackingNumber, ProductQuantity, ProductName, CompanyName, Parcel.Weight,
        Parcel.RecipientFirstName, Parcel.RecipientSurname, AddressLine1, AddressLine2, Town, Postcode,
        DeliveryOption, PreferredTimeSlot, NeighbourHouseNumber, SafePlace
        FROM Parcel, Address, Product
        WHERE Parcel.ProductID_FK = Product.ProductID
        AND Parcel.RecipientAddressID_FK = Address.AddressID";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to retrieve parcels from the database.</p>";
            mysqli_close($connection);
            return false;
        }
        $parcels = [];
        $parcelsHistory = [];
        foreach ($result as $parcel)
        {
            $query2 = "SELECT * FROM ParcelHistoryStatusUpdate WHERE ParcelTrackingNumber_FK = " . $parcel['ParcelTrackingNumber'] . " ORDER BY Date DESC, Time DESC";
            $result2 = mysqli_query($connection, $query2);
            $parcels[$parcel['ParcelTrackingNumber']] = $parcel;
            foreach ($result2 as $statusUpdate)
            {
                $parcelsHistory[$statusUpdate['ParcelTrackingNumber_FK']][] = $statusUpdate;
            }
        }
        mysqli_close($connection);
        return [$parcels, $parcelsHistory];
    }
    // retrieves a list of parcels linked to a product with a name matching the search 
    function searchByProductName()
    {
        $search = $_POST['ProductName'];
        $valid = validateParcelSearchByProductInput(); // handles invalid input messages within the function
        if (!$valid)
        {
            return false;
        }
        $connection = establishConnection();
        $query = "SELECT ParcelTrackingNumber, ProductQuantity, ProductName, CompanyName, Parcel.Weight,
        Parcel.RecipientFirstName, Parcel.RecipientSurname, AddressLine1, AddressLine2, Town, Postcode,
        DeliveryOption, PreferredTimeSlot, NeighbourHouseNumber, SafePlace
        FROM Parcel, Address, Product WHERE Parcel.ProductID_FK = Product.ProductID
        AND Parcel.RecipientAddressID_FK = Address.AddressID
        AND LOWER(ProductName) LIKE \"%" . strtolower($search) . "%\";";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to search for parcels by product name.</p>";
            mysqli_close($connection);
            return false;
        }
        $parcels = [];
        $parcelsHistory = [];
        foreach ($result as $parcel)
        {
            $parcels[$parcel['ParcelTrackingNumber']] = $parcel;
            $query2 = "SELECT * FROM ParcelHistoryStatusUpdate WHERE ParcelTrackingNumber_FK = " . $parcel['ParcelTrackingNumber'] . " ORDER BY Date DESC, Time DESC;";
            $result2 = mysqli_query($connection, $query2);
            if (!$result2)
            {
                echo "<p class=\"message\">Failed to get parcel's history data when searching by product name.</p>";
                return false;
            }
            foreach ($result2 as $statusUpdate)
            {
                $parcelsHistory[$statusUpdate['ParcelTrackingNumber_FK']][] = $statusUpdate;
            }
        }
        mysqli_close($connection);
        $parcelsData = [$parcels, $parcelsHistory];
        return $parcelsData;
    }
    // retrieves only one parcel (the parcel with a matching tracking number and postcode) if found
    function searchParcelByTrackingNumberAndPostcode()
    {
        $trackingNumber = $_POST['ParcelTrackingNumber'];
        $postcode = strtolower(str_replace(' ', '', $_POST['Postcode']));
        if (validateParcelSearchInput($trackingNumber, $postcode))
        {
            $connection = establishConnection();
            $query = "SELECT ParcelTrackingNumber, ProductQuantity, ProductName, CompanyName, Parcel.Weight, DeliveryOption, PreferredTimeSlot FROM Parcel, Product, Address WHERE ParcelTrackingNumber = " . $trackingNumber . " AND Address.AddressID = Parcel.RecipientAddressID_FK AND LOWER(REPLACE(Address.Postcode, ' ', '')) = \"" . $postcode . "\" AND Product.ProductID = Parcel.ProductID_FK;";
            $result = mysqli_query($connection, $query);
            if (!$result)
            {
                echo "<p class=\"message\">Failed to search for parcel by tracking number and postcode.</p>";
                mysqli_close($connection);
                return false;
            }
            if (mysqli_num_rows($result) == 0)
            {
                echo "<p class=\"message\">Sorry, no parcels were found with the provided tracking number and postcode.</p>";
                mysqli_close($connection);
                return false;
            }
            $parcel = mysqli_fetch_assoc($result);
            $query2 = "SELECT * FROM ParcelHistoryStatusUpdate WHERE ParcelTrackingNumber_FK = " . $trackingNumber . " ORDER BY Date DESC, Time DESC;";
            $result2 = mysqli_query($connection, $query2);
            mysqli_close($connection);
            if (!$result2)
            {
                echo "<p class=\"message\">Failed to get parcel's history when searching by tracking number and postcode.</p>";
                return false;
            }
            $parcelHistory = [];
            foreach ($result2 as $statusUpdate)
            {
                $parcelHistory[] = $statusUpdate;
            } 
            return [$parcel, $parcelHistory];
        }
        return false;
    }
    // allows user to update the safe place and neighbour house number for their own parcel
    function updateDeliveryArrangments()
    {
        $valid = isCollectiveAlternativeDeliveryArrangentsInputValid(); // handles invalid input messages within the function
        if (!$valid)
        {
            return;
        }
        $connection = establishConnection();
        $query = "UPDATE Parcel SET NeighbourHouseNumber = " . $_POST['NeighbourHouseNumber'] . ", SafePlace = \"" . $_POST['SafePlace'] . "\" WHERE ParcelTrackingNumber = " . $_POST['ParcelTrackingNumber'] . ";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to update the parcel's alternative delivery arrangments.</p>";
            return;
        }
        echo "<p class=\"message\">Parcel's alternative delivery arrangements have been updated.</p>";   
    }
    // allows admin to update parcel details
    function updateParcel($parcelTrackingNumber)
    {
        $valid = isCollectiveManageParcelInputValid(); // handles invalid input messages within the function
        if (!$valid)
        {
            return;
        }
        $connection = establishConnection();
        if (empty($_POST['NeighbourHouseNumber']))
        {
            $_POST['NeighbourHouseNumber'] = "NULL";
        }
        $query = "UPDATE Parcel, Address SET NeighbourHouseNumber = " . $_POST['NeighbourHouseNumber'] . ", SafePlace = \"" . $_POST['SafePlace'] . "\", PreferredTimeSlot = \"" . $_POST['PreferredTimeSlot'] . "\", DeliveryOption = \"" . $_POST['DeliveryOption'] . "\", Weight = " . $_POST['Weight'] . ", Parcel.RecipientFirstName = \"" . $_POST['RecipientFirstName'] . "\", Parcel.RecipientSurname = \"" . $_POST['RecipientSurname'] . "\", AddressLine1 = \"" . $_POST['AddressLine1'] . "\", AddressLine2 = \"" . $_POST['AddressLine2'] . "\", Town = \"" . $_POST['Town'] . "\", Postcode = \"" . $_POST['Postcode'] . "\", ProductQuantity = " . $_POST['ProductQuantity'] .  " WHERE ParcelTrackingNumber = " . $parcelTrackingNumber . " AND AddressID = RecipientAddressID_FK;";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to update parcel.</p>";
            return;
        }
        echo "<p class=\"message\">Parcel with tracking number " . $parcelTrackingNumber . " updated successfully.</p>";
    }
    // allow admin to delete a parcel
    function deleteParcel($parcelTrackingNumber)
    {
        $connection = establishConnection();
        $query = "DELETE FROM Parcel WHERE ParcelTrackingNumber = " . $parcelTrackingNumber . ";";
        $query2 = "DELETE FROM ParcelHistoryStatusUpdate WHERE ParcelTrackingNumber_FK = " . $parcelTrackingNumber . ";";
        $query3 = "DELETE Address FROM Address INNER JOIN Parcel ON Address.AddressID = Parcel.RecipientAddressID_FK WHERE Parcel.ParcelTrackingNumber = " . $parcelTrackingNumber . ";";
        $result = mysqli_query($connection, $query);
        $result2 = mysqli_query($connection, $query2);
        $result3 = mysqli_query($connection, $query3);
        if (!$result)
        {
           echo "<p class=\"message\">Failed to delete parcel.</p>";
           mysqli_close($connection);
           return;
        }
        if (!$result2)
        {
           echo "<p class=\"message\">Failed to delete status updates belonging to parcel.</p>";
           mysqli_close($connection);
           return;
        }
        if (!$result3)
        {
           echo "<p class=\"message\">Failed to delete address data belonging to parcel.</p>";
           mysqli_close($connection);
           return;
        }
        mysqli_close($connection);
        echo "<p class=\"message\">Parcel with Tracking Number " . $parcelTrackingNumber . " has been deleted.</p>";
    }
    // create address details associated with parcel
    function createAddress()
    {
        $connection = establishConnection();
        $query = "INSERT INTO Address (RecipientFirstName, RecipientSurname, AddressLine1, AddressLine2, Town, Postcode) VALUES (\"" . $_POST['RecipientFirstName'] . "\", \"" . $_POST['RecipientSurname'] . "\", \"" . $_POST['AddressLine1'] . "\", \"" . $_POST['AddressLine2'] . "\", \"" . $_POST['Town'] . "\", \"" . $_POST['Postcode'] . "\");";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to create address.</p>";
        }
        $query = "SELECT AddressID FROM Address WHERE RecipientFirstName = \"" . $_POST['RecipientFirstName'] . "\"" . " AND RecipientSurname = \"" . $_POST['RecipientSurname'] . "\"" . " AND AddressLine1 = \"" .  $_POST['AddressLine1'] . "\" AND AddressLine2 = \"" . $_POST['AddressLine2'] . "\"" . " AND Town = \"" . $_POST['Town'] . "\"" . " AND Postcode = \"" . $_POST['Postcode'] . "\";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result or mysqli_num_rows($result) == 0)
        {
            echo "<p class=\"message\">Failed to get Address ID</p>";
            return false;
        }
        $addressID = mysqli_fetch_assoc($result)['AddressID'];
        return $addressID;
    }
    // create a parcel in the database
    function createParcel($userID, $addressID, $productID, $weight, $productQuantity)
    {
        $sessionID = getSessionIDForUser($userID);
        $connection = establishConnection();
        if (empty($_POST['NeighbourHouseNumber']))
        {
            $_POST['NeighbourHouseNumber'] = "NULL";
        }
        $query = "INSERT INTO Parcel (RegisteredUserID_FK, RecipientAddressID_FK, ProductID_FK, NeighbourHouseNumber, SafePlace, PreferredTimeSlot, DeliveryOption, Weight, ProductQuantity) VALUES (" . $userID . ", " . $addressID . ", " . $productID . ", " . $_POST['NeighbourHouseNumber'] . ", \"" . $_POST['SafePlace'] . "\", \"" . $_POST['PreferredTimeSlot'] . "\", \"" . $_POST['DeliveryOption'] . "\", " . $weight . ", " . $productQuantity . ");"; 
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to create parcel.</p>";
            return;
        }
        echo "<p class=\"message\"><a href=\"index.php\">Order placed! Return to homepage</a></p>";
        reduceStock($productID, $productQuantity);
        
    }
    // create a status update e.g. Dispatched on (Date) at (Time)
    function createParcelStatusUpdate($parcelTrackingNumber)
    {
        $valid = isStatusUpdateValid(); // handles invalid input messages within the function
        if (!$valid)
        {
            return;
        }
        $connection = establishConnection();
        $query = "INSERT INTO ParcelHistoryStatusUpdate (ParcelLocationStatus, Date, Time, ParcelTrackingNumber_FK) VALUES (\"" . $_POST['StatusDescription'] . "\", \"" . $_POST['Date'] . "\", \"" . $_POST['Time'] . "\", " . $_POST['ParcelTrackingNumber'] . ");";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to create parcel status update.</p>";
            echo "<p class=\"message\">" . $query . "</p>";
            return;
        }
        echo "<p class=\"message\">Parcel status update created successfully.</p>";

    }
?>