<?php
    // checks user input is valid and sanitized
    function validateInput($input, $inputType)
    {
        if ($inputType == "EMAIL")
        {
            $pattern = "/[a-zA-Z0-9\-_.]@[a-zA-Z0-9\-_.].[a-zA-Z0-9\-_.]/"; // allows all letters, numbers, -, _, . and @
        }
        elseif ($inputType == "NAME")
        {
            $pattern = "/[a-zA-Z\-]/"; // allows only letters (capital and lowercase) or dashes to be included in a name
        }
        elseif ($inputType == "PASSWORD")
        {
            $pattern = "/[a-zA-Z0-9\-\+!_?£$&*()#]{8,30}/"; // allows all letters, numbers, +, -, !, ?, £, $, & and * - must be between 8 and 30 characters long
        }
        elseif ($inputType == "ADDRESSLINE")
        {
            $pattern = "/[a-zA-Z0-9.\-\s]{0,100}/"; // allows all letters, numbers, ., -, and space between 0 and 100 characters long
        }
        elseif ($inputType == "TOWN")
        {
            $pattern = "/[a-zA-Z\-.\s]/"; // allows all letters, ., -, and space
        }
        elseif ($inputType == "POSTCODE")
        {
            $pattern = "/[a-zA-Z0-9]{5,8}/"; // allows a range of 6 to 8 characters (excluding spaces)
        }
        elseif($inputType == "SAFEPLACE")
        {
            $pattern = "/[a-zA-Z0-9\s\-]{0,50}/"; // allows all letters, numbers, -, and space between 0 and 50 characters long
        }
        elseif ($inputType == "TRACKINGNUMBER")
        {
            $pattern = "/[0-9]{6,6}/"; // allows exactly 6 digits
        }
        elseif ($inputType == "PRODUCTNAMESEARCH" or $inputType == "PRODUCTNAME" or $inputType == "COMPANYNAME")
        {
            $pattern = "/[a-zA-Z0-9\s\-]/"; // allows all letters, numbers, -, and space
        }
        elseif ($inputType == "NEIGHBOURHOUSENUMBER")
        {
            $pattern = "/[0-9]{0,5}/"; // allows between 0 and 5 digits
        }
        elseif ($inputType == "STOCK" or $inputType == "QUANTITY")
        {
            $pattern = "/[0-9]{1,5}/"; // allows between 1 and 5 digits
        }
        elseif ($inputType == "PRODUCTDESCRIPTION" or $inputType == "STATUSUPDATETEXT")
        {
            $pattern = "/[a-zA-Z0-9\s\-\p{P}]/"; // allows all relevant punctuation, all letters, numbers, and space
        }
        elseif ($inputType == "MONEY")
        {
            $pattern = "/[0-9.]{4,9}/"; // allows 4-9 characters - all of which must be either a digit or full stop
        }
        elseif ($inputType == "WEIGHT")
        {
            $pattern = "/[0-9.]{1,8}/"; // allows 1-8 characters - all of which must be either a digit or a full stop
        }
        elseif ($inputType == "EAN")
        {
            $pattern = "/[0-9]{13,13}/"; // allows exactly 13 digits
        }
        $valid = preg_match($pattern, $input);
        if ($valid == 0)
        {
            return false;
        }
        // check the input does not contain any single or double quotes (if it does then it is invalid input)
        elseif (substr_count($input, "\"") > 0 or substr_count($input, "'") > 0)
        {
            return false;
        }
        return true;
    }
