<?php

    // returns the number of pages of products in the catalogue
    function getNumberOfPages()
    {
        $productsPerPage = 3; // can make an attribute of a class such as ProductManager
        $products = selectProducts();
        $numberOfPages = ceil(count($products) / $productsPerPage);
        return $numberOfPages;
    }
    // retrieves details of all products in the database
    function selectProducts($adminUserRequired=false)
    {
        if ($adminUserRequired and !userIsAdmin())
        {
            echo "<p class=\"message\">You must be an admin to access product information in the Manage Products page.<br><a href=\"index.php\">Return to Homepage</a><br></p>";
            return false;
        }   
        $connection = establishConnection();
        $query = "SELECT * FROM Product;";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        $products = [];
        if (!$result)
        {
            echo "<p class=\"message\">Failed to select products.</p>";
            return false;
        }
        foreach ($result as $product)
        {
            $products[] = $product;
        }
        return $products;
    }
    // selects products to display on current page
    function selectProductsOnPage($currentPage, $shoppingCartProducts=[])
    {
        $connection = establishConnection();
        $productsPerPage = 3;
        $offset = ($currentPage-1) * $productsPerPage;
        $query = "SELECT * FROM Product LIMIT " . $offset . "," . $productsPerPage . ";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to get products for this page.</p>";
            return false;
        }
        $products = [];
        $shoppingCartProductIDs = [];
        foreach ($shoppingCartProducts as $cartProduct)
        {
            $shoppingCartProductIDs[] = $cartProduct['ProductID'];
        }
        foreach ($result as $product)
        {
            $product['ExistsInShoppingCart'] = false;
            if (in_array($product['ProductID'], $shoppingCartProductIDs))
            {
                $product['ExistsInShoppingCart'] = true; // prevents the user from buying another one of the same products (they can increase the product quantity instead)
            }
            $products[] = $product;
        }
        return $products;
    }
    // adds a product to the catalogue
    function createProduct()
    {
        $valid = isCollectiveProductInputValid(); // handles invalid input messages within the function
        if (!$valid)
        {
            return;
        }
        $connection = establishConnection();
        $query = "INSERT INTO Product (ProductName, ProductDescription, CompanyName, Price, CostPrice, Stock, EAN, Weight) VALUES (\"" . $_POST['ProductName'] . "\", \"" . $_POST['ProductDescription'] . "\", \"" . $_POST['CompanyName'] . "\", " . $_POST['Price'] . ", " . $_POST['CostPrice'] . ", " . $_POST['Stock'] . ", \"" . $_POST['EAN'] . "\", Weight = " . $_POST['Weight'] . ");";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to create product.</p>";
            echo "<p class=\"message\">". $query . "</p>";
            return;
        }
        echo "<p class=\"message\">Product \"" . $_POST['ProductName'] . "\" created successfully.</p>";
    }
    // reduces stock when order is placed
    function reduceStock($productID, $productQuantity)
    {
        $connection = establishConnection();
        $query = "SELECT Stock FROM Product WHERE ProductID = " . $productID . ";";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to find stock after ordering parcel.</p>";
            return;
        }
        $stock = mysqli_fetch_assoc($result)['Stock']-$productQuantity;
        $query2 = "UPDATE Product SET Stock = " . $stock . " WHERE ProductID = " . $productID . ";";
        $result2 = mysqli_query($connection, $query2);
        mysqli_close($connection);
        if (!$result2)
        {
            echo "<p class=\"message\">Failed to reduce stock after ordering parcel. However this can be done manually by the admin user</p>";
        }
    }
    // delete a product from the catalogue
    function deleteProduct($productID)
    {
        $connection = establishConnection();
        $query = "DELETE FROM Product WHERE ProductID = " . $productID . ";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to update product.</p>";
            echo "<p class=\"message\">". $query . "</p>";
            return;
        }
        echo "<p class=\"message\">Product \"" . $_POST['ProductName']  . "\" has been deleted.</p>";
    }
    // update the details of a product
    function updateProduct($productID)
    {
        $valid = isCollectiveProductInputValid(); // handles invalid input messages within the function
        if (!$valid)
        {
            return;
        }
        $connection = establishConnection();
        $query = "UPDATE Product SET ProductName = \"" . $_POST['ProductName'] . "\", ProductDescription = \"" . $_POST['ProductDescription'] . "\", CompanyName = \"" . $_POST['CompanyName'] . "\", Price = " . $_POST['Price'] . ", CostPrice = " . $_POST['CostPrice'] . ", Stock = " . $_POST['Stock'] . ", EAN = \"" . $_POST['EAN'] . "\", Weight = " . $_POST['Weight'] . " WHERE ProductID = " . $productID . ";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to update product.</p>";
            echo "<p class=\"message\">". $query . "</p>";
            return;
        }
        echo "<p class=\"message\">Product updated successfully.</p>";
    }
    // removes unwanted product from the current user's shopping cart
    function removeProductFromShoppingCart($productID, $sessionID)
    {
        $connection = establishConnection();
        $query = "DELETE FROM SessionProductLink WHERE ProductID = " . $productID . " AND SessionID = " . $sessionID . ";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to remove product from shopping cart.</p>";
        }
        else
        {
            echo "<p class=\"message\">Product has been removed from your shopping cart.</p>";
        }
    }
    // add produdct to shopping cart so that it is saved even if the user exits and returns later
    function addProductToShoppingCart($productID, $productQuantity, $sessionID)
    {
        $connection = establishConnection();
        $query = "INSERT INTO SessionProductLink (SessionID, ProductID, ProductQuantity) VALUES (" . $sessionID . ", " . $productID . ", " . $productQuantity . ");";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to add product to shopping cart.</p>";
        }
    }
    // gets the products in the current user's shopping cart
    function getShoppingCartProducts($sessionID)
    {
        if (empty($sessionID))
        {
            echo "<p class=\"message\">You must be logged in to view your shopping cart.</p>";
            return;
        }
        $connection = establishConnection();
        $query = "SELECT Product.ProductID, ProductName, ProductDescription, CompanyName, Price, Stock, Weight, SessionProductLink.ProductQuantity FROM Product, SessionProductLink WHERE SessionProductLink.SessionID = " . $sessionID . " AND SessionProductLink.ProductID = Product.ProductID;";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to get shopping cart.</p";
            mysqli_close($connection);
            return false;
        }
        $shoppingCartProducts = [];
        if (mysqli_num_rows($result) == 0)
        {
            echo "<p class=\"message\">You have no items in your shopping cart.</p>";
        }
        else
        {
            foreach ($result as $product)
            {
                $shoppingCartProducts[] = $product;
            }
            echo "<p class=\"message\">You have " . mysqli_num_rows($result) . " items in your shopping cart.</p>";
        }
        mysqli_close($connection);
        return $shoppingCartProducts;
    }
    // resets shopping cart after placing an order
    function resetShoppingCart($userID)
    {
        $sessionID = getSessionIDForUser($userID);
        $connection = establishConnection();
        $query = "DELETE FROM SessionProductLink WHERE SessionID = " . $sessionID . ";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to reset shopping cart.</p>";
            return;
        }
        echo "<p class=\"message\">Your order was placed successfully!</p>";
    }
?>