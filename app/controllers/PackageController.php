<?php
require_once __DIR__.'/../models/InternetPackage.php';
class PackageController {
    public function index():void {
        $packageModel=new InternetPackage();
        $packages=$packageModel->getAll();
        $csrfToken=$_SESSION['csrf_token']??bin2hex(random_bytes(32));
        $_SESSION['csrf_token']=$csrfToken;
        require_once __DIR__.'/../views/packages/index.php';
    }public function storeAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $data=$this->validatePackageData($_POST);
        try {
            $packageModel=new InternetPackage();
            $packageId=$packageModel->create($data);
            jsonResponse(true, 'Internet package created successfully.', ['package_id'=>$packageId]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function updateAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $packageId=(int)($_POST['package_id']??0);
        if($packageId<=0) {
            jsonResponse(false, 'Invalid package selected.', [], 422);
        }$data=$this->validatePackageData($_POST);
        try {
            $packageModel=new InternetPackage();
            if(!$packageModel->findById($packageId)) {
                jsonResponse(false, 'Package not found.', [], 404);
            }$packageModel->update($packageId, $data);
            jsonResponse(true, 'Internet package updated successfully.');
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function toggleAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $packageId=(int)($_POST['package_id']??0);
        if($packageId<=0) {
            jsonResponse(false, 'Invalid package selected.', [], 422);
        }try {
            $packageModel=new InternetPackage();
            $newStatus=$packageModel->toggleStatus($packageId);
            jsonResponse(true, 'Package status updated successfully.', ['status'=>$newStatus]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }private function validatePackageData(array $input):array {
        $packageName=trim($input['package_name']??'');
        $minutes=(int)($input['minutes']??0);
        $price=(float)($input['price']??0);
        $status=$input['status']??'active';
        $sortOrder=(int)($input['sort_order']??0);
        if($packageName==='') {
            jsonResponse(false, 'Package name is required.', [], 422);
        }if($minutes<=0) {
            jsonResponse(false, 'Minutes must be greater than zero.', [], 422);
        }if($price<=0) {
            jsonResponse(false, 'Price must be greater than zero.', [], 422);
        }if(!in_array($status, ['active', 'inactive'], true)) {
            jsonResponse(false, 'Invalid package status.', [], 422);
        }return['package_name'=>$packageName, 'minutes'=>$minutes, 'price'=>$price, 'status'=>$status, 'sort_order'=>$sortOrder];
    }private function checkCsrf():void {
        $csrfToken=$_POST['csrf_token']??'';
        if(!$csrfToken||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }
    }
}
