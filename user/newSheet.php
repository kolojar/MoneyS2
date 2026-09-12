<?php
/** @var \mysqli $conn */
require __DIR__ . "/../assets/config.php";
session_start();

//Handle POST
if (isset($_POST["name"], $_POST["password"], $_POST["people"])) {
    //Create new table evidence
    $name = $_POST["name"];
    $passwordHash = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $people = $_POST["people"];
    $stmt = $conn->prepare("INSERT INTO `_tables` (`name`, `password`,`persons`) VALUES (?,?,?);");
    if (!$stmt->bind_param("sss", $name, $passwordHash, $people) || !$stmt->execute()) {
        http_response_code(500);
        echo "nah";
        die();
    }
    //print($stmt->insert_id);
    $id = ConvertToBase62($stmt->insert_id);
    $stmt->close();

    //Create table
    $query = "CREATE TABLE `" . $id . "` (`id` INT AUTO_INCREMENT PRIMARY KEY, `when` DATETIME DEFAULT CURRENT_TIMESTAMP, `where` VARCHAR(255) NOT NULL, `who` TINYINT NOT NULL, `name` VARCHAR(255) NOT NULL, `cnt` TINYINT NOT NULL, `price` DECIMAL(18,3) NOT NULL, `link` INT";
    for ($i = 0; $i < count(explode(";", $people)); $i++) {
        $query .= ", `p" . $i . "` DECIMAL(9,3) NOT NULL DEFAULT 0";
    }

    $query .= ") ENGINE=INNODB;";
    //print($query);
    if (!$conn->query($query)) {
        http_response_code(500);
        echo "nah";
        die();
    }

    //Add FULLTEXT indexes
    if(!$conn->query("ALTER TABLE `" . $id . "` ADD FULLTEXT INDEX fulltext_where (`where`);")) {
        http_response_code(500);
        echo "nah";
        die();
    }
    if(!$conn->query("ALTER TABLE `" . $id . "` ADD FULLTEXT INDEX fulltext_name (`name`);")) {
        http_response_code(500);
        echo "nah";
        die();
    }

    //Done
    http_response_code(200);
    echo $id;
    die();
}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Create new sheet</title>
        <link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
        <link rel="stylesheet" href="../assets/style.css" />
        <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
    </head>
    <body class="formBackground" form-box-holder>
        <form-box>
            <p class="formHeader">Create new sheet</p>
            <form-input type="text" tabindex="1" label="Name" id="name" placeholder="Sheet name" minlength=1></form-input>
            <form-input type="password" tabindex="1" label="Password" id="password" placeholder="Password (optional)"></form-input>
            <form-input type="textarea" tabindex="1" label="People" id="people" placeholder="john doe;jane doe;..." minlength=1></form-input>
            <div class="formButtonBoxHolder formCenter">
                <button id="send" tabindex="3" class="formButton formOkColor">Create</button>
            </div>
            <i>Note: Sepatate people with ;</i>
            <form-status-message></form-status-message>
        </form-box>
    </body>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
    <script type="module">
        import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
        import { SendToast, SetWaitStatusForms } from "../formWebScripts/js/formScript.js";
        import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
        import { GlobalLanguageManager } from "../formWebScripts/js/formScript.js";

        //GlobalLanguageManager.ChangeLanguage("en");
        let nameCnt = 2;
        const dialogManager = new FormDialogManager();
        document.getElementById("send").addEventListener("click", async () => {
            //Validate
            const [_1, ok1, _4] = await document.getElementById("name").validate();
            const [_2, ok2, _5] = await document.getElementById("password").validate();
            const [_3, ok3, _6] = await document.getElementById("people").validate();
            if(!ok1 || !ok2 || !ok3) {
              SendToast("Create new sheet","Input contains invalid data!", "error");
              return
            }

            //Send to server
            SetWaitStatusForms("Sending data to server, please wait...");
            const data = new FormData();
            data.append("name", document.getElementById("name").value);
            data.append("password", document.getElementById("password").value);
            data.append("people", document.getElementById("people").value)

            const [ok, res] = await SendPOSTDataToServerAsync("./newSheet.php", data);

            if (ok) {
                window.location.href = "./sheet.php?id="+ res;
            } else {
                SendToast("Server responce", res, "error");
                setTimeout(async () => {
                    await dialogManager.ShowAlertAsync("Create new sheet", "Failed creating sheet, please try it again.");
                    //window.location.reload();
                }, 1000);
            }
        });
    </script>
</html>
