<?php
require __DIR__ . "/../assets/config.php";
/** @var \mysqli $conn */
session_start();

//Handle POST
if(isset($_POST["action"])) {
    switch($_POST["action"]) {
        case "changeName": {
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
            //Change name
            $stmt = $conn->prepare("UPDATE `_tables` SET `password`=? WHERE id_tables=?");
            $id = ConvertFromBase62($_POST["id"]);
            $password = password_hash($_POST["name"], PASSWORD_DEFAULT);
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

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Manage sheet</title>
        <link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
        <link rel="stylesheet" href="../assets/style.css" />
        <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
    </head>
    <body class="formBackground" form-box-holder>
        <form-box>
            <p class="formHeader">Manage sheet</p>
            <p>Name: <?php echo $name ?></p>
            <div class='formButtonBox'>
                <a href="./sheet.php?id=<?php echo(rawurlencode($_GET["id"])) ?>"><button class='formInfoColor'>Back to sheet</button></a>
                <button class='formWarnColor' id='changeNameButton' value="<?php echo (rawurlencode($name)) ?>">Change name</button><br>
                <button class='formWarnColor' id='changePasswordButton'>Change password</button><br>
                <button class='formWarnColor' id='changePeopleButton' disabled value="<?php echo (rawurlencode($persons)) ?>">Change people</button><br>
                <button class='formErrorColor' id='archiveButton'>Archive</button>
            </div>
            <form-status-message></form-status-message>
        </form-box>
    </body>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
    <script type="module" src="./manageSheet.js"></script>
</html>
