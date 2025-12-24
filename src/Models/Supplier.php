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

    public static function find($id, $userId = null, $role = 'admin') {
        $pdo = Database::getConnection();
        
        $sql = "SELECT s.* FROM suppliers s ";
        
        // Se specificato un utente NON admin, controlla i permessi
        if ($userId && $role !== 'admin') {
            $sql .= " JOIN user_suppliers us ON s.id = us.supplier_id 
                      WHERE s.id = :id AND us.user_id = :uid";
        } else {
            $sql .= " WHERE s.id = :id";
        }

        $stmt = $pdo->prepare($sql);
        
        $params = ['id' => $id];
        if ($userId && $role !== 'admin') {
            $params['uid'] = $userId;
        }

        $stmt->execute($params);
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