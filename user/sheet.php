<?php
/** @var \mysqli $conn */
require __DIR__ . "/../assets/config.php";
session_start();

//Check if archived
$archivedStmt = $conn->prepare("SELECT archived FROM `tables` WHERE name=?");
$id = $_GET["id"];
if(!$archivedStmt->bind_param("s", $id) || !$archivedStmt->execute() || !$archivedStmt->bind_result($isArchived) || !$archivedStmt->fetch() || !$archivedStmt->close()) {
    http_response_code(400);
    echo "Invalid sheet.";
    die();
}
if($isArchived == 1) {
    echo "TODO: ARCHIVED!";
    die();
}

//Check if render pure HTML
if (isset($_GET["viewOnly"])) {
    generateTables(false,true, $conn);
    die();
}

function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sheet view</title>
        <link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
        <link rel="stylesheet" href="../assets/style.css" />
        <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
    </head>
    <style>
    th {
        position: sticky;
          top: 0;
          z-index: 1;
    }
    .form-horizontal-header {
        position: sticky;
          left: 0;
          z-index: 1;
    }
    .mouseField {
        cursor: pointer;
    }
    </style>
<body>
    <?php if (!isset($_GET["id"]) || strlen($_GET["id"]) == 0) {
        http_response_code(400);
        echo "<h1>Invalid id.</h1>";
        die();
    } ?>
    <header>
        <div class='formButtonBoxHolder'>
        <div class='formButtonBox'>
            <h1><?php echo $_GET["id"]; ?></h1>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <button class='formOkColor'>Add item</button>
            <a href="./manage.php?id=<?php echo $_GET["id"]; ?>" ><button class='formInfoColor'>Manage</button></a>
        </div>
        </div>
    </header>
    <main>
        <?php generateTables(false,false, $conn); ?>
    </main>
    <footer></footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./sheet.js"></script>
</html>

