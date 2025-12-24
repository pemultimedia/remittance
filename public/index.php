<?php

// 1. ABILITA VISUALIZZAZIONE ERRORI (Solo per debug, rimuovere in produzione)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. DEFINISCI LA ROOT DEL PROGETTO
define('BASE_PATH', dirname(__DIR__));

// 3. CARICA AUTOLOADER
require BASE_PATH . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;
use Dotenv\Dotenv;

try {
    // 4. CARICA VARIABILI AMBIENTE
    if (file_exists(BASE_PATH . '/.env')) {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->load();
    } else {
        die("Errore: File .env non trovato nella root del progetto.");
    }

    // 5. AVVIA SESSIONE
    Session::start();

    // 6. CONFIGURA ROUTER
    $router = new Router();

    // --- DEFINIZIONE ROTTE ---

    // Rotta Home (/) -> Reindirizza in base al login
    $router->get('/', function() {
        if (\App\Core\Session::isLoggedIn()) {
            \App\Core\Router::redirect('/dashboard');
        } else {
            \App\Core\Router::redirect('/login');
        }
    });

    // Auth
    $router->get('/login', [\App\Controllers\AuthController::class, 'login']);
    $router->post('/login', [\App\Controllers\AuthController::class, 'authenticate']);
    $router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

	// Dashboard & Navigazione
	$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);

	// Dettagli (Drill-down)
	$router->get('/remittance', [\App\Controllers\DashboardController::class, 'viewRemittance']); // ?id=...
	$router->get('/supplier', [\App\Controllers\DashboardController::class, 'viewSupplier']);     // ?id=...
	$router->get('/document', [\App\Controllers\DashboardController::class, 'viewDocument']);     // ?id=...

	// Gestione Fornitori (Configurazione)
	$router->get('/suppliers', [\App\Controllers\SupplierController::class, 'index']);
	$router->get('/suppliers/edit', [\App\Controllers\SupplierController::class, 'edit']);
	$router->post('/suppliers/update', [\App\Controllers\SupplierController::class, 'update']);

    // 7. RISOLVI LA RICHIESTA
    $router->resolve();

} catch (Exception $e) {
    echo "<h1>Errore Critico</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}