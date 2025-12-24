<?php

// Carica l'autoloader di Composer
require __DIR__ . '/../vendor/autoload.php';

use App\Services\ImapService;
use App\Services\ParserService;
use App\Core\Database;
use Dotenv\Dotenv;

// 1. Inizializzazione Ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');

echo "[" . date('Y-m-d H:i:s') . "] Avvio script elaborazione email...\n";

try {
    $pdo = Database::getConnection();
    $imapService = new ImapService();
    $parserService = new ParserService();

    // 2. Determina da quando cercare le email
    // Cerchiamo la data dell'ultima email processata con successo
    $stmt = $pdo->query("SELECT MAX(received_at) as last_date FROM processed_emails WHERE status = 'success'");
    $lastDate = $stmt->fetchColumn();

    $sinceDate = null;
    if ($lastDate) {
        // IMAP richiede formato 'd-M-Y'. Prendiamo il giorno stesso dell'ultima email per sicurezza
        // (filtreremo i duplicati tramite Message-ID nel DB)
        $sinceDate = date('d-M-Y', strtotime($lastDate));
        echo "Ricerca email a partire dal: $sinceDate\n";
    } else {
        echo "Nessuna email precedente trovata. Ricerca completa (o ultimi 30gg default IMAP).\n";
    }

    // 3. Scarica Email
    $emails = $imapService->fetchRemittanceEmails($sinceDate);
    echo "Trovate " . count($emails) . " email potenziali.\n";

    foreach ($emails as $emailData) {
        echo "Elaborazione email: " . $emailData['subject'] . " (ID: " . $emailData['message_id'] . ")... ";

        // 4. Controllo Duplicati (Idempotenza)
        // Verifichiamo se il message_id esiste già
        $stmtCheck = $pdo->prepare("SELECT id FROM processed_emails WHERE message_id = :mid");
        $stmtCheck->execute(['mid' => $emailData['message_id']]);
        if ($stmtCheck->fetch()) {
            echo "SKIPPED (Già processata).\n";
            continue;
        }

        $pdo->beginTransaction();

        try {
            // 5. Parsing
            $parsedData = $parserService->parseEmailContent($emailData['body']);
            $meta = $parsedData['metadata'];
            $transactions = $parsedData['transactions'];

            // Validazione minima
            if (empty($meta['site_name'])) {
                throw new Exception("Impossibile trovare Supplier Site Name nell'email.");
            }

            // 6. Gestione Fornitore (Upsert)
            // Se non esiste lo crea, se esiste restituisce l'ID
            $stmtSupplier = $pdo->prepare("
                INSERT INTO suppliers (amazon_supplier_site_name, amazon_supplier_no, name, currency)
                VALUES (:site, :no, :name, :curr)
                ON CONFLICT (amazon_supplier_site_name) 
                DO UPDATE SET updated_at = NOW()
                RETURNING id
            ");
            $stmtSupplier->execute([
                'site' => $meta['site_name'],
                'no' => $meta['supplier_no'],
                'name' => $meta['supplier_name'],
                'curr' => $meta['currency']
            ]);
            $supplierId = $stmtSupplier->fetchColumn();

            // 7. Registra Email Processata
            $stmtEmail = $pdo->prepare("
                INSERT INTO processed_emails (message_id, supplier_id, subject, received_at, status)
                VALUES (:mid, :sid, :sub, :rec, 'success')
                RETURNING id
            ");
            $stmtEmail->execute([
                'mid' => $emailData['message_id'],
                'sid' => $supplierId,
                'sub' => $emailData['subject'],
                'rec' => date('Y-m-d H:i:s', strtotime($emailData['date']))
            ]);
            $emailDbId = $stmtEmail->fetchColumn();

            // 8. Salvataggio Transazioni e Documenti
            foreach ($transactions as $trans) {
                
                // A. Gestione Documento (Fattura/Nota Credito)
                // Inserisce il documento se non esiste per quel fornitore
                $stmtDoc = $pdo->prepare("
                    INSERT INTO documents (supplier_id, document_number, document_date, description, type)
                    VALUES (:sid, :doc_num, :doc_date, :desc, :type)
                    ON CONFLICT (supplier_id, document_number) 
                    DO UPDATE SET updated_at = NOW() -- Aggiorna timestamp per dire 'l'ho rivisto'
                    RETURNING id
                ");
                
                $stmtDoc->execute([
                    'sid' => $supplierId,
                    'doc_num' => $trans['invoice_number'],
                    'doc_date' => $trans['invoice_date'],
                    'desc' => $trans['description'],
                    'type' => $trans['type']
                ]);
                $documentId = $stmtDoc->fetchColumn();

                // B. Inserimento Transazione (Pagamento/Storno)
                // Qui inseriamo sempre una nuova riga perché una fattura può avere più pagamenti/aggiustamenti
                $stmtTrans = $pdo->prepare("
                    INSERT INTO transactions (document_id, processed_email_id, amount, payment_date, discount_taken, amount_paid, raw_data)
                    VALUES (:doc_id, :email_id, :amount, :p_date, :disc, :paid, :raw)
                ");

                // Payment date viene dall'header della mail (metadati), non dalla riga della fattura
                $paymentDate = $meta['payment_date_sql'] ?? date('Y-m-d');

                $stmtTrans->execute([
                    'doc_id' => $documentId,
                    'email_id' => $emailDbId,
                    'amount' => $trans['amount'], // Importo pulito (negativo se storno)
                    'p_date' => $paymentDate,
                    'disc' => $trans['discount_taken'],
                    'paid' => $trans['amount'], // Ridondante ma utile per storico
                    'raw' => json_encode($trans)
                ]);
            }

            // TODO: Qui chiameremo il GoogleSheetService per aggiornare i fogli
            // $googleService->updateSheet($supplierId, $transactions);

            $pdo->commit();
            echo "OK (Salvato DB).\n";

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "ERRORE: " . $e->getMessage() . "\n";

            // Log dell'errore nel DB (se possibile, altrimenti solo console)
            try {
                $stmtErr = $pdo->prepare("
                    INSERT INTO processed_emails (message_id, subject, received_at, status, error_log)
                    VALUES (:mid, :sub, :rec, 'error', :err)
                    ON CONFLICT (message_id) DO UPDATE SET status = 'error', error_log = :err
                ");
                $stmtErr->execute([
                    'mid' => $emailData['message_id'],
                    'sub' => $emailData['subject'],
                    'rec' => date('Y-m-d H:i:s', strtotime($emailData['date'])),
                    'err' => $e->getMessage()
                ]);
            } catch (Exception $logEx) {
                echo "FATAL: Impossibile loggare errore su DB.\n";
            }
        }
    }

} catch (Exception $e) {
    echo "Errore critico script: " . $e->getMessage() . "\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Fine esecuzione.\n";