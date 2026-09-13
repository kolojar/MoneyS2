<?php
    require __DIR__ . "/../assets/config.php";
    session_start();

    //Handle timeouts
    if(!isset($_SESSION['loginTimeoutLevel'])){
        $_SESSION['loginTimeoutLevel'] = 1;
    }
    if(!isset($_SESSION['loginTries'])){
        $_SESSION['loginTries'] = 3;
    }
    if($_SESSION["loginTries"] ==0 && time() - $_SESSION['loginTimeout'] > 60 * $_SESSION['loginTimeoutLevel']) {
        $_SESSION["loginTries"] = 3;
        $_SESSION['loginTimeoutLevel'] =  $_SESSION['loginTimeoutLevel']  + 1;
    }
    if($_SESSION["loginTries"] == 0) {
        http_response_code(403);
        echo "Wait for timeout (" . $_SESSION['loginTimeoutLevel'] . " minute/s).";
        die();
    }

    //Handle POST
    if (isset($_POST["password"])) {
        if($_POST["password"] == $_SERVER["adminPassword"]) {
            http_response_code(200);
            $_SESSION["isAdmin"] = true;
            $_SESSION['loginTries'] = 3;
            $_SESSION['loginTimeoutLevel'] = 1;
            echo "ok";
            die();
        }

        //Handle timeout
        $_SESSION["loginTries"] = $_SESSION["loginTries"] - 1;
        if($_SESSION["loginTries"] == 0) {
            $_SESSION['loginTimeout'] = time();
        }
        $_SESSION["loggedIn"] = "";
        http_response_code(403);
        echo "Invalid password";
        die();
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Login to Admin</title>
        <link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
        <link rel="stylesheet" href="../assets/style.css" />
        <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
    </head>
    <body class="formBackground" form-box-holder>
        <form-box>
            <p class="formHeader">Login to Admin</p>
            <form-input type="password" tabindex="1" label="Password" id="password" placeholder="Your password"></form-input>
            <div class="formButtonBoxHolder formCenter">
                <button id="send" tabindex="2" class="formButton formOkColor">Login</button>
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
        const dialogManager = new FormDialogManager();
        document.getElementById("send").addEventListener("click", async () => {
            SetWaitStatusForms("Sending data to server, please wait...");
            const data = new FormData();
            data.append("password", document.getElementById("password").value);

            const [ok, res] = await SendPOSTDataToServerAsync("./login.php", data);

            if (ok) {
                if (res == "ok") {
                    window.location.href = "./admin.php";
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
