<?php

namespace App\Services;

use PhpImap\Mailbox;
use PhpImap\Exceptions\ConnectionException;
use Exception;

class ImapService
{
    private Mailbox $mailbox;
    private string $searchSubject = 'Remittance Advice - ON LINE PLATFORM FOR';

    public function __construct()
    {
        // Recupera credenziali dal file .env
        $host = $_ENV['IMAP_HOST'] ?? '{imap.gmail.com:993/imap/ssl}INBOX';
        $user = $_ENV['IMAP_USER'];
        $password = $_ENV['IMAP_PASSWORD'];

        try {
            $this->mailbox = new Mailbox(
                $host,
                $user,
                $password,
                __DIR__ . '/../../logs/attachments', // Cartella temp per allegati (se servissero)
                'UTF-8'
            );
        } catch (ConnectionException $e) {
            throw new Exception("Errore connessione IMAP: " . $e->getMessage());
        }
    }

    /**
     * Cerca le nuove email basandosi sulla data dell'ultima elaborazione
     * o cerca tra le non lette se non c'è una data.
     * 
     * @param string|null $sinceDate Formato 'd-M-Y' (es. 24-Dec-2025) richiesto da IMAP
     */
    public function fetchRemittanceEmails(?string $sinceDate = null): array
    {
        // Costruisci la query di ricerca
        // SUBJECT "..." cerca nel soggetto
        // SINCE "..." cerca messaggi da una certa data in poi
        $searchQuery = 'SUBJECT "' . $this->searchSubject . '"';
        
        if ($sinceDate) {
            $searchQuery .= ' SINCE "' . $sinceDate . '"';
        }

        // Recupera gli ID delle email
        $mailIds = $this->mailbox->searchMailbox($searchQuery);

        if (!$mailIds) {
            return [];
        }

        $emails = [];
        foreach ($mailIds as $id) {
            // Scarica l'oggetto email completo
            $mail = $this->mailbox->getMail($id, false); // false = non segnare come letto subito
            
            // Preferiamo la versione HTML perché le tabelle sono più facili da parsare
            // Se non c'è HTML, prendiamo il testo piano
            $content = $mail->textHtml ?: $mail->textPlain;

            $emails[] = [
                'message_id' => $mail->messageId, // ID univoco header
                'subject' => $mail->subject,
                'date' => $mail->date, // Data ricezione
                'from' => $mail->fromAddress,
                'body' => $content
            ];
        }

        return $emails;
    }
}