<?php

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
//echo '</pre>';


header('Content-Type: application/json');
echo json_encode($requirements);
exit();

