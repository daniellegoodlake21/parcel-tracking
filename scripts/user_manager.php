<?php
    // creates a user session in the database which can be used when reopening the website and logging back in
    function createUserSession($connection, $userID)
    {
        $query = "INSERT INTO Session (RegisteredUserID) VALUES (" . $userID . ");";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to create user session.</p>";
        }
    }
    // gets session ID associated with user from the database
    function getSessionIDForUser($userID)
    {
        $connection = establishConnection();
        $query = "SELECT SessionID FROM Session WHERE RegisteredUserID = " . $userID . ";";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to get user session.</p>";
            mysqli_close($connection);
            return false;
        }
        elseif (mysqli_num_rows($result) == 0)
        {
            createUserSession($connection, $userID);   
            $result = mysqli_query($connection, $query); // re-run the query because new data was added
        }
        $sessionID = mysqli_fetch_assoc($result)['SessionID'];
        mysqli_close($connection);
        return $sessionID;
    }
    // get user's first name and last name for displaying who the parcel belongs to in the parcels list found on the Manage Parcels admin webpage
    function getUserName($userID)
    {
        $connection = establishConnection();
        $query = "SELECT FirstName, Surname FROM RegisteredUser WHERE RegisteredUserID = " . $userID . ";";
        $result = mysqli_query($connection, $query);
        mysqli_close($connection);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to get user first name and surname.</p>";
            return false;
        }
        $user = mysqli_fetch_assoc($result);
        return $user;
    }
    // returns true if the user is an admin otherwise returns false
    function userIsAdmin()
    {
        $userID = $_SESSION['RegisteredUserID'];
        if (empty($userID))
        {
            return false;
        }
        $connection = establishConnection();
        $query = "SELECT Admin FROM RegisteredUser WHERE RegisteredUserID = '". $userID . "';";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Checking if user is admin failed. Using default value of false.</p>";
            return false;
        }
        $isAdmin = mysqli_fetch_assoc($result)['Admin'];
        return $isAdmin;
    }
    // returns an array of all users listed in the database
    function selectUsers($adminUserRequired=false)
    {
        if ($adminUserRequired and !userIsAdmin())
        {
            echo "<p class=\"message\">You must be an admin to access user information in the Manage Users page.<br><a href=\"index.php\">Return to Homepage</a><br></p>";
            return false;
        }
        $users = [];
        $connection = establishConnection();
        if (isset($connection))
        {
            $query = "SELECT RegisteredUserID, FirstName, Surname, Email FROM RegisteredUser;";
            $result = mysqli_query($connection, $query);
            if (!$result)
            {
                echo "<p class=\"message\">Failed to retrieve users from the database.</p>";
            }  
            else
            {
                foreach ($result as $user)
                {
                    $users[$user['RegisteredUserID']] = $user;
                }
            }
            mysqli_close($connection);
        }
        return $users;
    }
    // registers a user
    function registerUser()
    {
        $firstName = $_POST['FirstName'];
        $surname = $_POST['Surname'];
        $email = $_POST['Email'];
        $valid = isCollectiveUserDataValid("REGISTER");
        if (!$valid)
        {
            return false;
        }
        $passwordHash = password_hash($_POST['Password'], PASSWORD_DEFAULT);
        // add the user to the database
        $connection = establishConnection();
        if (isset($connection))
        {
            $query = "INSERT INTO RegisteredUser (FirstName, Surname, Email, PasswordHash) VALUES ('" . $firstName . "', '" . $surname . "', '" . $email . "', '" . $passwordHash . "');";
            $result = mysqli_query($connection, $query);
            if (!$result)
            {
                // after sanitisation and validation the only error that will occur is if the user has entered an existing email address, as the email is a unique field in the RegisteredUser table
                echo "<p class=\"registerMessage\">There is already a user with this email address. Please try again.<br></p>";
                mysqli_close($connection);
                return false;
            }
            else
            {
                echo "<p class=\"registerMessage\">Welcome, " . htmlspecialchars($firstName) . "!<br>You are now registered.<br></p>";
                mysqli_close($connection);
            }
            $connection = establishConnection();
            $userID = getUserID($connection, $email);
            mysqli_close($connection);
            unset($firstName);
            unset($surname);
            unset($email);
            unset($password);
            unset($passwordHash);
            return $userID;
        }
        return false;
    }
    // gets user ID from RegisteredUser table in database
    function getUserID($connection, $email)
    {
        // email is unique in the table, therefore it is possible to select a user by their email
        // however, email is modifiable by the administrator when editing user details, so it cannot
        // be the primary key
        $query = "SELECT RegisteredUserID FROM RegisteredUser WHERE Email = '". $email . "';";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Getting user ID failed.</p>";
            return false;
        }
        $currentUserResult = mysqli_fetch_assoc($result);
        $userID = $currentUserResult['RegisteredUserID'];
        return $userID;
    }
    // logs the user in
    function loginUser()
    {
        $email = $_POST['Email'];
        $valid = isCollectiveUserDataValid("LOGIN");
        if (!$valid)
        {
            return false;
        }
        $connection = establishConnection();
        if (isset($connection))
        {
            $query = "SELECT * FROM RegisteredUser WHERE Email = '" . $email . "';";
            $result = mysqli_query($connection, $query);
            if (!$result)
            {
                echo "<p class=\"loginMessage\">Failed to get users matching this email.</p>";
                mysqli_close($connection);
                return false;
            }
            else
            {
                if (mysqli_num_rows($result) == 0)
                {
                    echo "<p class=\"loginMessage\">A user with this email and password was not found.</p>";
                    mysqli_close($connection);
                    return false;
                }
                elseif (mysqli_num_rows($result) > 1)
                {
                    echo "<p class=\"loginMessage\">Multiple users with this email and password were found. This should not happen.</p>";
                    mysqli_close($connection);
                    return false;
                }
                else
                {
                    $user = mysqli_fetch_assoc($result);
                    $passwordCorrect = password_verify($_POST['Password'], $user['PasswordHash']);
                    mysqli_close($connection);
                    if ($passwordCorrect)
                    {

                        echo "<p class=\"loginMessage\">Successfully logged in! Welcome back, " . $user['FirstName'] . "!</p>";
                        $userID = $user['RegisteredUserID'];
                        unset($email);
                        unset($password);
                        unset($passwordCorrect);
                        return $userID;                        
                    }
                    else
                    {
                        unset($email);
                        unset($password);
                        unset($passwordCorrect);
                        echo "<p class=\"loginMessage\">Incorrect passsword.</p>";
                        return false;
                    }
                }
            }
        }
    }
    // updates a user in the database
    function updateUser($userID)
    {
        $valid = isCollectiveUserDataValid("MANAGE");
        if (!$valid)
        {
            return false;
        }
        $connection = establishConnection();
        $query = "UPDATE RegisteredUser SET FirstName = '" . $_POST['FirstName'] . "', Surname = '" . $_POST['Surname'] . "', Email = '" . $_POST['Email'] . "' WHERE RegisteredUserID = " . $userID . ";";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to update user.</p>";
        }
        else
        {
            echo "<p class=\"message\">The user has been updated.</p>";
        }
    }
    // deletes a user from the database
    function deleteUser($userID)
    {
        if ($userID == "69")
        {
            echo "<p class=\"message\">Cannot delete admin user.</p>";
            return;
        }
        $connection = establishConnection();
        $query = "DELETE FROM RegisteredUser WHERE RegisteredUserID = " . $userID . ";";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "<p class=\"message\">Failed to delete user.</p>";
        }
        else
        {
            echo "<p class=\"message\">User deleted.</p>";
        }
        mysqli_close($connection);
    }
?>