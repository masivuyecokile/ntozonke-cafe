<?php
require_once __DIR__.'/../models/Sale.php';
class SaleController {
    public function index():void {
        $saleModel=new Sale();
        $selectedDate=$_GET['date']??date('Y-m-d');
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate=date('Y-m-d');
        }$stats=$saleModel->getStatsByDate($selectedDate);
        $sales=$saleModel->getByDate($selectedDate);
        $csrfToken=$_SESSION['csrf_token']??bin2hex(random_bytes(32));
        $_SESSION['csrf_token']=$csrfToken;
        require_once __DIR__.'/../views/sales/index.php';
    }
}
