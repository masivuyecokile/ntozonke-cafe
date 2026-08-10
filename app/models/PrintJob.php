<?php
class PrintJob {
    private PDO $db;
    public function __construct() {
        $this->db=(new Database())->connect();
    }private function getSetting(string $key, float $default=0):float {
        $stmt=$this->db->prepare("
    SELECT setting_value
    FROM settings
    WHERE setting_key = :setting_key
    LIMIT 1
    ");
        $stmt->execute([':setting_key'=>$key]);
        $row=$stmt->fetch();
        return $row?(float)$row->setting_value:$default;
    }public function getAllRecent():array {
        $stmt=$this->db->query("
    SELECT
    pj.*,
    p.pc_name,
    u.name AS approved_by_name
    FROM print_jobs pj
    LEFT JOIN pcs p ON p.id = pj.pc_id
    LEFT JOIN users u ON u.id = pj.approved_by
    ORDER BY pj.id DESC
    LIMIT 100
    ");
        return $stmt->fetchAll();
    }public function getStatsToday():object {
        $stmt=$this->db->query("
    SELECT
    COUNT(*) AS total_jobs,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_jobs,
    SUM(CASE WHEN status = 'printed' THEN 1 ELSE 0 END) AS printed_jobs,
    COALESCE(SUM(CASE WHEN status = 'printed' THEN amount ELSE 0 END), 0) AS print_revenue,
    COALESCE(SUM(CASE WHEN status = 'printed' THEN pages * copies ELSE 0 END), 0) AS total_pages
    FROM print_jobs
    WHERE DATE(created_at) = CURDATE()
    ");
        return $stmt->fetch();
    }public function createIncomingJob(array $data):int {
        $pages=max(1, (int)$data['pages']);
        $copies=max(1, (int)$data['copies']);
        $printType=$data['print_type'];
        $rate=$printType==='colour'?$this->getSetting('print_colour_rate', 2.00):$this->getSetting('print_bw_rate', 0.50);
        $amount=$pages*$copies*$rate;
        $sessionId=null;
        if(!empty($data['pc_id'])) {
            $sessionStmt=$this->db->prepare("
        SELECT id
        FROM sessions
        WHERE pc_id = :pc_id
        AND status = 'active'
        ORDER BY id DESC
        LIMIT 1
        ");
            $sessionStmt->execute([':pc_id'=>$data['pc_id']]);
            $session=$sessionStmt->fetch();
            $sessionId=$session?$session->id:null;
        }$stmt=$this->db->prepare("
INSERT INTO print_jobs (
pc_id,
session_id,
source,
document_name,
printer_name,
pages,
copies,
print_type,
amount,
status,
sync_status
) VALUES (
:pc_id,
:session_id,
:source,
:document_name,
:printer_name,
:pages,
:copies,
:print_type,
:amount,
'pending',
'pending'
)
");
        $stmt->execute([':pc_id'=>$data['pc_id']?:null, ':session_id'=>$sessionId, ':source'=>$data['source'], ':document_name'=>$data['document_name'], ':printer_name'=>$data['printer_name'], ':pages'=>$pages, ':copies'=>$copies, ':print_type'=>$printType, ':amount'=>$amount]);
        return(int)$this->db->lastInsertId();
    }public function approveAndPrint(int $printJobId, int $approvedBy):object {
        $this->db->beginTransaction();
        try {
            $stmt=$this->db->prepare("
        SELECT *
        FROM print_jobs
        WHERE id = :id
        AND status IN ('pending','held')
        LIMIT 1
        FOR UPDATE
        ");
            $stmt->execute([':id'=>$printJobId]);
            $job=$stmt->fetch();
            if(!$job) {
                throw new Exception('Print job not found or already processed.');
            }$now=date('Y-m-d H:i:s');
            $update=$this->db->prepare("
UPDATE print_jobs
SET status = 'printed',
approved_by = :approved_by,
approved_at = :approved_at,
printed_at = :printed_at,
sync_status = 'pending'
WHERE id = :id
");
            $update->execute([':approved_by'=>$approvedBy, ':approved_at'=>$now, ':printed_at'=>$now, ':id'=>$printJobId]);
            $saleCheck=$this->db->prepare("
SELECT id
FROM sales
WHERE print_job_id = :print_job_id
LIMIT 1
");
            $saleCheck->execute([':print_job_id'=>$printJobId]);
            $existingSale=$saleCheck->fetch();
            if(!$existingSale) {
                $sale=$this->db->prepare("
    INSERT INTO sales (
    session_id,
    print_job_id,
    sale_type,
    description,
    amount,
    payment_method,
    created_by,
    sync_status
) VALUES (
:session_id,
:print_job_id,
'printing',
:description,
:amount,
'cash',
:created_by,
'pending'
)
");
                $sale->execute([':session_id'=>$job->session_id, ':print_job_id'=>$printJobId, ':description'=>'Printing - '.$job->document_name, ':amount'=>$job->amount, ':created_by'=>$approvedBy]);
            }$this->db->commit();
            return $job;
        }catch(Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }public function reject(int $printJobId, int $rejectedBy, string $reason=''):object {
        $this->db->beginTransaction();
        try {
            $stmt=$this->db->prepare("
        SELECT *
        FROM print_jobs
        WHERE id = :id
        AND status IN ('pending','held')
        LIMIT 1
        FOR UPDATE
        ");
            $stmt->execute([':id'=>$printJobId]);
            $job=$stmt->fetch();
            if(!$job) {
                throw new Exception('Print job not found or already processed.');
            }$update=$this->db->prepare("
UPDATE print_jobs
SET status = 'rejected',
rejected_by = :rejected_by,
rejected_at = :rejected_at,
rejection_reason = :rejection_reason,
sync_status = 'pending'
WHERE id = :id
");
            $update->execute([':rejected_by'=>$rejectedBy, ':rejected_at'=>date('Y-m-d H:i:s'), ':rejection_reason'=>$reason, ':id'=>$printJobId]);
            $this->db->commit();
            return $job;
        }catch(Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }public function getPendingSummary():object {
        $stmt=$this->db->query("
    SELECT
    COUNT(*) AS pending_count,
    COALESCE(SUM(amount), 0) AS pending_value
    FROM print_jobs
    WHERE status = 'pending'
    ");
        return $stmt->fetch();
    }public function getLatestPending():?object {
        $stmt=$this->db->query("
    SELECT
    pj.*,
    p.pc_name
    FROM print_jobs pj
    LEFT JOIN pcs p ON p.id = pj.pc_id
    WHERE pj.status = 'pending'
    ORDER BY pj.id DESC
    LIMIT 1
    ");
        $job=$stmt->fetch();
        return $job?:null;
    }public function createAdminDirectPrint(array $data, int $createdBy):int {
        $this->db->beginTransaction();
        try {
            $pages=max(1, (int)$data['pages']);
            $copies=max(1, (int)$data['copies']);
            $printType=$data['print_type'];
            $rate=$printType==='colour'?$this->getSetting('print_colour_rate', 2.00):$this->getSetting('print_bw_rate', 0.50);
            $amount=$pages*$copies*$rate;
            $now=date('Y-m-d H:i:s');
            $stmt=$this->db->prepare("
        INSERT INTO print_jobs (
        pc_id,
        session_id,
        source,
        document_name,
        printer_name,
        pages,
        copies,
        print_type,
        amount,
        status,
        approved_by,
        approved_at,
        printed_at,
        sync_status
    ) VALUES (
    NULL,
    NULL,
    'admin_direct',
    :document_name,
    :printer_name,
    :pages,
    :copies,
    :print_type,
    :amount,
    'printed',
    :approved_by,
    :approved_at,
    :printed_at,
    'pending'
)
");
            $stmt->execute([':document_name'=>$data['document_name'], ':printer_name'=>$data['printer_name'], ':pages'=>$pages, ':copies'=>$copies, ':print_type'=>$printType, ':amount'=>$amount, ':approved_by'=>$createdBy, ':approved_at'=>$now, ':printed_at'=>$now]);
            $printJobId=(int)$this->db->lastInsertId();
            $sale=$this->db->prepare("
INSERT INTO sales (
session_id,
print_job_id,
sale_type,
description,
amount,
payment_method,
created_by,
sync_status
) VALUES (
NULL,
:print_job_id,
'printing',
:description,
:amount,
'cash',
:created_by,
'pending'
)
");
            $sale->execute([':print_job_id'=>$printJobId, ':description'=>'Admin direct print - '.$data['document_name'], ':amount'=>$amount, ':created_by'=>$createdBy]);
            $this->db->commit();
            return $printJobId;
        }catch(Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
