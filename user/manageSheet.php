<?php
require __DIR__ . "/../assets/config.php";
session_start();

if(!isset($_GET["id"])) {
    http_response_code(400);
    echo "Missing ID parameter.";
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
            <p>Name: <?php echo (rawurldecode($_GET["id"])) ?></p>
            <div class='formButtonBox'>
                <a href="./sheet.php?id=<?php echo(rawurlencode($_GET["id"])) ?>"><button class='formInfoColor'>Back to sheet</button></a>
                <button class='formWarnColor' id='changeNameButton'>Change name</button><br>
                <button class='formWarnColor' id='changePasswordButton'>Change password</button><br>
                <button class='formWarnColor' id='changePeopleButton'>Change people</button><br>
                <button class='formErrorColor' id='archiveButton'>Archive</button>
            </div>
            <form-status-message></form-status-message>
        </form-box>
    </body>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
    <script type="module" src="./manageSheet.js"></script>
</html>
