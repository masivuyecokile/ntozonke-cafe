<?php
require_once __DIR__.'/../models/Setting.php';
require_once __DIR__.'/../models/Sale.php';
class ServiceController {
    public function index():void {
        $settingModel=new Setting();
        $settings=$settingModel->getAllAsObject();
        $csrfToken=$_SESSION['csrf_token']??bin2hex(random_bytes(32));
        $_SESSION['csrf_token']=$csrfToken;
        require_once __DIR__.'/../views/services/index.php';
    }public function storeAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $serviceType=$_POST['service_type']??'';
        $quantity=(int)($_POST['quantity']??0);
        $paymentMethod=$_POST['payment_method']??'cash';
        $notes=trim($_POST['notes']??'');
        if($quantity<=0) {
            jsonResponse(false, 'Quantity must be greater than zero.', [], 422);
        }if(!in_array($paymentMethod, ['cash', 'card', 'eft', 'free'], true)) {
            jsonResponse(false, 'Invalid payment method.', [], 422);
        }$serviceMap=['scan'=>['label'=>'Scanning', 'setting_key'=>'scan_rate', 'unit'=>'page'], 'photocopy_bw'=>['label'=>'Photocopy B/W', 'setting_key'=>'photocopy_bw_rate', 'unit'=>'page'], 'lamination'=>['label'=>'Lamination', 'setting_key'=>'lamination_rate', 'unit'=>'item'], 'binding'=>['label'=>'Binding', 'setting_key'=>'binding_rate', 'unit'=>'item']];
        if(!isset($serviceMap[$serviceType])) {
            jsonResponse(false, 'Invalid service selected.', [], 422);
        }try {
            $settingModel=new Setting();
            $settings=$settingModel->getAllAsObject();
            $settingKey=$serviceMap[$serviceType]['setting_key'];
            $rate=(float)($settings-> {
                $settingKey
            }??0);
            if($rate<0) {
                jsonResponse(false, 'Invalid service rate configured.', [], 422);
            }$amount=$paymentMethod==='free'?0:($quantity*$rate);
            $description=$serviceMap[$serviceType]['label'].' x '.$quantity.' '.$serviceMap[$serviceType]['unit'].'(s)';
            if($notes!=='') {
                $description.=' - '.$notes;
            }$saleModel=new Sale();
            $saleId=$saleModel->createServiceSale(['description'=>$description, 'amount'=>$amount, 'payment_method'=>$paymentMethod], (int)$_SESSION['user_id']);
            jsonResponse(true, 'Service sale recorded successfully.', ['sale_id'=>$saleId, 'amount'=>$amount]);
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
