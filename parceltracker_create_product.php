<!DOCTYPE html>
    <head>
        <title>Create Product</title>
        <link rel="stylesheet" type="text/css" href="parcelTracker.css" media="screen"/>
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
        require "scripts/product_manager.php";
        require "scripts/connection_manager.php";
        require "scripts/validation_manager.php";
        if (userIsAdmin())
        {
            echo "<div id=\"registeredUserSidebar\">
            <h2>Account</h2>
            <a href=\"parceltracker_my_parcels.php\" id=\"myParcelsLink\">My Parcels</a>
            <br>
            <a href=\"parceltracker_shopping_cart.php\" id=\"shoppingCartLink\">Shopping Cart</a>
            <h3>Parcel Tracking System Management</h3>
            <br>
            <br>
            <a href=\"parceltracker_manage_users.php\" id=\"manageUsersLink\">Manage Users</a>
            <br>
            <br>
            <a href=\"parceltracker_manage_products.php\" id=\"manageProductsLink\" class=\"currentPageLink\">Manage Products</a>
            <br>
            <br>
            <a href=\"parceltracker_manage_parcels.php\" id=\"manageParcelsLink\">Manage Parcels</a>
        </div>
        <div id=\"manageProductsSidebar\">
            <h2>Manage Products</h2>
            <br>
            <br>
            <a href=\"parceltracker_manage_products.php\" id=\"manageProductsLink\">Update Or Delete Existing Product</a>
            <br>
            <br>
            <a href=\"parceltracker_create_product.php\" id=\"createProductLink\" class=\"currentPageLink\">Create Product</a> 
            <br>
        </div>
        <div id=\"createProductOuterSection\">
                    <p class=\"message\">Creating New Product</p>
                    <form action=\"parceltracker_create_product.php\" method=\"post\" id=\"createProductForm\">
                        <div id=\"createProduct\">
                            <br>
                            <label for=\"productName\">Product Name</label>
                            <br>
                            <input type=\"text\" name=\"ProductName\" id=\"productName\" requireds/>
                            <br>
                            <label for=\"productDescription\">Product Description</label>
                            <br>
                            <textarea rows=\"3\" cols=\"50\" name=\"ProductDescription\" id=\"productDescription\"></textarea>
                            <br>
                            <label for=\"companyName\">Company Name</label>
                            <br>
                            <input type=\"text\" name=\"CompanyName\" id=\"companyName\" required/>
                            <br>
                            <label for=\"price\">Price (£)</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" name=\"Price\" id=\"price\"/>
                            <br>
                            <label for=\"costPrice\">Cost Price (£)</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" name=\"CostPrice\" id=\"costPrice\"/>
                            <br>  
                            <label for=\"stock\">Stock</label>
                            <br>
                            <input type=\"number\" name=\"Stock\" id=\"stock\"/>
                            <br>                    
                            <label for=\"ean\">EAN</label>
                            <br>
                            <input type=\"text\" name=\"EAN\" id=\"ean\" required/>
                            <br>
                            <label for=\"weight\">Weight</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" name=\"Weight\" id=\"weight\"/>
                            <br>
                        </div>
                        <br>
                        <input type=\"submit\" name=\"btnCreateProduct\" id=\"btnCreateProduct\" value=\"Save New Product\"/>
                        <br>
                    </form>
                </div>";
                if (isset($_POST['btnCreateProduct']))
                {
                    createProduct();
                }
        }
        else
        {
            echo "<p class=\"message\">You must be an admin to create a new product.<br><a href=\"index.php\">Return to Homepage</a><br></p>";
        }

        ?>
    </head>
    <body>