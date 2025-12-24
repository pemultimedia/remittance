<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;

class ParserService
{
    /**
     * Metodo principale che orchestra l'estrazione
     */
    public function parseEmailContent(string $htmlContent): array
    {
        // Pulisci l'HTML da eventuali encoding strani (quoted-printable è gestito da php-imap, ma controlliamo)
        if (empty($htmlContent)) {
            throw new Exception("Contenuto email vuoto");
        }

        // 1. Estrai Metadati (Fornitore, Date, Totali)
        $metadata = $this->extractMetadata($htmlContent);

        // 2. Estrai Righe Tabella (Fatture e Note Credito)
        $transactions = $this->extractTableRows($htmlContent);

        return [
            'metadata' => $metadata,
            'transactions' => $transactions
        ];
    }

    /**
     * Estrae i dati di testata usando Regex sul testo (più sicuro per etichette fisse)
     */
    private function extractMetadata(string $html): array
    {
        // Rimuoviamo i tag HTML per facilitare le regex sui metadati
        $text = strip_tags($html);
        
        $data = [];

        // Regex patterns basati sugli esempi forniti
        $patterns = [
            'supplier_name' => '/Payment made to:\s*(.*?)(?:\n|\r)/i',
            'supplier_no' => '/Our Supplier No:\s*(\d+)/i',
            'site_name' => '/Supplier Site Name:\s*(\w+)/i',
            'payment_number' => '/Payment number\s*:\s*(\d+)/i',
            'payment_date' => '/Payment Date\s*:\s*([\d\-A-Z]+)/i', // Es. 18-DEC-2025
            'currency' => '/Payment currency:\s*([A-Z]{3})/i',
            'total_amount' => '/Payment Amount\s*:\s*([\d\.,]+)/i'
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data[$key] = trim($matches[1]);
            } else {
                $data[$key] = null;
            }
        }

        // Normalizza la data di pagamento per il DB (YYYY-MM-DD)
        if ($data['payment_date']) {
            $data['payment_date_sql'] = $this->parseDate($data['payment_date']);
        }

        return $data;
    }

    /**
     * Estrae le righe della tabella parsando il DOM HTML
     */
    private function extractTableRows(string $html): array
    {
        $dom = new DOMDocument();
        // Sopprimi warning per HTML malformato (comune nelle email)
        libxml_use_internal_errors(true);
        // Carica HTML (aggiungiamo un wrapper utf-8 per sicurezza)
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        
        // Cerchiamo la tabella che contiene le intestazioni delle fatture
        // L'header contiene "Invoice Number"
        $rows = $xpath->query('//tr');
        
        $transactions = [];
        $isHeaderFound = false;

        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            
            // Saltiamo righe vuote o con poche colonne
            if ($cols->length < 5) {
                continue;
            }

            $firstCol = trim($cols->item(0)->textContent);

            // Identifica l'header per iniziare a leggere dopo
            if (stripos($firstCol, 'Invoice Number') !== false) {
                $isHeaderFound = true;
                continue;
            }

            // Se non abbiamo ancora trovato l'header, saltiamo
            if (!$isHeaderFound) {
                continue;
            }

            // Se troviamo una riga che sembra un totale o footer, ci fermiamo?
            // Per ora assumiamo che tutto ciò che segue l'header siano dati validi
            // finché la prima colonna non è vuota.
            if (empty($firstCol)) {
                continue;
            }

            // Mappatura colonne basata sull'esempio HTML:
            // 0: Invoice Number
            // 1: Invoice Date
            // 2: Invoice Description
            // 3: Discount Taken (può essere vuoto)
            // 4: Amount Paid
            // 5: Amount Remaining (opzionale, a volte c'è a volte no)

            $invoiceNumber = trim($cols->item(0)->textContent);
            $invoiceDate = trim($cols->item(1)->textContent);
            $description = trim($cols->item(2)->textContent);
            
            // Gestione importi: pulizia virgole e parentesi
            $discountTakenRaw = trim($cols->item(3)->textContent);
            $amountPaidRaw = trim($cols->item(4)->textContent);

            $discountTaken = $this->cleanAmount($discountTakenRaw);
            $amountPaid = $this->cleanAmount($amountPaidRaw);

            // Determina il tipo di transazione
            // Se l'importo pagato è negativo o zero e c'è una descrizione particolare, potrebbe essere una nota credito o storno
            $type = 'INVOICE';
            if ($amountPaid < 0) {
                $type = 'CREDIT_NOTE'; // O storno
            }
            
            // Logica specifica per identificare Note di Credito vs Fatture
            // Spesso le note credito hanno riferimenti come "5801006361" ma importo negativo
            // Oppure descrizioni come "Damage Allowance"
            
            $transactions[] = [
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $this->parseDate($invoiceDate),
                'description' => $description,
                'discount_taken' => $discountTaken,
                'amount' => $amountPaid, // Questo è il valore che va nel DB come transazione
                'type' => $type
            ];
        }

        return $transactions;
    }

    /**
     * Converte stringhe come "26,944.58" o "(7.81)" in float PHP
     */
    private function cleanAmount(string $str): float
    {
        if (empty($str)) {
            return 0.0;
        }

        $isNegative = false;

        // Controlla parentesi per negativi
        if (str_contains($str, '(') && str_contains($str, ')')) {
            $isNegative = true;
        }

        // Rimuovi tutto tranne numeri e punto decimale
        // Attenzione: Amazon usa la virgola come separatore migliaia e punto come decimale (formato US)
        // Rimuoviamo le virgole
        $clean = str_replace(',', '', $str);
        // Rimuoviamo parentesi e spazi
        $clean = preg_replace('/[^\d\.]/', '', $clean);

        $val = (float)$clean;

        return $isNegative ? -$val : $val;
    }

    /**
     * Converte date tipo "15-DEC-2025" in "2025-12-15"
     */
    private function parseDate(string $dateStr): ?string
    {
        if (empty($dateStr)) return null;
        
        // DateTime capisce bene formati come "15-DEC-2025"
        try {
            $dt = new \DateTime($dateStr);
            return $dt->format('Y-m-d');
        } catch (Exception $e) {
            return null; // O gestisci errore
        }
    }
}