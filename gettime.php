<!DOCTYPE html>
<html>
<head>
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
try {
//Create a PDO instance.
$pdo = new PDO($dsn,$db_username,$db_password,$opt);
$q = $_GET['q'];
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE class = :class");
$stmt->execute(array(":class" => $q));

//According to selected session, fetch the time information and send to the client.
echo<<<FORMTWOSTART
<form name="timeForm" method="post">
    <div class="select">
        <span class="arr"></span>
        <select name="time">
            <option value="">Select a time</option>
FORMTWOSTART;

while($row = $stmt->fetch()) {
    echo "<option value='$row[time]'>$row[time]</option>";
}

echo<<<FORMTWOEND
</select>
    </div>
</form>
FORMTWOEND;

}
catch (PDOException $e) {
    exit("PDO Error: ".$e->getMessage()."<br>");
}
?>
</body>
</html>