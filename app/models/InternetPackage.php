<?php
class InternetPackage {
    private PDO $db;
    public function __construct() {
        $this->db=(new Database())->connect();
    }public function getActive():array {
        $stmt=$this->db->query("
    SELECT *
    FROM internet_packages
    WHERE status = 'active'
    ORDER BY sort_order ASC, minutes ASC
    ");
        return $stmt->fetchAll();
    }public function getAll():array {
        $stmt=$this->db->query("
    SELECT *
    FROM internet_packages
    ORDER BY sort_order ASC, minutes ASC
    ");
        return $stmt->fetchAll();
    }public function findActiveById(int $id):?object {
        $stmt=$this->db->prepare("
    SELECT *
    FROM internet_packages
    WHERE id = :id
    AND status = 'active'
    LIMIT 1
    ");
        $stmt->execute([':id'=>$id]);
        $package=$stmt->fetch();
        return $package?:null;
    }public function findById(int $id):?object {
        $stmt=$this->db->prepare("
    SELECT *
    FROM internet_packages
    WHERE id = :id
    LIMIT 1
    ");
        $stmt->execute([':id'=>$id]);
        $package=$stmt->fetch();
        return $package?:null;
    }public function create(array $data):int {
        $stmt=$this->db->prepare("
    INSERT INTO internet_packages (
    package_name,
    minutes,
    price,
    status,
    sort_order
) VALUES (
:package_name,
:minutes,
:price,
:status,
:sort_order
)
");
        $stmt->execute([':package_name'=>$data['package_name'], ':minutes'=>$data['minutes'], ':price'=>$data['price'], ':status'=>$data['status'], ':sort_order'=>$data['sort_order']]);
        return(int)$this->db->lastInsertId();
    }public function update(int $id, array $data):void {
        $stmt=$this->db->prepare("
    UPDATE internet_packages
    SET package_name = :package_name,
    minutes = :minutes,
    price = :price,
    status = :status,
    sort_order = :sort_order
    WHERE id = :id
    ");
        $stmt->execute([':package_name'=>$data['package_name'], ':minutes'=>$data['minutes'], ':price'=>$data['price'], ':status'=>$data['status'], ':sort_order'=>$data['sort_order'], ':id'=>$id]);
    }public function toggleStatus(int $id):string {
        $package=$this->findById($id);
        if(!$package) {
            throw new Exception('Package not found.');
        }$newStatus=$package->status==='active'?'inactive':'active';
        $stmt=$this->db->prepare("
UPDATE internet_packages
SET status = :status
WHERE id = :id
");
        $stmt->execute([':status'=>$newStatus, ':id'=>$id]);
        return $newStatus;
    }
}
