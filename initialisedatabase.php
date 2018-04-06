<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Place_left and Booking_list Initialise Script</title>
    <link rel="stylesheet" type="text/css" href="style.css"/>
    <script src="node_modules/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>

<?php
//Set the information of database. 
$db_hostname = "mysql";
$db_database = "x7zl3";
$db_username = "x7zl3";
$db_password = "lzy-0401";
$db_charset = "utf8mb4";
$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";
$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false
    );

try{
    //Create a PDO instance.
    $pdo = new PDO($dsn,$db_username,$db_password,$opt);

    //Set place_left of all sessions to inital value.
    $stmt = $pdo->prepare("UPDATE sessions SET place_left = capacity");
    $successIniPL = $stmt->execute();

    //Empty all the bookings lists.
    $stmt = $pdo->prepare("TRUNCATE TABLE bookings");
    $successEmpList = $stmt->execute();

        if ($successIniPL && $successEmpList) {
            generateSuccessMessage();
        }
        else {
            generateFailMessage();
        }
    
} catch (PDOException $e) {
    exit("PDO Error: ".$e->getMessage()."<br>");
}

$pdo = NULL;

//Generate the successful information of initialisation.
function generateSuccessMessage() {
    echo '<script type="text/javascript">';
    echo <<< INF
    swal({ 
        title: 'CONGRATULATION', 
        text: 'Sessions place_left: Initialised\\nBooking List: Initialised',
        icon: "success",
        button: "OK",
    });
INF;
    echo '</script>';
}

//Generate the failed information of initialisation.
function generateFailMessage($str) {
    echo '<script type="text/javascript">';
    echo <<< INF
    swal({ 
        title: 'SORRY', 
        text: 'Initialised',
        icon: "error",
        button: "OK",
    });
INF;
    echo '</script>';
}
?>
</body>
</html>