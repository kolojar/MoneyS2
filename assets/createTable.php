<!-- post [name, p0, p1, ..., pn] -->
<?php
/** @var mysqli $conn */
require __DIR__."/config.php";

if (!isset($_POST["name"], $_POST["p0"])) {
    die();
}

$query =
    "CREATE TABLE `" .
    $_POST["name"] .
    "` (`id` INT AUTO_INCREMENT PRIMARY KEY, `when` DATETIME DEFAULT CURRENT_TIMESTAMP, `where` VARCHAR(255) NOT NULL, `who` TINYINT NOT NULL, `name` VARCHAR(255) NOT NULL, `cnt` TINYINT NOT NULL, `price` FLOAT NOT NULL, `link` INT";

$n = 0;
while (isset($_POST["p" . $n])) {
    $query .= ", `" . $_POST["p" . $n] . "` FLOAT NOT NULL";
    $n++;
}

$query .= ") ENGINE=INNODB;";
print($query);
if (!$conn->query($query)) {
    echo "nah";
    die();
} else {
    $conn->query("INSERT INTO `tables` (`name`) VALUES ('".$_POST["name"]."');");
}


?>
