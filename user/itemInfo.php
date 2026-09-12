<?php
require __DIR__ . "/../assets/config.php";
/** @var \mysqli $conn */
session_start();

//Handle SEARCH
if(isset($_POST["search"])) {
    //Check for valid value
    if(!isset($_POST["value"])) {
        http_response_code(400);
        echo "Search needs value!";
        die();
    }

    //Check for valid sheet
    if(!isset($_POST["id"])) {
        http_response_code(400);
        echo "Search needs sheet id!";
        die();
    }

    //Check for valid search
    if($_POST["search"] != "where" && $_POST["search"] != "name") {
        http_response_code(400);
        echo "Invalid search table!";
        die();
    }

    //Run SQL
    $search = $_POST["search"];
    $sheet = $_POST["id"];
    $value = $_POST["value"];
    $stmt = $conn->prepare("SELECT t.`$search` FROM `$sheet` t WHERE MATCH(`$search`) AGAINST(? IN BOOLEAN MODE) LIMIT 20");
    if(!$stmt->bind_param("s", $value) || !$stmt->execute()) {
        http_response_code(400);
        echo "Invalid MySQL error!";
        die();
    }

    //Convert to JSON
    $result = $stmt->get_result();
    $values = [];
    while($row = $result->fetch_row()) {
        $values[] = $row[0];
    }
    $stmt->close();
    $result->close();
    header('Content-Type: application/json');
    echo (json_encode($values));
    die();
}

