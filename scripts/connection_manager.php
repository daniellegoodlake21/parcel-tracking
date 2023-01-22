<?php
    // sets up the connection to the database and connects to it
    function establishConnection()
    {
        $username = "Danielle";
        $host = "localhost";
        $database = "parcel-tracking";
        $password = "placeholder"; // I have replaced my password with the word 'placeholder' for the GitHub repository.
        $connection = mysqli_init();
        if (!$connection)
        {
            echo "<p class=\"message\">There was an error initialising the database connection.</p>";
        }
        else
        {
            $connection = new mysqli($host, $username, $password, $database);
            if ($connection -> connect_errno) 
            {
                echo "<p class=\"message\">Failed to connect to the database.</p>";
            }
            else
            {
                return $connection;
            }
        }
        return null;
    }
?>