<?php function generateTables(bool $unfilledOnly, bool $viewOnly, mysqli $conn)
{
    //Get names
    $names = [];
    $fieldsStmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = DATABASE() ORDER BY ORDINAL_POSITION LIMIT 18446744073709551615 OFFSET 8;");
    $id = $_GET["id"];
    if (!$fieldsStmt->bind_param("s", $id) || !$fieldsStmt->execute()) {
        http_response_code(400);
        echo "Invalid sheet.";
        return;
    }
    foreach ($fieldsStmt->get_result() as $field) {
        $names[] = $field["COLUMN_NAME"];
    }

    //Generate header
    echo "<h1>Items</h1>";
    if(!$viewOnly) {
        echo "<i>Click on cell to edit it's value.</i>";
    }
    echo "<div class='tableScrollHolder'>";
    echo "<table class='styledTable styledTableNoWrap'>";
    echo "<tr>";
    echo "<th colspan=7>Item info</th>";
    echo "<th colspan=" . count($names) . ">Used count</th>";
    echo "<th colspan=" . count($names) . ">Used price</th>";
    echo "<th rowspan=2>Actions</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>When</th>";
    echo "<th>Where</th>";
    echo "<th>Who</th>";
    echo "<th>Name</th>";
    echo "<th>Count</th>";
    echo "<th>Price per item</th>";
    echo "<th>Linked items</th>";
    foreach ($names as $name) {
        echo "<th>" . $name . "</th>";
    }
    foreach ($names as $name) {
        echo "<th>" . $name . "</th>";
    }
    echo "</tr>";

    //Generate rows
    //Who used -> Who paid
    $whoOwesWho = [];
    $valuesStmt = $conn->prepare("SELECT * FROM " . $_GET["id"]);
    if (!$valuesStmt->execute()) {
        http_response_code(400);
        echo "Invalid sheet.";
        return;
    }
    foreach ($valuesStmt->get_result() as $value) {
        echo "<tr id='row'" . $value["id"] . ">";
        echo "<td fid='" . $value["id"] . "' class='timeFormat mouseField fieldWhen'>" . $value["when"] . "</td>";
        echo "<td fid='" . $value["id"] . "' class='mouseField fieldWhere'>" . $value["where"] . "</td>";
        echo "<td fid='" . $value["id"] . "' class='mouseField fieldWho'>" . $names[$value["who"]] . "</td>";
        echo "<td fid='" . $value["id"] . "' class='mouseField fieldName'>" . $value["name"] . "</td>";
        echo "<td fid='" . $value["id"] . "' class='mouseField fieldCount'>" . $value["cnt"] . "</td>";
        echo "<td fid='" . $value["id"] . "' class='mouseField fieldPrice'>" . $value["price"] . "</td>";
        echo "<td class='formButtonBoxTable'>";
        if ($value["link"] != null) {
            echo "<a href='#row'" . $value["link"] . "><button class='formInfoColor formButtonInline'>Jump to link</button></a>";
        } else {
            echo "<button fid='" . $value["id"] . "' class='formOkColor btnAddLink formButtonInline'>Add link</button>";
        }
        echo "</td>";
        foreach ($names as $name) {
            echo "<td name='" . $name . "' fid='" . $value["id"] . "' class='mouseField fieldCount'>" . (isset($value[$name]) ? $value[$name] : "0") . "</td>";
        }
        foreach ($names as $name) {
            $diff = bcmul(isset($value[$name]) ? $value[$name] : "0", $value["price"]);
            echo "<td>" . $diff . "</td>";
            $whoOwesWho[$name][$names[$value["who"]]] = bcadd(isset($whoOwesWho[$name][$names[$value["who"]]]) ? $whoOwesWho[$name][$names[$value["who"]]] : "0", $diff);
        }
        echo "<td class='formButtonBoxTable'>";
        echo "<button fid='" . $value["id"] . "' class='formWarnColor btnSplitMoney formButtonInline'>Split money</button>";
        echo "<button fid='" . $value["id"] . "' class='formErrorColor btnDelete formButtonInline'>Delete</button>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    //Generate header
    echo "<h1>Who owes who</h1>";
    if(!$viewOnly) {
        echo "<i>Click on cell to make payment.</i>";
    }
    echo "<div class='tableScrollHolder'>";
    echo "<table class='styledTable styledTableNoWrap'>";
    echo "<tr>";
    echo "<th class='form-topleft-header'>→ owes to ↓</th>";
    foreach ($names as $name) {
        echo "<th>" . $name . "</th>";
    }
    echo "</tr>";
    foreach($names as $row) {
        echo "<tr>";
        echo "<th class='form-horizontal-header'>" . $row . "</th>";
        foreach($names as $col) {
            $val = (isset($whoOwesWho[$col][$row]) ? $whoOwesWho[$col][$row] : "0");
            echo  "<td who-used='" . encodeURIComponent($col) . "' who-paid='" . encodeURIComponent($row) . "'  style='text-align:center' class='" . ($val == "0" ? "formOkColor" : "mouseField cellPay") . "'>" . $val . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    //Generate header
    echo "<h1>Who pays who</h1>";
    if(!$viewOnly) {
        echo "<i>Click on cell to make payment.</i>";
    }
    echo "<div class='tableScrollHolder'>";
    echo "<table class='styledTable styledTableNoWrap'>";
    echo "<tr>";
    echo "<th class='form-topleft-header'>→ pays to ↓</th>";
    foreach ($names as $name) {
        echo "<th>" . $name . "</th>";
    }
    echo "</tr>";
    foreach($names as $row) {
        echo "<tr>";
        echo "<th class='form-horizontal-header'>" . $row . "</th>";
        foreach($names as $col) {
            $rowOwesColVal = (isset($whoOwesWho[$row][$col]) ? $whoOwesWho[$row][$col] : "0");
            $colOwesRowVal = (isset($whoOwesWho[$col][$row]) ? $whoOwesWho[$col][$row] : "0");
            $diff = bcsub($colOwesRowVal,$rowOwesColVal);
            if(bccomp($diff,"0") == -1) {
                $diff = "0";
            }
            echo  "<td who-used='" . encodeURIComponent($col) . "' who-paid='" . encodeURIComponent($row) . "' style='text-align:center' class='" . ($diff == "0" ? "formOkColor" : "mouseField cellPay") . "'>" . $diff . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}
?>
