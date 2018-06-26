<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization');
header('Content-Type: application/json');

require __DIR__ . '/Parser.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['requirement'])) {
    die('Only Post Method is allowed');
}

$requirementBody = $_POST['requirement'];

$requirements = explode('##########', $requirementBody);

$requirements = array_map(function($value) {

    $parser = new Parser($value);

    return $parser->returnRequirements();
}, $requirements);


//echo '<pre>';
//print_r($requirements);
//echo '</pre>';die;

echo json_encode($requirements);
exit();

