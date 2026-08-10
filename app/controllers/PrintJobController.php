<?php
require_once __DIR__.'/../models/PrintJob.php';
require_once __DIR__.'/../models/PC.php';
class PrintJobController {
    public function index():void {
        $printJobModel=new PrintJob();
        $pcModel=new PC();
        $printJobs=$printJobModel->getAllRecent();
        $pcs=$pcModel->getAll();
        $stats=$printJobModel->getStatsToday();
        $csrfToken=$_SESSION['csrf_token']??bin2hex(random_bytes(32));
        $_SESSION['csrf_token']=$csrfToken;
        require_once __DIR__.'/../views/print-jobs/index.php';
    }public function testIncomingAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $pcId=(int)($_POST['pc_id']??0);
        $documentName=trim($_POST['document_name']??'');
        $pages=(int)($_POST['pages']??0);
        $copies=(int)($_POST['copies']??1);
        $printType=$_POST['print_type']??'black_white';
        if($pcId<=0||$documentName===''||$pages<=0) {
            jsonResponse(false, 'Please complete all required print job fields.', [], 422);
        }if(!in_array($printType, ['black_white', 'colour'], true)) {
            jsonResponse(false, 'Invalid print type selected.', [], 422);
        }try {
            $printJobModel=new PrintJob();
            $printJobId=$printJobModel->createIncomingJob(['pc_id'=>$pcId, 'source'=>'pc_client', 'document_name'=>$documentName, 'printer_name'=>'Ntozonke Print Queue', 'pages'=>$pages, 'copies'=>$copies, 'print_type'=>$printType]);
            jsonResponse(true, 'Incoming print job received successfully.', ['print_job_id'=>$printJobId]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function approveAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $printJobId=(int)($_POST['print_job_id']??0);
        if($printJobId<=0) {
            jsonResponse(false, 'Invalid print job selected.', [], 422);
        }try {
            $printJobModel=new PrintJob();
            $printJobModel->approveAndPrint($printJobId, (int)$_SESSION['user_id']);
            jsonResponse(true, 'Print job approved and marked as printed.');
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function rejectAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $printJobId=(int)($_POST['print_job_id']??0);
        $reason=trim($_POST['reason']??'');
        if($printJobId<=0) {
            jsonResponse(false, 'Invalid print job selected.', [], 422);
        }try {
            $printJobModel=new PrintJob();
            $printJobModel->reject($printJobId, (int)$_SESSION['user_id'], $reason);
            jsonResponse(true, 'Print job rejected successfully.');
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }private function checkCsrf():void {
        $csrfToken=$_POST['csrf_token']??'';
        if(!$csrfToken||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            jsonResponse(false, 'Security token expired. Please refresh and try again.', [], 419);
        }
    }public function pendingSummaryAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='GET') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }try {
            $printJobModel=new PrintJob();
            $summary=$printJobModel->getPendingSummary();
            $latest=$printJobModel->getLatestPending();
            jsonResponse(true, 'Pending print jobs loaded.', ['pending_count'=>(int)($summary->pending_count??0), 'pending_value'=>(float)($summary->pending_value??0), 'latest_job'=>$latest?['id'=>$latest->id, 'pc_name'=>$latest->pc_name??'Unknown PC', 'document_name'=>$latest->document_name, 'amount'=>(float)$latest->amount, 'created_at'=>$latest->created_at]:null]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }public function adminDirectAjax():void {
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }$this->checkCsrf();
        $documentName=trim($_POST['document_name']??'');
        $pages=(int)($_POST['pages']??0);
        $copies=(int)($_POST['copies']??1);
        $printType=$_POST['print_type']??'black_white';
        if($documentName===''||$pages<=0) {
            jsonResponse(false, 'Please complete all required admin print fields.', [], 422);
        }if(!in_array($printType, ['black_white', 'colour'], true)) {
            jsonResponse(false, 'Invalid print type selected.', [], 422);
        }try {
            $printJobModel=new PrintJob();
            $printJobId=$printJobModel->createAdminDirectPrint(['document_name'=>$documentName, 'printer_name'=>'Admin Printer', 'pages'=>$pages, 'copies'=>$copies, 'print_type'=>$printType], (int)$_SESSION['user_id']);
            jsonResponse(true, 'Admin direct print recorded successfully.', ['print_job_id'=>$printJobId]);
        }catch(Exception $e) {
            jsonResponse(false, $e->getMessage(), [], 500);
        }
    }
}
