<!DOCTYPE html>
    <head>
        <title>Manage Products</title>
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
            require "scripts/product_manager.php";
            require "scripts/connection_manager.php";
            require "scripts/validation_manager.php";
            require "scripts/user_manager.php";
            // retrieve a list of products from the database
            $products = selectProducts(true); 
            if ($products != false)
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
                        <a href=\"parceltracker_manage_users.php\" id=\"manageUsersLink\">Manage Users</a>
                        <br>
                        <br>
                        <a href=\"parceltracker_manage_products.php\" id=\"manageProductsLink\">Manage Products</a>
                        <br>
                        <br>
                        <a href=\"parceltracker_manage_parcels.php\" id=\"manageParcelsLink\">Manage Parcels</a>
                    </div>
                    <div id=\"manageProductsSidebar\">
                    <h2>Manage Products</h2>
                    <br>
                    <br>
                    <a href=\"parceltracker_manage_products.php\" id=\"manageProductsLink\" class=\"currentPageLink\">Update Or Delete Existing Product</a>
                    <br>
                    <br>
                    <a href=\"parceltracker_create_product.php\" id=\"createProductLink\">Create Product</a> 
                    <br>
                </div>";
                    }
                    else
                    {
                        echo "</div>";
                    }
                }
            if (count($products) == 0)
            {
                echo "<p class=\"message\">No products currently exist.</p>";
            }
            else
            {
                // display a table containing product names 
                echo "
                <div id=\"productSelector\">
                    <table id=\"productsList\">
                        <tr>
                            <th class=\"tableHeader\">Select A Product</th>
                        </tr>
                        <tr>
                            <th class=\"tableSubheader\">Product Name</th>
                        </tr>";
                foreach ($products as $product)
                {
                    echo "
                    <tr id=\"tableRowProductID" . $product['ProductID'] . "\">
                        <td><a href=\"?productId=" . $product['ProductID'] . "\">" . $product['ProductName'] . "</td>
                    </tr>";
                }
                echo "
                    </table>
                </div>";
            }
            // get the selected product
            if (!empty($_GET['productId']))
            {
            $selectedProductId = $_GET['productId'];
            $selectedProduct = null;
            foreach ($products as $product)
            {
                $productId = $product['ProductID'];
                if ($productId == $selectedProductId)
                {
                    $selectedProduct = $product;
                }
            }
            if (isset($selectedProduct))
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
        selectRow(\"tableRowProductID" . $selectedProduct['ProductID'] . "\", 2);</script>";
                // create outer section for updating the selected product
                echo "
                <div id=\"updateProductOuterSection\">
                    <p class=\"message\">Selected Product: " . $selectedProduct['ProductName'] . "<br>Product ID: " . $selectedProduct['ProductID'] . "</p>
                    <form action=\"parceltracker_manage_products.php\" method=\"post\" id=\"updateProductForm\">
                        <div id=\"updateProduct\">
                            <br>
                            <label for=\"productName\">Product Name</label>
                            <br>
                            <input type=\"text\" name=\"ProductName\" id=\"productName\" value=\"" . $selectedProduct['ProductName'] . "\" required/>
                            <br>
                            <label for=\"productDescription\">Product Description</label>
                            <br>
                            <textarea rows=\"3\" cols=\"50\" name=\"ProductDescription\" id=\"productDescription\">" . $selectedProduct['ProductDescription'] . "</textarea>
                            <br>
                            <label for=\"companyName\">Company Name</label>
                            <br>
                            <input type=\"text\" name=\"CompanyName\" id=\"companyName\" value=\"" . $selectedProduct['CompanyName'] . "\" required/>
                            <br>
                            <label for=\"price\">Price (£)</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" name=\"Price\" id=\"price\" value=\"" . $selectedProduct['Price'] . "\"/>
                            <br>
                            <label for=\"costPrice\">Cost Price (£)</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" name=\"CostPrice\" id=\"costPrice\" value=\"" . $selectedProduct['CostPrice'] . "\"/>
                            <br>  
                            <label for=\"stock\">Stock</label>
                            <br>
                            <input type=\"number\" name=\"Stock\" id=\"stock\" value=\"" . $selectedProduct['Stock'] . "\"/>
                            <br>                    
                            <label for=\"ean\">EAN</label>
                            <br>
                            <input type=\"text\" name=\"EAN\" id=\"ean\" value=\"" . $selectedProduct['EAN'] . "\" required/>
                            <br>
                            <label for=\"weight\">Weight</label>
                            <br>
                            <input type=\"number\" step=\"0.01\" name=\"Weight\" id=\"weight\" value=\"" . $selectedProduct['Weight'] . "\"/>
                            <br>
                        </div>
                        <br>
                        <input type=\"hidden\" name=\"ProductID\" value=\"" . $selectedProduct['ProductID'] . "\"/> 
                        <input type=\"submit\" name=\"btnUpdateProduct\" id=\"btnUpdateProduct\" value=\"Save Changes\"/>
                        <input type=\"submit\" name=\"btnDeleteProduct\" id=\"btnDeleteProduct\" value=\"Delete Product\"/>
                        <br>
                        </form>
                </div>";
            }
        }
            else
            {
                echo "<p id=\"noneSelectedMessage\">No product is currently selected.<br>Select a product name to modify its information.</p>";
            }
            
            if (isset($_POST["btnUpdateProduct"]))
            {
                updateProduct($_POST["ProductID"]);
            }
            elseif (isset($_POST["btnDeleteProduct"]))
            {
                deleteProduct($_POST["ProductID"]);
            }
        }
        ?>
    </body>
</html>