<?php
/** @var \mysqli $conn */
require __DIR__ . "/../assets/config.php";
session_start();

//Handle POST
if (isset($_POST["name"], $_POST["password"], $_POST["people"])) {
    $query =
        "CREATE TABLE `" .
        $_POST["name"] .
        "` (`id` INT AUTO_INCREMENT PRIMARY KEY, `when` DATETIME DEFAULT CURRENT_TIMESTAMP, `where` VARCHAR(255) NOT NULL, `who` TINYINT NOT NULL, `name` VARCHAR(255) NOT NULL, `cnt` TINYINT NOT NULL, `price` FLOAT NOT NULL, `link` INT";
    
    $people = explode(";", $_POST["people"]);
        
    foreach ($people as $person) {
        $query .= ", `" . $person . "` FLOAT NOT NULL";
    }
    
    $query .= ") ENGINE=INNODB;";
    //print($query);
    if (!$conn->query($query)) {
        echo "nah";
        die();
    } else {
        $conn->query("INSERT INTO `tables` (`name`, `password`) VALUES ('".$_POST["name"]."', '".$_POST["password"]."');");
        http_response_code(200);
        echo "success";
        die();
    }
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
            <form-input type="text" tabindex="1" label="People" id="people" placeholder="john doe;jane doe;..." minlength=1></form-input>            
            <div class="formButtonBoxHolder formCenter">
                <button id="send" tabindex="3" class="formButton formOkColor">Create</button>
            </div>
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
            SetWaitStatusForms("Sending data to server, please wait...");
            const data = new FormData();
            data.append("name", document.getElementById("name").value.replaceAll(" ", "_"));
            data.append("password", document.getElementById("password").value);

            data.append("people", document.getElementById("people").value.replaceAll(" ", "_"))

            const [ok, res] = await SendPOSTDataToServerAsync("./newSheet.php", data);

            if (ok) {
                if (res == "ok") {
                    window.location.href = "./sheet.php?name="+ encodeURIComponent(document.getElementById("password").value);
                } else {
                    SendToast("Server responce", res, "error");
                    setTimeout(async () => {
                        await dialogManager.ShowAlertAsync("Login to Admin", "Comunication with server failed, please try it again later.");
                        window.location.reload();
                    }, 1000);
                }
            } else {
                SendToast("Server responce", res, "error");
                setTimeout(async () => {
                    await dialogManager.ShowAlertAsync("Login to Admin", "Invalid password, please try it again.");
                    window.location.reload();
                }, 1000);
            }
        });
    </script>
</html>
