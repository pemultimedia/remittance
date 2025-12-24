<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Exception;

class GoogleSheetService
{
    private Sheets $service;

    public function __construct()
    {
        $client = new Client();
        $client->setApplicationName($_ENV['APP_NAME']);
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig(__DIR__ . '/../../' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS']);
        $client->setAccessType('offline');

        $this->service = new Sheets($client);
    }

    /**
     * Metodo principale chiamato dallo script CLI
     */
    public function updateSupplierSheets(array $supplier, array $transactions): void
    {
        $spreadsheetId = $supplier['google_spreadsheet_id'];
        $invoiceTab = $supplier['google_sheet_invoices']; // Es. "Fatture"
        $creditNoteTab = $supplier['google_sheet_credit_notes']; // Es. "Note Credito"

        if (empty($spreadsheetId)) {
            echo "  [WARN] Nessun Spreadsheet ID configurato per il fornitore {$supplier['name']}.\n";
            return;
        }

        // Separa le transazioni in base al tipo
        $invoices = [];
        $creditNotes = [];

        foreach ($transactions as $t) {
            if ($t['type'] === 'INVOICE') {
                $invoices[] = $t;
            } else {
                $creditNotes[] = $t;
            }
        }

        // Processa Fatture
        if (!empty($invoices) && !empty($invoiceTab)) {
            $this->processInvoices($spreadsheetId, $invoiceTab, $invoices);
        }

        // Processa Note di Credito
        if (!empty($creditNotes) && !empty($creditNoteTab)) {
            $this->processCreditNotes($spreadsheetId, $creditNoteTab, $creditNotes);
        }
    }

    /**
     * Gestione Fatture
     * Match Colonna C (Indice 2). Aggiorna N (13) e O (14).
     */
    private function processInvoices(string $spreadsheetId, string $sheetName, array $transactions): void
    {
        // 1. Scarica la colonna C per trovare i match
        // Range C:C
        $response = $this->service->spreadsheets_values->get($spreadsheetId, "$sheetName!C:C");
        $rows = $response->getValues();
        
        // Mappa: Numero Fattura => Indice Riga (1-based per API Google)
        $existingInvoices = [];
        if ($rows) {
            foreach ($rows as $index => $row) {
                if (isset($row[0])) {
                    // Puliamo il numero fattura per il confronto
                    $cleanNum = trim($row[0]);
                    // Indice riga = index + 1
                    $existingInvoices[$cleanNum] = $index + 1;
                }
            }
        }

        foreach ($transactions as $trans) {
            $docNum = trim($trans['invoice_number']);
            $amount = $trans['amount']; // Importo pagato
            $date = $trans['invoice_date']; // Data pagamento (o invoice date se preferisci, da specifica era "Data Pagamento")
            // Nota: Nello script process_emails.php abbiamo passato 'payment_date' dentro $trans['payment_date'] se disponibile, 
            // ma qui stiamo usando l'array $trans che viene dal DB o dal parser. 
            // Assumiamo che $trans['payment_date'] sia la data del bonifico.
            $paymentDate = $trans['payment_date'] ?? date('Y-m-d');

            if (isset($existingInvoices[$docNum])) {
                // --- UPDATE ---
                $rowIndex = $existingInvoices[$docNum];
                // Colonna N è la 14esima lettera (A=1... N=14).
                // Range N{row}:O{row}
                $range = "$sheetName!N$rowIndex:O$rowIndex";
                $values = [[ $amount, $paymentDate ]];
                $body = new ValueRange(['values' => $values]);
                
                $this->service->spreadsheets_values->update(
                    $spreadsheetId,
                    $range,
                    $body,
                    ['valueInputOption' => 'USER_ENTERED']
                );
                echo "    -> Fattura $docNum aggiornata (Riga $rowIndex).\n";

            } else {
                // --- APPEND ---
                // Se non c'è match, inseriamo una nuova riga.
                // Mettiamo il numero fattura in colonna C, importo in N, data in O.
                // Le altre colonne restano vuote.
                // Struttura riga (A, B, C, ..., N, O)
                // Indici: A=0, B=1, C=2 ... N=13, O=14
                $newRow = array_fill(0, 15, null); // Crea array vuoto fino alla colonna O
                $newRow[2] = $docNum; // Col C
                $newRow[13] = $amount; // Col N
                $newRow[14] = $paymentDate; // Col O

                $body = new ValueRange(['values' => [$newRow]]);
                
                $this->service->spreadsheets_values->append(
                    $spreadsheetId,
                    "$sheetName!A:O",
                    $body,
                    ['valueInputOption' => 'USER_ENTERED']
                );
                echo "    -> Fattura $docNum non trovata. Nuova riga inserita.\n";
            }
        }
    }

    /**
     * Gestione Note Credito
     * Match Colonna A (Indice 0). Aggiorna D (3), E (4), G (6).
     */
    private function processCreditNotes(string $spreadsheetId, string $sheetName, array $transactions): void
    {
        // 1. Scarica la colonna A per trovare i match
        $response = $this->service->spreadsheets_values->get($spreadsheetId, "$sheetName!A:A");
        $rows = $response->getValues();

        $existingNotes = [];
        if ($rows) {
            foreach ($rows as $index => $row) {
                if (isset($row[0])) {
                    $cleanRef = trim($row[0]);
                    $existingNotes[$cleanRef] = $index + 1;
                }
            }
        }

        foreach ($transactions as $trans) {
            $refNum = trim($trans['invoice_number']); // Amazon Ref ID
            $amount = $trans['amount']; // Importo (negativo)
            $desc = $trans['description'];
            // Data di riferimento: usiamo la data fattura/riferimento se c'è, altrimenti data pagamento
            $refDate = $trans['invoice_date'] ?? $trans['payment_date'];

            if (isset($existingNotes[$refNum])) {
                // --- UPDATE ---
                $rowIndex = $existingNotes[$refNum];
                
                // Dobbiamo aggiornare colonne non contigue (D, E, G).
                // Facciamo due update o uno unico coprendo D-G?
                // D=Col 4, E=Col 5, F=?, G=Col 7.
                // Meglio fare update mirati o un range D:G lasciando F invariato?
                // Le API Sheets sovrascrivono. Se F ha dati, non possiamo scrivere null.
                // Facciamo update separati per sicurezza o costruiamo il range se F è vuoto.
                // Opzione sicura: Update D:E e poi G.
                
                // Update D:E (Importo, Descrizione)
                $rangeDE = "$sheetName!D$rowIndex:E$rowIndex";
                $bodyDE = new ValueRange(['values' => [[ $amount, $desc ]]]);
                $this->service->spreadsheets_values->update($spreadsheetId, $rangeDE, $bodyDE, ['valueInputOption' => 'USER_ENTERED']);

                // Update G (Data)
                $rangeG = "$sheetName!G$rowIndex";
                $bodyG = new ValueRange(['values' => [[ $refDate ]]]);
                $this->service->spreadsheets_values->update($spreadsheetId, $rangeG, $bodyG, ['valueInputOption' => 'USER_ENTERED']);

                echo "    -> Nota Credito $refNum aggiornata (Riga $rowIndex).\n";

            } else {
                // --- APPEND ---
                // Col A = Ref, Col D = Importo, Col E = Desc, Col G = Data
                // Indici: A=0, D=3, E=4, G=6
                $newRow = array_fill(0, 7, null);
                $newRow[0] = $refNum;
                $newRow[3] = $amount;
                $newRow[4] = $desc;
                $newRow[6] = $refDate;

                $body = new ValueRange(['values' => [$newRow]]);
                
                $this->service->spreadsheets_values->append(
                    $spreadsheetId,
                    "$sheetName!A:G",
                    $body,
                    ['valueInputOption' => 'USER_ENTERED']
                );
                echo "    -> Nota Credito $refNum non trovata. Nuova riga inserita.\n";
            }
        }
    }
}