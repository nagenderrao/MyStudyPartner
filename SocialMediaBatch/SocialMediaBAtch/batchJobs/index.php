<?php
require_once("batch_engine.php");
require_once("batch_admin.php");
require_once("persistence.php");
require_once("semantria_persistence.php");
require_once("semantria_analizer.php");
require_once("semantria_app.php");

ini_set('display_errors', 1);
ini_set('memory_limit','1000M');
error_reporting(0);
echo "Running...";
$mapp = new SemantriaApp();
$mapp->Run();
$mapp->CleanUp();
echo "Completed...";
?>