// validates place order data
function isCollectivePlaceOrderDataValid()
{
    $recipientFirstNameValid = validateInput($_POST['RecipientFirstName'], "NAME");
    $recipientSurnameValid = validateInput($_POST['RecipientSurname'], "NAME");
    $addressLine1Valid = validateInput($_POST['AddressLine1'], 'ADDRESSLINE');
    $addressLine2Valid = validateInput($_POST['AddressLine2'], 'ADDRESSLINE');
    $townValid = validateInput($_POST['Town'], 'TOWN');
    $postcodeValid = validateInput(str_replace(' ', '', $_POST['Postcode']), 'POSTCODE');
    $safePlaceValid = validateInput($_POST['SafePlace'], 'SAFEPLACE');
    $neighbourHouseNumberValid = validateInput($_POST['NeighbourHouseNumber'], "NEIGHBOURHOUSENUMBER");
    if ($addressLine1Valid and $addressLine2Valid and $townValid and $postcodeValid and $safePlaceValid and $neighbourHouseNumberValid)
    {
        return true;
    }
    elseif (!$recipientFirstNameValid)
    {
        echo "<p class=\"message\">Sorry, recipient first name is invalid - it must not contain quotes or punctuation. Please try again.</p>";
    }
    elseif (!$recipientSurnameValid)
    {
        echo "<p class=\"message\">Sorry, recipient surname is invalid - it must not contain letters or punctuation. Please try again.</p>";
    }
    elseif (!$addressLine1Valid)
    {
        echo "<p class=\"message\">Address Line 1 invalid - it must not contain quotes. Please try again.</p>";
    }
    elseif (!$addressLine2Valid)
    {
        echo "<p class=\"message\">Address Line 2 invalid - it must not contain quotes. Please try again.</p>";
    }
    elseif (!$townValid)
    {
        echo "<p class=\"message\">Town invalid - it must not contain quotes or punctuation. Please try again.</p>";
    }
    elseif (!$postcodeValid)
    { 
        echo "<p class=\"message\">Postcode invalid - it should be no less than 5 and no more than 8 characters (excluding spaces).<br>Please try again.</p>";
    }
    elseif (!$safePlaceValid)
    {
        echo "<p class=\"message\">Safe place invalid - it must not contain quotes or punctuation. Please try again.</p>";
    }
    elseif (!$neighbourHouseNumberValid)
    {
        echo "<p class=\"message\">Neighbour house number invalid - it must be an integer no longer than 5 digits. Please try again.</p>";
    }
    return false;
}
// validates user data for login, registration and amdin user management 
function isCollectiveUserDataValid($formType)
{
    {
        // validate and sanitize inputs
        // the form type will be either LOGIN, REGISTER or MANAGE
        // login form only contains email and password so there is no need to check FirstName and Surname fields as they are not present
        if ($formType == "REGISTER" or $formType == "MANAGE")
        {
            $firstName = $_POST['FirstName'];
            $firstNameValid = validateInput($firstName, "NAME");
            if (!$firstNameValid)
            {
                echo "<p class=\"message\">Your input for the first name field is invalid. Please try again using only letters and dashes</p>";
                return false;
            }
            $surname = $_POST['Surname'];
            $surnameValid = validateInput($surname, "NAME");
            if (!$surnameValid)
            {
                echo "<p class=\"message\">Your input for the last name field in invalid. Please try again using only letters and dashes.</p>";
                return false;
            }
        }
        // email is always present in the form so is always checked
        $email = $_POST['Email'];
        $emailValid = validateInput($email, "EMAIL");
        if (!$emailValid)
        {
            echo "<p class=\"loginMessage\">Your input for the email address field is invalid. Please include the @ symbol, do not include quotes, and try again.</p>";
            return false;
        }
        // the validity of the password should only be checked if the form type is LOGIN or REGISTER
        if ($formType == "REGISTER" or $formType == "LOGIN")
        {
            $password = $_POST['Password'];
            $passwordValid = validateInput($password, "PASSWORD");
            if (!$passwordValid)
            {
                echo "<p class=\"loginMessage\">Invalid password. Must be 8 to 30 characters and must not contain quotes, commas, or periods. Please try again.</p>";
                return false;
            }
        }
        return true;
    }
}
// validates input when searching for a parcel by tracking number and postcode
function validateParcelSearchInput($trackingNumber, $postcode)
{
    $isValidTrackingNumber = validateInput($trackingNumber, 'TRACKINGNUMBER');
    $isValidPostcode = validateInput(str_replace(" ", "", $postcode), 'POSTCODE');
    // if both are valid then search for the parcel
    if ($isValidPostcode and $isValidTrackingNumber)
    {
        return true;
    }
    elseif ($isValidTrackingNumber)
    {
        // if only the tracking number is valid, display a message telling the user the postcode input is the incorrect length, please try again
        echo "<p class=\"message\">Sorry, the postcode you have searched for is invalid.<br>It should be no less than 5 and no more than 8 characters (excluding spaces).<br>Please try again.</p>";
    }
    elseif($isValidPostcode)
    {
        // if only the postcode is valid, display a message telling the user the tracking number input is the incorrect length, please try again
        echo "<p class=\"message\">Sorry, the tracking number you have searched for is the incorrect length.<br>It should be 6 characters long.<br>Please try again.</p>";
    }
    else
    {
        // if both the postcode and tracking number are invalid, display a message explaining both
        echo "<p class=\"message\">Sorry, the postcode and tracking number are invalid.<br>Correct postcode length: 5-8 characters (excluding spaces).<br>Correct tracking number length: 6 characters.<br>Please try again.</p>";
    }
    return false;
}
// validates searching parcel by product name
function validateParcelSearchByProductInput()
{
    $productName = $_POST['ProductName'];
    $valid = validateInput($productName, "PRODUCTNAMESEARCH");
    if (!$valid)
    {
        echo "<p class=\"message\">Sorry, the product name search text is invalid. Please try again.</p>";
    }
    return $valid;
}
// validates alternative delivery arrangements (in My Parcels webpage)
function isCollectiveAlternativeDeliveryArrangentsInputValid()
{
    $neighbourHouseNumber = $_POST['NeighbourHouseNumber'];
    $safePlace = $_POST['SafePlace'];
    $neighbourHouseNumberValid = validateInput($neighbourHouseNumber, "NEIGHBOURHOUSENUMBER");
    $safePlaceValid = validateInput($safePlace, "SAFEPLACE");
    if ($neighbourHouseNumberValid and $safePlaceValid)
    {
        return true;
    }
    elseif ($neighbourHouseNumberValid)
    {
        echo "<p class=\"message\">Sorry, the safe place must not contain quotes or punctuation marks or brackets. Please try again.</p>";
    }
    else if ($safePlaceValid)
    {
        echo "<p class=\"message\">Sorry, the neighbour house number must be an integer. Please try again.</p>";
  
    }
    else
    {
        echo "<p class=\"message\">Sorry, both inputs are invalid. The safe place must not contain quotes or punctuation marks or brackets.<br>The neighbour house number must be an integer. Please try again.</p>";
    }
    return false;
}
// validates product information when an admin updates or creates a product
function isCollectiveProductInputValid()
{
    $productNameValid = validateInput($_POST['ProductName'], "PRODUCTNAME");
    $productDescriptionValid = validateInput($_POST['ProductDescription'], "PRODUCTDESCRIPTION");
    $companyNameValid = validateInput($_POST['CompanyName'], "COMPANYNAME");
    $priceValid = validateInput($_POST['Price'], "MONEY");
    $costPriceValid = validateInput($_POST['CostPrice'], "MONEY");
    $stockValid = validateInput($_POST['Stock'], "STOCK");
    $eanValid = validateInput($_POST['EAN'], "EAN");
    $weightValid = validateInput($_POST['Weight'], "WEIGHT");
    if (!$productNameValid)
    {
        echo "<p class=\"message\">Sorry, the product name is invalid - it must not contain quotes or punctuation marks. Please try again.</p>";
    }
    elseif (!$productDescriptionValid)
    {
        echo "<p class=\"message\">Sorry, the product description is invalid - it must not contain quotes. Please try again.</p>";
    }
    elseif (!$companyNameValid)
    {
        echo "<p class=\"message\">Sorry, the company name is invalid - it must not contain quotes or punctuation marks. Please try again.</p>";
    }
    elseif (!$priceValid)
    {
        echo "<p class=\"message\">Sorry, the price is invalid - it must be an integer or a decimal. Please try again.</p>";
    }
    elseif (!$costPriceValid)
    {
        echo "<p class=\"message\">Sorry, the cost price is invalid - it must be an integer or a decimal. Please try again.</p>";
    }
    elseif (!$stockValid)
    {
        echo "<p class=\"message\">Sorry, the stock is invalid - it must be an integer 5 or less digits long. Please try again.</p>";
    }
    elseif (!$eanValid)
    {
        echo "<p class=\"message\">Sorry, the EAN is invalid - it must be an integer exactly 13 characters long. Please try again.</p>";
    }
    elseif (!$weightValid)
    {
        echo "<p class=\"message\">Sorry, the weight is invalid - it must be an integer or decimal value. Please try again.</p>";
    }
    else
    {
        return true;
    }
    return false;
}
// validates admin's input when updating parcel information
function isCollectiveManageParcelInputValid()
{
    $weightValid = validateInput($_POST['Weight'], "WEIGHT");
    $productQuantityValid = validateInput($_POST['ProductQuantity'], "QUANTITY");
    $otherDeliveryInfoValid = isCollectivePlaceOrderDataValid(); // this function
    // already displays invalid input messages within this function
    // so no need to do it again. It checks recipient first name and surname, address lines 1 and 2, town, postcode,
    // safe place and neighbour house number
    if ($weightValid and $productQuantityValid and $otherDeliveryInfoValid)
    {
        return true;
    }
    elseif (!$weightValid)
    {
        echo "<p class=\"message\">Sorry, the weight is invalid - it must be an integer or decimal. Please try again.</p>";
    }
    elseif (!$productQuantityValid)
    {
        echo "<p class=\"message\">Sorry, the product quantity is invalid - it must be an integer no longer than 5 digits. Please try again.</p>";
    }
    return false;
}
// validates admin's input when creating a parcel location status update
function isStatusUpdateValid()
{
    if (empty($_POST['Date']))
    {
        echo "<p class=\"message\">Please enter the date of the parcel status update first.</p>";
        return false;
    }
    if (empty($_POST['Time']))
    {
        echo "<p class=\"message\">Please enter the time of the parcel status update first.</p>";
        return false;
    }
    $statusText = $_POST['StatusDescription'];
    $valid = validateInput($statusText, "STATUSUPDATETEXT");
    if (!$valid)
    {
        echo "<p class=\"message\">Sorry, the status update text is invalid - it must not contain quotes. Please try again.</p>";
    }
    return $valid;
}
?>