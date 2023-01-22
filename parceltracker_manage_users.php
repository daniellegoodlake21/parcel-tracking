<!DOCTYPE html>
    <head>
        <title>Manage Users</title>
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
        require "scripts/user_manager.php";
        require "scripts/validation_manager.php";
        require "scripts/connection_manager.php";
        if (isset($_POST['btnUpdateUser']))
        {
            updateUser($_POST['RegisteredUserID']);
        }
        elseif (isset($_POST['btnDeleteUser']))
        {
            deleteUser($_POST['RegisteredUserID']);
        }
        $users = selectUsers(true);
        if ($users != false)
        {
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
                    <a href=\"parceltracker_manage_users.php\" id=\"manageUsersLink\" class=\"currentPageLink\">Manage Users</a>
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
            if (count($users) == 0)
            {
                echo "<p class=\"message\">No users currently exist.</p>";
            }
            else
            {
                echo "
                <div id=\"userSelector\">
                    <table id=\"manageUsersList\">
                        <tr>
                            <th class=\"tableHeader\">Select A User</th>
                        </tr>
                        <tr>
                            <th class=\"tableSubheader\">Name</th>
                        </tr>";
                foreach ($users as $user)
                {
                    echo "
                    <tr id=\"tableRowUserID" . $user["RegisteredUserID"] . "\">
                        <td><a href=\"?userId=" . $user["RegisteredUserID"] . "\">" . $user["FirstName"] . " " . $user["Surname"] . "</a></td>
                    </tr>";
                }
                echo "
                    </table>
                </div>";
            }
            if (!empty($_GET['userId']))
            {
                $selectedUserId = $_GET['userId'];
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
                selectRow(\"tableRowUserID" . $selectedUserId . "\", 2);</script>";
                // find the user data for the selected user
                $userData;
                foreach ($users as $userId => $user)
                {
                    if ($userId == $selectedUserId)
                    {
                        $userData = $user;
                    }
                }
                if (isset($userData))
                {
                    // display the user data in the update user form
                    echo "
                    <div id=\"updateUserOuterSection\">
                        <p class=\"message\">Selected User: " . $userData['FirstName'] . " " . $userData['Surname'] . "<br>User ID: " . $userData['RegisteredUserID'] . "</p>
                        <form method=\"post\" action=\"parceltracker_manage_users.php\" id=\"updateUserForm\">
                            <div id=\"updateUser\">
                                <br>
                                <label for=\"FirstName\">First Name</label>
                                <br>
                                <input type=\"text\" name=\"FirstName\" id=\"FirstName\" value=\"" . $userData['FirstName'] . "\" required/>
                                <br>
                                <label for=\"Surname\">Surname</label>
                                <br>
                                <input type=\"text\" name=\"Surname\" id=\"Surname\" value=\"" . $userData['Surname'] . "\" required/>
                                <br>
                                <label for=\"Email\">Email Address</label>
                                <br>
                                <input type=\"text\" name=\"Email\" id=\"Email\" value=\"" . $userData['Email'] . "\" required/>
                                <br>
                                <br>
                                <input type=\"hidden\" name=\"RegisteredUserID\" value=\"" . $userData['RegisteredUserID'] . "\"/>
                            </div>
                            <br>
                            <input type=\"submit\" name=\"btnUpdateUser\" id=\"btnUpdateUser\" value=\"Save Changes\"/>
                            <input type=\"submit\" name=\"btnDeleteUser\" id=\"btnDeleteUser\" value=\"Delete User\"/>
                            <br>
                        </form>
                    </div>";
                }
            }
            else
            {
                echo "<p id=\"noneSelectedMessage\">No user is currently selected.<br>Select a user's name to modify their information.</p>";
            }
        }
        ?>
    </body>
</html>