//Handle POST
if(isset($_POST["action"])) {
    switch($_POST["action"]) {
        case "changeName": {
            //Validate
            if(!isset($_POST["id"]) || !isset($_POST["name"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Change name
            $stmt = $conn->prepare("UPDATE `_tables` SET `name`=? WHERE id_tables=?");
            $id = ConvertFromBase62($_POST["id"]);
            $name = $_POST["name"];
            if(!$stmt->bind_param("si", $name, $id) || !$stmt->execute() || $stmt->affected_rows == 0 || !$stmt->close()) {
                http_response_code(400);
                echo "Error saving name!";
                die();
            }
            http_response_code(200);
            echo "Name saved.";
            die();
        }
        case "changePassword": {
            //Validate
            if(!isset($_POST["id"]) || !isset($_POST["password"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Change password
            $stmt = $conn->prepare("UPDATE `_tables` SET `password`=? WHERE id_tables=?");
            $id = ConvertFromBase62($_POST["id"]);
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            if(!$stmt->bind_param("si", $password, $id) || !$stmt->execute() || $stmt->affected_rows == 0 || !$stmt->close()) {
                http_response_code(400);
                echo "Error saving password!";
                die();
            }
            http_response_code(200);
            echo "Password saved.";
            die();
        }
        case "archive": {
            //TODO: Archive

            //Run SQL
            $id = ConvertFromBase62($_POST["id"]);
            $stmt = $conn->prepare("UPDATE `tables` SET archived=1 WHERE id_tables=?");
            if(!$stmt->bind_param("i", $id) || !$stmt->execute() || $stmt->affected_rows == 0 || !$stmt->close()) {
                http_response_code(400);
                echo "Error archiving sheet.";
                exit();
            }
            http_response_code(201);
            echo "ok";
            exit();
        }
        case "delete": {
            //TODO: Delete

            //Run SQL
            $id = ConvertFromBase62($_POST["id"]);
            $stmt = $conn->prepare("DELETE FROM `tables` WHERE id_tables=?");
            if(!$stmt->bind_param("i", $id) || !$stmt->execute() || $stmt->affected_rows == 0) {
                http_response_code(400);
                echo "Error deleting sheet.";
                exit();
            }
            http_response_code(201);
            echo "ok";
            exit();
        }
        case "insert": {
            //Validate
            if(!isset($_POST["when"]) || !isset($_POST["where"]) || !isset($_POST["who"]) || !isset($_POST["name"]) || !isset($_POST["count"]) || !isset($_POST["price"]) || !isset($_POST["id"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Run SQL
            $stmt = $conn->prepare("INSERT INTO `" . $_POST["id"] . "`(`when`, `where`, `who`, `name`, `cnt`, `price`) VALUES (?,?,?,?,?,?)");
            $when = $_POST["when"];
            $where = $_POST["where"];
            $who = $_POST["who"];
            $name = $_POST["name"];
            $cnt = $_POST["count"];
            $price = $_POST["price"];
            if(!$stmt->bind_param("ssssss", $when,$where,$who,$name,$cnt,$price) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Error inserting sheet.";
                exit();
            }
            http_response_code(201);
            echo "ok";
            exit();
        }
        case "update": {
            //Validate
            if(!isset($_POST["when"]) || !isset($_POST["where"]) || !isset($_POST["who"]) || !isset($_POST["name"]) || !isset($_POST["count"]) || !isset($_POST["price"]) || !isset($_POST["id"])|| !isset($_POST["item"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Run SQL
            $stmt = $conn->prepare("UPDATE `" . $_POST["id"] . "` SET `when`=?,`where`=?,`who`=?,`name`=?,`cnt`=?,`price`=? WHERE id = ?");
            $when = $_POST["when"];
            $where = $_POST["where"];
            $who = $_POST["who"];
            $name = $_POST["name"];
            $cnt = $_POST["count"];
            $price = $_POST["price"];
            $item = $_POST["item"];
            if(!$stmt->bind_param("ssssssi", $when,$where,$who,$name,$cnt,$price,$item) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Error updating sheet.";
                exit();
            }
            http_response_code(201);
            echo "ok";
            exit();
        }
    }
    http_response_code(400);
    echo "Missing action parameter.";
    die();
}

//Handle missing ID
if(!isset($_GET["id"])) {
    http_response_code(400);
    echo "Missing ID parameter.";
    die();
}

//Get name
$nameStmt = $conn->prepare("SELECT name, persons FROM `_tables` WHERE id_tables=?");
$id = ConvertFromBase62($_GET["id"]);
if(!$nameStmt->bind_param("s",$id) || !$nameStmt->execute() || !$nameStmt->bind_result($name, $persons) || !$nameStmt->fetch() || !$nameStmt->close()) {
    http_response_code(400);
    echo "Invalid sheet ID.";
    die();
}
$tableId = ConvertFromBase62($_GET["id"]);
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Manage item</title>
        <link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
        <link rel="stylesheet" href="../assets/style.css" />
        <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
    </head>
    <body class="formBackground" form-box-holder>
        <form-box>
            <p class="formHeader"><?php echo (isset($_GET["item"])) ? "Manage" : "Add" ?> item</p>
            <form-input id="when" tabindex=1 minlength=1 label="When:" type="datetime-local" placeholder="When"></form-input>
            <form-input id="where"  tabindex=2 minlength=1 label="Where:" type="search-realtime" placeholder="Where"></form-input>
            <datalist id="places">
                <?php
                    $tableId = ConvertFromBase62($_GET["id"]);
                    $result = $conn->query("SELECT `where` FROM " . $_GET["id"] . " GROUP BY `where`");
                    while ($row = $result->fetch_assoc()) {
                        echo '<option label="' . $row["where"] . '" value="' . $row["where"] .'"></option>';
                    }
                ?>
            </datalist>
            <form-input id="who"  tabindex=3 label="Who:" type="select" placeholder="Who" list="userNames"></form-input>
            <datalist id="userNames">
                <?php
                    $stmt = $conn->prepare("SELECT persons FROM _tables WHERE id_tables = ?");

                    if(!$stmt->bind_param("i", $tableId) || !$stmt->execute() ||  !$stmt->bind_result($namesRaw) || !$stmt->fetch() || !$stmt->close()) {
                        echo "Invalid sheet!";
                        die();
                    }
                    $names = explode(";",$namesRaw);
                    for($i = 0; $i < count($names); $i++) {
                        echo '<option label="' . $names[$i] . '" value="' . $i .'"></option>';
                    }
                ?>
            </datalist>
            <form-input id="name"  tabindex=4 minlength=1 label="Name of item:" type="search-realtime" placeholder="Name of item"></form-input>
            <form-input id="count"  tabindex=5 min=1 minlength=1 label="Count:" type="number" placeholder="Count"></form-input>
            <form-input id="price"  tabindex=6 minlength=1 label="Price per item:" type="number" step=0.01 placeholder="Price per item"></form-input>
            <div class='formButtonBoxHolder'>
                <div class="formJustifyLeft">
                    <a  tabindex=11 href='./sheet.php?id=<?php echo $_GET["id"];?>'><button class="formErrorColor">Exit</button></a>
                    <button class="formErrorColor"  tabindex=9 id="btnClear">Clear</button>
                    <button class="formWarnColor"  tabindex=10 id="btnRestore">Restore from memory</button>
                </div>
                <div class="formJustifyRight">
                    <button class="btnSave formOkColor"  tabindex=7 exit=0>Save</button>
                    <button class="btnSave formInfoColor"  tabindex=8 exit=1>Save and exit</button>
                </div>
            </div>
            <i>Note: When adding new item and clicking Save, new entry will always be added.</i>
            <form-status-message></form-status-message>
        </form-box>
    </body>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
    <script type="module" src="./itemInfo.js"></script>
</html>
