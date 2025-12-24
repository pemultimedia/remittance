<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class Supplier {
    public static function getAll() {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function find($id) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function updateGoogleConfig($id, $data) {
        $pdo = Database::getConnection();
        $sql = "UPDATE suppliers SET 
                name = :name,
                google_spreadsheet_id = :sid,
                google_sheet_invoices = :sinv,
                google_sheet_credit_notes = :scn,
                updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'sid' => $data['google_spreadsheet_id'],
            'sinv' => $data['google_sheet_invoices'],
            'scn' => $data['google_sheet_credit_notes'],
            'id' => $id
        ]);
    }
}