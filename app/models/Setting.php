<?php
class Setting {
    private PDO $db;
    public function __construct() {
        $this->db=(new Database())->connect();
    }public function getAllAsObject():object {
        $stmt=$this->db->query("
            SELECT setting_key, setting_value
            FROM settings
        ");
        $rows=$stmt->fetchAll();
        $settings=new stdClass();
        foreach($rows as $row) {
            $settings-> {
                $row->setting_key
            }=$row->setting_value;
        }return $settings;
    }public function updateMany(array $settings):void {
        $stmt=$this->db->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (:setting_key, :setting_value)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        foreach($settings as $key=>$value) {
            $stmt->execute([':setting_key'=>$key, ':setting_value'=>$value]);
        }
    }
}
