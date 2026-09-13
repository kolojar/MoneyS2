<?php
/** @var \mysqli $conn */
require __DIR__ . "/../assets/sharedFunctions.php";
session_start();

if(!isset($_GET["id"])) {
    http_response_code(400);
    echo "Missing ID parameter.";
    die();
}

//Check access
if(!CheckAccess($_GET["id"])) {
    header("Location: ./login.php?id=" . $_GET["id"]);
    die();
}

//Check if archived
$archivedStmt = $conn->prepare("SELECT archived,name FROM `_tables` WHERE id_tables=?");
$id =ConvertFromBase62($_GET["id"]);
if(!$archivedStmt->bind_param("s", $id) || !$archivedStmt->execute() || !$archivedStmt->bind_result($isArchived, $name) || !$archivedStmt->fetch() || !$archivedStmt->close()) {
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
        <meta name="form-locales-main" content="../formWebScripts/locales/" />
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
            <h1><?php echo $name; ?></h1>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <a href="./itemInfo.php?id=<?php echo rawurlencode($_GET["id"]) ?>"><button class='formOkColor'>Add item</button></a>
            <a href="./manageSheet.php?id=<?php echo rawurlencode($_GET["id"]) ?>"><button class='formInfoColor'>Manage</button></a>
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
    $fieldsStmt = $conn->prepare("SELECT persons FROM `_tables` WHERE id_tables=?");
    $id = ConvertFromBase62($_GET["id"]);
    if (!$fieldsStmt->bind_param("s", $id) || !$fieldsStmt->execute() || !$fieldsStmt->bind_result($namesRaw) || !$fieldsStmt->fetch() ||!$fieldsStmt->close()) {
        http_response_code(400);
        echo "Invalid sheet. A";
        return;
    }
    $names = explode(";",$namesRaw);
    echo "<datalist id='namesEscaped'>";
    for ($i = 0; $i < count($names); $i++) {
        echo "<option value='" . $i . "' label='" . rawurlencode($names[$i])."'></option>";
    }
    echo "</datalist>";

    //Generate header
    echo "<h1>Items</h1>";
    if(!$viewOnly) {
        echo "<i>Click on used amount cell to edit it's value.</i>";
    }
    echo "<div class='tableScrollHolder'>";
    echo "<table class='styledTable styledTableNoWrap'>";
    echo "<tr>";
    echo "<th rowspan=2>Actions</th>";
    echo "<th colspan=7>Item info</th>";
    echo "<th colspan=" . count($names) . ">Used count</th>";
    echo "<th colspan=" . count($names) . ">Used price</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<th class='tableStickySecondRow'>When</th>";
    echo "<th class='tableStickySecondRow'>Where</th>";
    echo "<th class='tableStickySecondRow'>Who</th>";
    echo "<th class='tableStickySecondRow'>Name</th>";
    echo "<th class='tableStickySecondRow'>Count</th>";
    echo "<th class='tableStickySecondRow'>Price per item</th>";
    echo "<th class='tableStickySecondRow'>Linked items</th>";
    foreach ($names as $name) {
        echo "<th class='tableStickySecondRow'>" . $name . "</th>";
    }
    foreach ($names as $name) {
        echo "<th class='tableStickySecondRow'>" . $name . "</th>";
    }
    echo "</tr>";

    //Generate rows
    //Who used -> Who paid
    $whoOwesWho = [];
    //Day -> Where -> Who = amount
    $stats = [];
    $valuesStmt = $conn->prepare("SELECT * FROM " .  $_GET["id"] ." ORDER BY `when`");
    if (!$valuesStmt->execute()) {
        http_response_code(400);
        echo "Invalid sheet. B";
        return;
    }
    foreach ($valuesStmt->get_result() as $value) {
        echo "<tr id='row" . $value["id"] . "'>";
        echo "<td class='formButtonBoxTable'>";
        echo "<button fid='" . $value["id"] . "' class='formWarnColor btnSplitMoney formButtonInline'>Split money</button>";
        echo "<a href='./itemInfo.php?id=" . $_GET["id"] . "&item=" .  $value["id"] . "'><button class='formInfoColor formButtonInline'>Edit</button></a>";
        echo "<button fid='" . $value["id"] . "' class='formErrorColor btnDelete formButtonInline'>Delete</button>";
        echo "</td>";
        echo "<td class='timeFormat'>" . $value["when"] . "</td>";
        echo "<td>" . $value["where"] . "</td>";
        echo "<td>" . $names[$value["who"]] . "</td>";
        echo "<td>" . $value["name"] . "</td>";
        echo "<td>" . $value["cnt"] . "</td>";
        echo "<td>" . $value["price"] . "</td>";
        echo "<td class='formButtonBoxTable'>";
        if ($value["link"] != null) {
            echo "<a href='#row'" . $value["link"] . "><button class='formInfoColor formButtonInline'>Jump to link</button></a>";
        } else {
            echo "<button fid='" . $value["id"] . "' class='formOkColor btnAddLink formButtonInline'>Add link</button>";
        }
        echo "</td>";
        for ($i = 0; $i<count($names);$i++) {
            echo "<td style='text-align:center' max='" . $value["cnt"] . "' name='" . $i . "' fid='" . $value["id"] . "' class='mouseField fieldCount'>" . (isset($value["p".$i]) ? rtrim(rtrim($value["p".$i],"0")?:"0",".")?:"0" : "0") . "</td>";
        }
        $i = 0;
        foreach ($names as $name) {
            $diff = bcmul(isset($value["p".$i]) ? $value["p".$i] : "0", $value["price"]);
            echo "<td style='text-align:center'>" . $diff . "</td>";
            $whoOwesWho[$name][$names[$value["who"]]] = bcadd(isset($whoOwesWho[$name][$names[$value["who"]]]) ? $whoOwesWho[$name][$names[$value["who"]]] : "0", $diff);
            $stats[explode(" ",$value["when"])[0]][$value["where"]][$name] = bcadd($stats[explode(" ",$value["when"])[0]][$value["where"]][$name], $diff);
            //$places[$value["where"]][$names[$value["who"]]] = bcadd($places[$value["where"]][$names[$value["who"]]], bcmul($value["cnt"], $value["price"]));
            $i++;
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    //Generate header
    echo "<h1 id='whoOwesWho'>Who owes who</h1>";
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
    foreach($names as $idRow => $row) {
        echo "<tr id='rowOwesTo" . $idRow . "'>";
        echo "<th class='form-horizontal-header'>" . $row . "</th>";
        foreach($names as $idCol => $col) {
            $val = (isset($whoOwesWho[$col][$row]) ? $whoOwesWho[$col][$row] : "0.000");
            echo  "<td target='OwesTo' who-used='" . $idCol . "' who-paid='" . $idRow . "'  style='text-align:center' class='" . ($val == "0" ? "formOkColor" : "mouseField cellPay") . "'>" . $val . "</td>";
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
    foreach($names as $idRow => $row) {
        echo "<tr id='rowPaysTo" . $idRow . "'>";
        echo "<th class='form-horizontal-header'>" . $row . "</th>";
        foreach($names as $idCol => $col) {
            $rowOwesColVal = (isset($whoOwesWho[$row][$col]) ? $whoOwesWho[$row][$col] : "0.000");
            $colOwesRowVal = (isset($whoOwesWho[$col][$row]) ? $whoOwesWho[$col][$row] : "0.000");
            $diff = bcsub($colOwesRowVal,$rowOwesColVal);

            if(bccomp($diff,"0") == -1) {
                $diff = "0.000";
            }
            echo  "<td  target='PaysTo' who-used='" . $idCol . "' who-paid='" . $idRow . "' style='text-align:center' class='" . ($diff == "0" ? "formOkColor" : "mouseField cellPay") . "'>" . $diff . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    //Generate days
    echo "<h1>Info per day</h1>";
    //Place -> Who -> Price
    $places = [];
    foreach($stats as $day => $dayInfo) {
        echo "<h2 class='dateFormat'>" . $day . "</h2>";
        echo "<div class='tableScrollHolder'>";
        echo "<table class='styledTable styledTableNoWrap'>";
        echo "<tr>";
        echo "<th class='form-topleft-header'>Place</th>";
        foreach($names as $name) {
            echo "<th>" . $name ."</th>";
        }
        echo "<th>Total</th>";
        echo "</tr>";
        $nameSum = [];
        foreach($dayInfo as $where => $placeInfo) {
            if($where == "BANK") {continue;}
            echo "<tr>";
            echo "<th class='form-horizontal-header'>" . $where . "</th>";
            $placeSum = "0";
            foreach($names as $name) {
                echo "<td style='text-align:center'>" .$placeInfo[$name] . "</td>";
                $placeSum = bcadd($placeSum,$placeInfo[$name]);
                $nameSum[$name] = bcadd($nameSum[$name],$placeInfo[$name]);
                $places[$where][$name] = bcadd($places[$where][$name],$placeInfo[$name]);
            }
            echo "<td style='text-align:center'>" . $placeSum . "</td>";
            echo "</tr>";
        }
        echo "<tr>";
        echo "<th class='form-horizontal-header'>Sum:</th>";
        $total = "0";
        foreach($names as $name) {
            echo "<td style='text-align:center'>" . $nameSum[$name] . "</td>";
            $total = bcadd($total, $nameSum[$name]);
        }
        echo "<td style='text-align:center'>" . $total . "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</div>";
    }

    //Generate places summary
    echo "<h1>Info per place</h1>";
    echo "<div class='tableScrollHolder'>";
    echo "<table class='styledTable styledTableNoWrap'>";
    echo "<tr>";
    echo "<th class='form-topleft-header'>Place</th>";
    foreach($names as $name) {
        echo "<th>" . $name ."</th>";
    }
    echo "<th>Total</th>";
    echo "</tr>";
    echo "<tr>";
    $nameSum = [];
    foreach($places as $where => $placeInfo) {
        if($where == "BANK") {continue;}
        echo "<th class='form-horizontal-header'>" . $where . "</th>";
        $rowSum = "0";
        foreach($names as $name) {
            echo "<td style='text-align:center'>" . (isset($placeInfo[$name]) ? $placeInfo[$name] : "0.000") . "</td>";
            $rowSum = bcadd($rowSum, $placeInfo[$name]);
            $nameSum[$name] = bcadd($nameSum[$name], $placeInfo[$name]);
        }
        echo "<td style='text-align:center'>" . $rowSum. "</td>";
        echo "</tr>";
    }
    echo "<th class='form-horizontal-header'>Sum:</th>";
    $total = "0";
    foreach($names as $name) {
        echo "<td style='text-align:center'>" . (isset($nameSum[$name])? $nameSum[$name] :"0.000") . "</td>";
        $total = bcadd($total, $nameSum[$name]);
    }
    echo "<td style='text-align:center'>" . $total . "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";
}
?>
