<?php

/** @var \mysqli $conn */
require __DIR__ . "/../assets/config.php";
session_start();

//Handle POST
if (isset($_POST["password"])) {
    $passwordResult = $conn->query("SELECT `password` FROM `_tables` WHERE id_tables = " . ConvertFromBase62($_POST["id"]));
    $password = $passwordResult->fetch_assoc()["password"];
    if (password_verify($_POST["password"],  $password)) {
        http_response_code(200);
        $_SESSION["loggedIn"] = $_POST["id"];
        echo "ok";
        die();
    }
    http_response_code(403);
    echo "Invalid password";
    die();
}

//Handle GET
if (!isset($_GET["id"])) {
    echo "Missing ID.";
    http_response_code(400);
    die();
}

//Check access
if(CheckAccess($_GET["id"])) {
    header("Location: ./sheet.php?id=" . $_GET["id"]);
    die();
}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Login to Sheet</title>
        <link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
        <link rel="stylesheet" href="../assets/style.css" />
        <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
    </head>
    <body class="formBackground" form-box-holder>
        <form-box>
            <p class="formHeader">Login to Sheet</p>
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
        const params = new URLSearchParams(window.location.search);
        const dialogManager = new FormDialogManager();
        document.getElementById("send").addEventListener("click", async () => {
            SetWaitStatusForms("Sending data to server, please wait...");
            const data = new FormData();
            data.append("id",params.get("id"))
            data.append("password", document.getElementById("password").value);

            const [ok, res] = await SendPOSTDataToServerAsync("./login.php", data);

            if (ok) {
                if (res == "ok") {
                    window.location.href = "./sheet.php?id=<?php echo $_GET["id"]; ?>";
                } else {
                    SendToast("Server responce", res, "error");
                    setTimeout(async () => {
                        await dialogManager.ShowAlertAsync("Login to Sheet", "Comunication with server failed, please try it again later.");
                        window.location.reload();
                    }, 1000);
                }
            } else {
                SendToast("Server responce", res, "error");
                setTimeout(async () => {
                    await dialogManager.ShowAlertAsync("Login to Sheet", "Invalid password, please try it again.");
                    window.location.reload();
                }, 1000);
            }
        });
    </script>
</html>
