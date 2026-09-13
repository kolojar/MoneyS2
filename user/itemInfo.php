<?php
require __DIR__ . "/../assets/sharedFunctions.php";
/** @var \mysqli $conn */
session_start();

//Handle SEARCH
if (isset($_POST["search"])) {
    //Check for valid value
    if (!isset($_POST["value"])) {
        http_response_code(400);
        echo "Search needs value!";
        die();
    }

    //Check for valid sheet
    if (!isset($_POST["id"])) {
        http_response_code(400);
        echo "Search needs sheet id!";
        die();
    }

    //Check for valid search
    if ($_POST["search"] != "where" && $_POST["search"] != "name") {
        http_response_code(400);
        echo "Invalid search table!";
        die();
    }

    //Run SQL
    $search = $_POST["search"];
    $sheet = $_POST["id"];
    $value = $_POST["value"] . "*";
    $value2 = $_POST["value"] . "%";
    //$value2 = "%" . $_POST["value"] . "%";
    if ($value == "*") {
        $stmt = $conn->prepare("SELECT t.`$search`, COUNT(t.`$search`) AS c FROM `$sheet` t GROUP BY t.`$search` ORDER BY c DESC LIMIT 20");
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Invalid MySQL error!";
            die();
        }
    } else {
        $stmt = $conn->prepare("SELECT t.`$search`, COUNT(t.`$search`) AS c FROM `$sheet` t WHERE MATCH(t.`$search`) AGAINST(? IN BOOLEAN MODE) OR t.`$search` LIKE ? GROUP BY t.`$search` ORDER BY c DESC LIMIT 20");
        if (!$stmt->bind_param("ss", $value, $value2) || !$stmt->execute()) {
            http_response_code(400);
            echo "Invalid MySQL error!";
            die();
        }
    }
    //Convert to JSON
    $result = $stmt->get_result();
    $values = [];
    while ($row = $result->fetch_row()) {
        $values[] = $row[0];
    }
    $stmt->close();
    $result->close();
    header("Content-Type: application/json");
    echo json_encode($values);
    die();
}

