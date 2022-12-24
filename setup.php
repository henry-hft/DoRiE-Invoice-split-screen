<?php
$dbFile = "api/db/dorie.db";

$sqlSchema = "sql/schema.sql"

// check if folder already exists
if (!file_exists("api/db")) {
	mkdir("api/db");
}

// check if database file already exists
if (file_exists($dbFile)) {
	unlink($dbFile);
}

$database = new SQLite3($dbFile);
chmod($dbFile, 0775);
chown("api/db/", "www-data");
chown($dbFile, "www-data");

$sql = file_get_contents($sqlSchema);
		
$database->exec($sql);
				
$database->close();

echo "IMS CHIPS\n";
echo "DoRiE\n";
echo "---------------------------------\n";					
echo "Database successfully created\n";
echo "---------------------------------\n";	
?>
