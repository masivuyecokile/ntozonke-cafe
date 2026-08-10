<?php
require_once __DIR__.'/../models/Setting.php';
class PricingController {
    public function index():void {
        $settingModel=new Setting();
        $settings=$settingModel->getAllAsObject();
        $csrfToken=$_SESSION['csrf_token']??bin2hex(random_bytes(32));
        $_SESSION['csrf_token']=$csrfToken;
        require_once __DIR__.'/../views/pricing/index.php';
    }public function updateAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $allowedFields=['print_bw_rate', 'print_colour_rate', 'scan_rate', 'photocopy_bw_rate', 'lamination_rate', 'binding_rate'];
        $updates=[];
        foreach($allowedFields as $field) {
            $value=trim($_POST[$field]??'');
            if($value===''||!is_numeric($value)||(float)$value<0) {
                jsonResponse(false, 'Please enter valid pricing amounts.', [], 422);
            }$updates[$field]=number_format((float)$value, 2, '.', '');
        }try {
            $settingModel=new Setting();
            $settingModel->updateMany($updates);
            jsonResponse(true, 'Pricing updated successfully.');
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }private function checkCsrf():void {
        $csrfToken=$_POST['csrf_token']??'';
        if(!$csrfToken||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }
    }
}
