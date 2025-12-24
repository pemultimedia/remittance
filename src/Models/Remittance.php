<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class Remittance {
    // Recupera la lista delle email (pagamenti) filtrando per DATA PAGAMENTO
    // Se admin, vede tutto. Se user, vede solo i suoi fornitori.
    public static function getList($userId, $role, $startDate, $endDate) {
        $pdo = Database::getConnection();
        
        $sql = "
            SELECT 
                pe.id, 
                pe.received_at, 
                pe.subject, 
                s.id as supplier_id,  -- <--- AGGIUNTO QUESTO
                s.name as supplier_name, 
                s.amazon_supplier_site_name,
                s.currency,
                COUNT(t.id) as items_count,
                SUM(t.amount) as total_amount,
                MAX(t.payment_date) as payment_date
            FROM processed_emails pe
            JOIN suppliers s ON pe.supplier_id = s.id
        ";

        if ($role !== 'admin') {
            $sql .= " JOIN user_suppliers us ON s.id = us.supplier_id ";
        }

        $sql .= " JOIN transactions t ON pe.id = t.processed_email_id
                  WHERE t.payment_date BETWEEN :start AND :end ";

        if ($role !== 'admin') {
            $sql .= " AND us.user_id = :uid ";
        }

        // Aggiungiamo s.id anche nel GROUP BY
        $sql .= "
            GROUP BY pe.id, pe.received_at, pe.subject, s.id, s.name, s.amazon_supplier_site_name, s.currency
            ORDER BY payment_date DESC
        ";

        $stmt = $pdo->prepare($sql);
        
        $params = [
            'start' => $startDate,
            'end' => $endDate
        ];

        if ($role !== 'admin') {
            $params['uid'] = $userId;
        }

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Dettaglio singola remittance con controllo permessi
    public static function getDetails($id, $userId, $role) {
        $pdo = Database::getConnection();
        
        // 1. Recupero Metadati (Testata)
        $sql = "SELECT pe.*, s.name as supplier_name, s.currency 
                FROM processed_emails pe 
                JOIN suppliers s ON pe.supplier_id = s.id ";
        
        // Se NON è admin, controlla che il fornitore sia assegnato all'utente
        if ($role !== 'admin') {
            $sql .= " JOIN user_suppliers us ON s.id = us.supplier_id ";
        }

        $sql .= " WHERE pe.id = :id ";

        if ($role !== 'admin') {
            $sql .= " AND us.user_id = :uid ";
        }

        $stmtMeta = $pdo->prepare($sql);
        $params = ['id' => $id];
        if ($role !== 'admin') $params['uid'] = $userId;
        
        $stmtMeta->execute($params);
        $meta = $stmtMeta->fetch();

        if (!$meta) return null; // Accesso negato o ID inesistente

        // 2. Recupero Transazioni (Righe)
        // Qui non serve rifare il controllo permessi perché se abbiamo $meta, l'utente ha accesso
        $stmtTrans = $pdo->prepare("
            SELECT t.*, d.document_number, d.type as doc_type
            FROM transactions t
            LEFT JOIN documents d ON t.document_id = d.id
            WHERE t.processed_email_id = :id
            ORDER BY t.amount DESC
        ");
        $stmtTrans->execute(['id' => $id]);
        $items = $stmtTrans->fetchAll();

        return ['meta' => $meta, 'items' => $items];
    }
}