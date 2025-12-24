<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class Document {
    
    // Recupera la storia di un documento con controllo permessi
    public static function getHistory($id, $userId, $role) {
        $pdo = Database::getConnection();
        
        // Info Documento
        $sql = "SELECT d.*, s.name as supplier_name, s.currency 
                FROM documents d 
                JOIN suppliers s ON d.supplier_id = s.id ";

        if ($role !== 'admin') {
            $sql .= " JOIN user_suppliers us ON s.id = us.supplier_id ";
        }

        $sql .= " WHERE d.id = :id ";

        if ($role !== 'admin') {
            $sql .= " AND us.user_id = :uid ";
        }

        $stmtDoc = $pdo->prepare($sql);
        $params = ['id' => $id];
        if ($role !== 'admin') $params['uid'] = $userId;

        $stmtDoc->execute($params);
        $doc = $stmtDoc->fetch();

        if (!$doc) return null;

        // Transazioni collegate
        $stmtTrans = $pdo->prepare("
            SELECT t.*, pe.received_at, pe.subject, pe.id as remittance_id
            FROM transactions t
            JOIN processed_emails pe ON t.processed_email_id = pe.id
            WHERE t.document_id = :id
            ORDER BY pe.received_at DESC
        ");
        $stmtTrans->execute(['id' => $id]);
        $history = $stmtTrans->fetchAll();

        return ['doc' => $doc, 'history' => $history];
    }
    
    // Recupera tutti i documenti di un fornitore (Controllo accesso fatto a monte su Supplier::find, ma non fa male)
    public static function getBySupplier($supplierId, $startDate, $endDate) {
        $pdo = Database::getConnection();
        $sql = "
            SELECT d.*, 
                   SUM(t.amount) as total_paid, 
                   MAX(t.payment_date) as last_payment
            FROM documents d
            LEFT JOIN transactions t ON d.id = t.document_id
            WHERE d.supplier_id = :sid
            AND (t.payment_date BETWEEN :start AND :end OR t.payment_date IS NULL)
            GROUP BY d.id
            ORDER BY d.document_date DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'sid' => $supplierId,
            'start' => $startDate,
            'end' => $endDate
        ]);
        return $stmt->fetchAll();
    }
}