//Handle POST
if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "delete":
            //Validate
            if (!isset($_POST["id"]) || !isset($_POST["item"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Run SQL
            $item = $_POST["item"];
            $stmt = $conn->prepare("DELETE FROM `" . $_POST["id"] . "` WHERE id=?");
            if (!$stmt->bind_param("i", $item) || !$stmt->execute() || $stmt->affected_rows == 0) {
                http_response_code(400);
                echo "Error deleting item.";
                exit();
            }
            http_response_code(201);
            echo "ok";
            exit();
        case "insert":
            //Validate
            if (!isset($_POST["when"]) || !isset($_POST["where"]) || !isset($_POST["who"]) || !isset($_POST["name"]) || !isset($_POST["count"]) || !isset($_POST["price"]) || !isset($_POST["id"])) {
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
            if (!$stmt->bind_param("ssssss", $when, $where, $who, $name, $cnt, $price) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Error inserting sheet.";
                exit();
            }
            UpdateActivity($_POST["id"]);
            http_response_code(201);
            echo "ok";
            exit();
        case "update":
            //Validate
            if (!isset($_POST["when"]) || !isset($_POST["where"]) || !isset($_POST["who"]) || !isset($_POST["name"]) || !isset($_POST["count"]) || !isset($_POST["price"]) || !isset($_POST["id"]) || !isset($_POST["item"])) {
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
            if (!$stmt->bind_param("ssssssi", $when, $where, $who, $name, $cnt, $price, $item) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Error updating sheet.";
                exit();
            }
            UpdateActivity($_POST["id"]);
            http_response_code(201);
            echo "ok";
            exit();
        case "splitMoney":
            //Validate
            if (!isset($_POST["id"]) || !isset($_POST["item"]) || !isset($_POST["users"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Get total count
            $stmt = $conn->prepare("SELECT `cnt` FROM `" . $_POST["id"] . "` WHERE id = ?");
            $item = $_POST["item"];
            if (!$stmt->bind_param("i", $item) || !$stmt->execute() || !$stmt->bind_result($cnt) || !$stmt->fetch() || !$stmt->close()) {
                http_response_code(400);
                echo "Error splitting money.";
                die();
            }

            //Get users
            $users = json_decode($_POST["users"]);
            $ratio = bcdiv($cnt, count($users));

            //Create query
            $query = "UPDATE `" . $_POST["id"] . "` SET ";
            foreach ($users as $user) {
                $query = $query . "`p" . $user . "`=" . $ratio . ", ";
            }
            $query .= "WHERE id=?";
            $query = str_replace(", WHERE", " WHERE", $query);
            print $query;

            //Run SQL
            $stmt = $conn->prepare($query);
            if (!$stmt->bind_param("i", $item) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Error splitting money.";
                exit();
            }
            UpdateActivity($_POST["id"]);
            http_response_code(201);
            echo "ok";
            exit();
        case "setCount":
            //Validate
            if (!isset($_POST["id"]) || !isset($_POST["item"]) || !isset($_POST["user"]) || !isset($_POST["amount"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Get total count
            $stmt = $conn->prepare("SELECT * FROM `" . $_POST["id"] . "` WHERE id = ?");
            $item = $_POST["item"];
            if (!$stmt->bind_param("i", $item) || !$stmt->execute()) {
                http_response_code(400);
                echo "Error setting amount.";
                die();
            }
            $counts = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            //Sum used count
            $notUsed = $counts["cnt"];
            for ($i = 0; $i < count($counts); $i++) {
                $notUsed = bcsub($notUsed, $counts["p" . $i]);
            }

            //Calculate delta of user
            $amount = $_POST["amount"];
            $deltaUser = bcsub($amount, $counts["p" . $_POST["user"]]);

            //Validate
            if (bccomp($notUsed, $deltaUser) == -1) {
                http_response_code(400);
                echo "Maximum used amount is: " . $notUsed;
                die();
            }

            //Run SQL
            $stmt = $conn->prepare("UPDATE `" . $_POST["id"] . "` SET `p" . $_POST["user"] . "`=? WHERE id=?");
            if (!$stmt->bind_param("si", $amount, $item) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Error setting used count.";
                exit();
            }
            UpdateActivity($_POST["id"]);
            http_response_code(201);
            echo "ok";
            exit();
        case "pay":
            //Validate
            if (!isset($_POST["id"]) || !isset($_POST["from"]) || !isset($_POST["to"]) || !isset($_POST["amount"]) || !isset($_POST["name"])) {
                http_response_code(400);
                echo "Missing parameters!";
                die();
            }

            //Run SQL
            $stmt = $conn->prepare("INSERT INTO `" . $_POST["id"] . "`(`where`, `who`, `name`, `cnt`, `price`,`p" . $_POST["to"] . "`) VALUES ('BANK',?,?,?,1,?)");
            $who = $_POST["from"];
            $name = $_POST["name"];
            $cnt = $_POST["amount"];
            if (!$stmt->bind_param("ssss", $who, $name, $cnt, $cnt) || !$stmt->execute() || !$stmt->close()) {
                http_response_code(400);
                echo "Error saving payment.";
                exit();
            }
            UpdateActivity($_POST["id"]);
            http_response_code(201);
            echo "ok";
            exit();
    }
    http_response_code(400);
    echo "Missing action parameter.";
    die();
}

//Handle missing ID
if (!isset($_GET["id"])) {
    http_response_code(400);
    echo "Missing ID parameter.";
    die();
}

//Check access
if (!CheckAccess($_GET["id"])) {
    header("Location: ./login.php?id=" . $_GET["id"]);
    die();
}

//Get name
$nameStmt = $conn->prepare("SELECT name, persons FROM `_tables` WHERE id_tables=?");
$id = ConvertFromBase62($_GET["id"]);
if (!$nameStmt->bind_param("s", $id) || !$nameStmt->execute() || !$nameStmt->bind_result($name, $persons) || !$nameStmt->fetch() || !$nameStmt->close()) {
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
        <form-box class='formBoxScrollContent'>
            <?php if (isset($_GET["item"])) {
                //Get info
                $stmt = $conn->prepare("SELECT * FROM `" . $_GET["id"] . "` WHERE id=?");
                $item = $_GET["item"];
                if (!$stmt->bind_param("i", $item) || !$stmt->execute()) {
                    http_response_code(400);
                    echo "Invalid item id.";
                    die();
                }
                $info = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } ?>

            <p class="formHeader"><?php echo isset($_GET["item"]) ? "Manage" : "Add"; ?> item</p>
            <form-input id="when" tabindex=1 minlength=1 label="When:" type="datetime-local" placeholder="When" valueTime="<?php echo $info["when"]; ?>"></form-input>
            <form-input id="where"  tabindex=2 minlength=1 label="Where:" type="search-realtime" placeholder="Where" value="<?php echo $info["where"]; ?>"></form-input>
            <datalist id="places">
                <?php
                $tableId = ConvertFromBase62($_GET["id"]);
                $result = $conn->query("SELECT `where` FROM " . $_GET["id"] . " GROUP BY `where`");
                while ($row = $result->fetch_assoc()) {
                    echo '<option label="' . $row["where"] . '" value="' . $row["where"] . '"></option>';
                }
                $result->close();
                ?>
            </datalist>
            <form-input id="who"  tabindex=3 label="Who:" type="select" placeholder="Who" list="userNames" value="<?php echo $info["who"]; ?>"></form-input>
            <datalist id="userNames">
                <?php
                $stmt = $conn->prepare("SELECT persons FROM _tables WHERE id_tables = ?");

                if (!$stmt->bind_param("i", $tableId) || !$stmt->execute() || !$stmt->bind_result($namesRaw) || !$stmt->fetch() || !$stmt->close()) {
                    echo "Invalid sheet!";
                    die();
                }
                $names = explode(";", $namesRaw);
                for ($i = 0; $i < count($names); $i++) {
                    echo '<option label="' . $names[$i] . '" value="' . $i . '"></option>';
                }
                ?>
            </datalist>
            <form-input id="name"  tabindex=4 minlength=1 label="Name of item:" type="search-realtime" placeholder="Name of item" value="<?php echo $info["name"]; ?>"></form-input>
            <form-input id="count"  tabindex=5 min=1 minlength=1 label="Count:" type="number" placeholder="Count" value="<?php echo $info["cnt"]; ?>"></form-input>
            <form-input id="price"  tabindex=6 minlength=1 label="Price per item:" type="number" step=0.001 placeholder="Price per item" value="<?php echo $info["price"]; ?>"></form-input>
            <div class='formButtonBoxHolder'>
                <div class="formJustifyLeft">
                    <a  tabindex=11 href='./sheet.php?id=<?php echo $_GET["id"]; echo isset($_GET["item"]) ? ("#row" . $_GET["item"]) : "" ?>'><button class="formErrorColor">Exit</button></a>
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
