<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Training Session Booking System</title>
    <link rel="stylesheet" type="text/css" href="style.css"/>
    <script src="node_modules/sweetalert/dist/sweetalert.min.js"></script>
    <script>
    //Get the time information when user selects the topic.
    function getTimeInformation(str) {
        if (window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest();
        } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("timeInformation").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("POST", "gettime.php?q="+str, true);
        xmlhttp.send();
    }
    </script>
</head>
<body>

<div class="font-style">
<br>
<h1 style="margin-left: 150px">Training Session Booking System</h1>
<br>
<h2 style="margin-left: 300px; font-weight: normal">Training Session Table</h2>
</div>

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
    $information;
    if(hasAvailableSession()) {
        //If there are available sessions, then show the web page.
        generateSessionTable();
        generateTopicDropdownMenu();
        generateTimeDropdownMenu();
        generateNameTextField();
        generateEmailTextField();
        handleForm();
    }
    else {
        //If there are not available sessions, then just display the no available information.
        echo "No available session";
    }
} catch (PDOException $e) {
    exit("PDO Error: ".$e->getMessage()."<br>");
}
$pdo = NULL;

//Generate a table which contains the information of available sessions.
function generateSessionTable()
{
    $stmt = $GLOBALS["pdo"]->prepare("select * from sessionTable");
    $success = $stmt->execute();
    if($success) {
        echo "<table border='1'>";
        echo "<tr>\n<th>Class</th><th>Times</th><th>Capacity</th></tr>\n";
        while ($row = $stmt->fetch()) {
            echo "<tr>\n";
            echo "<td>",$row["Class"],"</td>";
            echo "<td>", $row["Times"],"</td>";
            echo "<td align='center'>", $row["Capacity"],"</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }else {
        echo "Fail to show training information\n";
    }
}

//Generate the topic selection dropdown menu.
function generateTopicDropdownMenu() 
{
echo <<< TOPIC_START
    <div class="form-style-5">
    <form name="form" method="post" class="form-style">
    <fieldset>
    <legend><span class="number">1</span> Select Session</legend>
    <div class="select">
        <span class="arr"></span>
        <select name="topic" onChange="getTimeInformation(this.value)">
            <option value="">Select a topic</option>
TOPIC_START;
        $stmt = $GLOBALS["pdo"]->prepare("SELECT DISTINCT class FROM sessions WHERE place_left>0 ORDER BY class");
        $stmt->execute();
        $isSelected = "";
        while($row = $stmt->fetch()) {
            if (isset($_POST['topic'])) {
                $isSelected = ($_POST['topic'] === $row['class'])? "selected" : "";
            }
            $value = $row['class'];
                echo "\n<option value='$row[class]' $isSelected>$row[class]</option>";
        }
echo <<< TOPIC_END
        </select>
    </div>
TOPIC_END;
}

//Genertate the time selection dropdown menu.
function generateTimeDropdownMenu()
{
echo <<< TIME_START
    <div id="timeInformation">
    <div class="select">
        <span class="arr"></span>
        <select name="time">
            <option value="">Select a time</option>
TIME_START;
    if(isset($_POST['topic'])) {
        $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM sessions WHERE class = :class AND place_left > :place_left");
        $stmt->execute(array(":class" => $_POST['topic'], ":place_left" => 0));
        $isSelected = "";
        while($row = $stmt->fetch()) {
            if (isset($_POST['time'])) {
                $isSelected = ($_POST['time'] === $row['time'])? "selected" : "";
            }
            echo "<option value='$row[time]' $isSelected>$row[time]</option>";  
        }
    }   
echo <<< TIME_END
        </select>
    </div>
    </div>
TIME_END;
}

//Generate the name text field.
function generateNameTextField() {
    $defaultValue = isset($_POST["name"])? $_POST["name"] : "";
    echo <<< NAME
    <legend><span class="number">2</span> Personal Information</legend>
    <input type="text" name="name" value="$defaultValue" placeholder="Name">
NAME;
}

//Generate the email text field.
function generateEmailTextField() {
    $defaultValue = isset($_POST["email"])? $_POST["email"] : "";
    echo <<<EMAIL
    <input type="text" name="email" value="$defaultValue" placeholder="Email">
    </fieldset>
    <input type="submit" name="submit" value="SUBMIT">
    </form>
EMAIL;
}

//Test if there are available sessions in the booking system.
function hasAvailableSession() {
    $availableSessionNumber = 0;
    $stmt = $GLOBALS["pdo"]->prepare("SELECT * FROM sessions");
    $stmt->execute();
    foreach($stmt as $row) {
        $availableSessionNumber += $row["place_left"];
    }
    return $availableSessionNumber;
}

//Handle the information of user input after sumbiting.
function handleForm() {

    if  (isset($_POST['submit'])) {// Test if user submit the form.

        $isTopicValid = isTopicEmpty();
        $isTimeValid = isTimeEmpty();
        $isNameValid = isNameValid();
        $isEmailValid = isEmailValid();

        if($isTopicValid && $isTimeValid && $isNameValid && $isEmailValid) {//Test if the input is valid.
            $topic = $_POST['topic'];
            $time = $_POST['time'];
            $name = $_POST['name'];
            $email = $_POST['email'];

            if(isPlaceLeft($topic, $time)) {//Test if the selected session has place left.

                if(updatePlaceLeft("substract", $topic, $time)) {
                    $stmt = $GLOBALS['pdo']->prepare("INSERT INTO bookings VALUES('$name', '$email', '$topic', '$time')");
                    $successInsert = $stmt->execute();
                    if ($successInsert) {
                        //Book successfully.
                        generateSuccessMessage();
                    } 
                    else {
                        //If booking record uodates fail, recover the place_left.
                        updatePlaceLeft("add", $topic, $time);
                    }
                } 
                else {
                    //Book failed due to database Problem.
                    generateFailMessage("Book Failed: Database Problem");
                }

            }
            else {
                //The session selected has no places left.
                generateFailMessage("Book Failed: No places left!");
            }

        } 
        else {
            //The input information invalid.
            generateFailMessage($GLOBALS['information']);
        }
    }

}

//Test if name is valid.
function isNameValid() {
    if(empty($_POST['name'])) {
        $GLOBALS['information'] .= "Name can not be empty !\n";
        return false;
    }
    elseif (!preg_match("/^['a-zA-Z][a-zA-Z' -]+/", $_POST["name"])) {
        $GLOBALS['information'] .= "Name is not valid !\n";
        return false;
    }
    return true;
}

//Test if email is valid.
function isEmailValid() {
    if(empty($_POST['email'])) {
        $GLOBALS['information'] .= "Email can not be empty !\n";
        return false;
    }
    elseif (!preg_match("/^[a-zA-Z\.-]+@[a-zA-Z\.-]+/", $_POST["email"]) || preg_match("/'{2,}|-{2,}/", $_POST["email"])) {
        $GLOBALS['information'] .= "Email is not valid !\n";
        return false;
    }
    return true;
}

//Test if topic selected is empty.
function isTopicEmpty() {
    if(empty($_POST['topic'])) {
        $GLOBALS['information'] .= "Topic can not be empty !\n";
        return false;
    }
    return true;
}

//Test if time selected is empty. 
function isTimeEmpty() {
    if(empty($_POST['time'])) {
        $GLOBALS['information'] .= "Time can not be empty !\n";
        return false;
    }
    return true;
}

//Test if the selected session has place left.
function isPlaceLeft($topic, $time) {
    $stmt = $GLOBALS['pdo']->prepare("SELECT place_left FROM sessions WHERE class = :class AND time = :time");
    $stmt->execute(array(":class" => $topic, ":time" => $time));
    foreach($stmt as $row) {
        $place_left = $row['place_left'];
    }
    return $place_left;
}

//Update the place of the selected session.
function updatePlaceLeft($str, $topic, $time) {
    $operation = ($str == 'add')? "+ 1" : "- 1";
    $stmt = $GLOBALS['pdo']->prepare("UPDATE sessions SET place_left = place_left $operation WHERE class = :class AND time = :time");
    $success = $stmt->execute(array(":class" => $topic, ":time" => $time));
    return $success;
}

//Generate a success message to inform user.
function generateSuccessMessage() {
    echo '<script type="text/javascript">';
    echo <<< INF
    swal({ 
        title: 'CONGRATULATION', 
        text: 'Book Finished',
        icon: "success",
        button: "OK",
    });
INF;
    echo '</script>';
}

//Generate a fail message to inform user.
function generateFailMessage($str) {
    echo "<input id='errorMessage' type='hidden' value='$str'></input>";
    echo '<script type="text/javascript">';
    echo <<< INF
    swal({ 
        title: 'SORRY', 
        text: document.getElementById('errorMessage').value,
        icon: "error",
        button: "OK",
        });
INF;
    echo '</script>';
}
?>
</body>
</html>