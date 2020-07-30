<?php

/**
*Class to create and issue database connections
*This would force the system to use an available database instance or create a new one
**/ 

header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$_POST = json_decode(file_get_contents("php://input"),true);



require_once './../repo/Service.php';
require_once './../db/Database.php';

$db = Database::getInstance();
$service = new Service();

//echo $service->makepayment(60, 1);

if(isset($_GET['customers'])){
    $customers = $service->getcustomersummaries();
    echo json_encode(["customers"=>$customers, "success"=>true]);
}

if(isset($_GET['debtcustomers'])){
    $customers = $service->getallcustomersummarydebt();
    echo json_encode(["customers"=>$customers, "success"=>true]);
}

if(isset($_GET['customerid'])){
    $custId = $_GET['customerid'];
    $customers = $service->getcustomersummarydebt($custId);
    echo json_encode(["customers"=>$customers, "success"=>true]);
}

if(isset($_POST['paying'])){
    $amount = $_POST['paying'];
    $customerid = $_POST['customerid'];
    $res = $service->makepayment($amount, $customerid);
    echo json_encode(["res"=>$res, "success"=>true]);
}






?>