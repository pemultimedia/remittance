-- Abilita estensione per UUID se volessimo usarli, ma qui usiamo SERIAL per semplicità
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Tabella Utenti (Login Dashboard)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    username VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')), -- 'admin' configura, 'user' visualizza
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabella Fornitori (Suppliers)
-- Si autoalimenta, ma i campi Google Sheets vanno configurati dall'admin
CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    amazon_supplier_site_name VARCHAR(100) UNIQUE NOT NULL, -- Chiave univoca dalla mail (es. W982O_EU_IT)
    amazon_supplier_no VARCHAR(50), -- "Our Supplier No"
    name VARCHAR(255), -- Nome leggibile (es. "ON LINE PLATFORM FOR SHOPS SRL")
    currency VARCHAR(3) DEFAULT 'EUR',
    
    -- Configurazione Google Sheets
    google_spreadsheet_id VARCHAR(255), -- ID del foglio Google (preso dall'URL)
    google_sheet_invoices VARCHAR(100), -- Nome del tab per le fatture (es. "Fatture")
    google_sheet_credit_notes VARCHAR(100), -- Nome del tab per le note credito (es. "Note Credito")
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabella di associazione Utenti <-> Fornitori
CREATE TABLE user_suppliers (
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    supplier_id INTEGER REFERENCES suppliers(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, supplier_id)
);

-- 4. Log delle Email Processate
-- Serve per evitare di rileggere la stessa email e per tracciare l'ultima letta
CREATE TABLE processed_emails (
    id SERIAL PRIMARY KEY,
    message_id VARCHAR(255) UNIQUE NOT NULL, -- ID univoco dell'header email
    supplier_id INTEGER REFERENCES suppliers(id),
    subject VARCHAR(255),
    received_at TIMESTAMP NOT NULL,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'success', -- 'success', 'error'
    error_log TEXT -- Dettaglio errore se fallisce il parsing
);

-- 5. Documenti (Fatture e Note di Credito)
-- Questa tabella rappresenta l'entità "Fattura" o "Nota Credito" citata nella riga della mail
CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    supplier_id INTEGER REFERENCES suppliers(id) NOT NULL,
    document_number VARCHAR(100) NOT NULL, -- "Invoice Number" dalla mail
    document_date DATE, -- "Invoice Date"
    description VARCHAR(255), -- "Invoice Description"
    type VARCHAR(20) CHECK (type IN ('INVOICE', 'CREDIT_NOTE', 'ADJUSTMENT')), -- Dedotto dal contesto o importo
    
    -- Campi di controllo per evitare duplicati logici
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(supplier_id, document_number) -- Un fornitore non può avere due documenti con lo stesso numero
);

-- 6. Transazioni (Dettaglio Pagamenti/Storni)
-- Ogni riga della tabella nella mail genera una transazione.
-- Una fattura può ricevere più pagamenti nel tempo o storni parziali.
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES documents(id), -- Collegamento al documento (se matchato)
    processed_email_id INTEGER REFERENCES processed_emails(id), -- Collegamento alla fonte
    
    amount DECIMAL(15, 2) NOT NULL, -- Importo della riga (Positivo o Negativo)
    payment_date DATE NOT NULL, -- "Payment Date" dall'header della mail
    
    raw_data JSONB, -- Salviamo l'intera riga parsata come JSON per sicurezza
    
    -- Dettagli specifici estratti
    discount_taken DECIMAL(15, 2) DEFAULT 0,
    amount_paid DECIMAL(15, 2) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indici per velocizzare la dashboard
CREATE INDEX idx_transactions_date ON transactions(payment_date);
CREATE INDEX idx_documents_supplier ON documents(supplier_id);
CREATE INDEX idx_processed_emails_msgid ON processed_emails(message_id);


-- La password hashata qui sotto corrisponde a: 9<e42TtxP2_q
INSERT INTO users (email, password_hash, username, role)
VALUES (
    'admin@example.com', 
    '$2y$10$YuNJytib20S6d0wq.sgW.ut2QfSGpXEhzA1SCefVSBeDQE3X3GTQi', 
    'Admin', 
    'admin'
);
