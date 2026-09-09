<?php
/** @var \mysqli $conn */
require __DIR__ . "/../assets/config.php";
session_start();
if (isset($_GET["viewOnly"])) {
    generateTables(false, $conn);
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
    </head>
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
            <a href="./manage.php?id=<?php echo $_GET["id"]; ?>" ><button class='formInfoColor'>Manage</button></a>
        </div>
        </div>
    </header>
    <main>
        <?php generateTables(false, $conn); ?>
    </main>
    <footer></footer>
</body>
</html>

<?php function generateTables(bool $unfilledOnly, mysqli $conn)
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
    echo "<table class='styledTable'>";
    echo "<tr>";
    echo "<th colspan=7>Item info</th>";
    echo "<th colspan=" . count($names) . ">Used count</th>";
    echo "<th colspan=" . count($names) . ">Used price</th>";
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
        echo "</tr>";
    }
    echo "</table>";

    //Generate header
    echo "<h1>Who owes who</h1>";
    echo "<table class='styledTable'>";
    echo "<tr>";
    echo "<th>→ owes to ↓</th>";
    foreach ($names as $name) {
        echo "<th>" . $name . "</th>";
    }
    echo "</tr>";
    echo "</table>";

    //Generate header
    echo "<h1>Who owes who normalized</h1>";
}
?>
