<?php
namespace App\Controllers;
use App\Core\Session;
use App\Core\Router;
use App\Models\Remittance;
use App\Models\Document;
use App\Models\Supplier;

class DashboardController {
    
    public function __construct() {
        if (!Session::isLoggedIn()) {
            Router::redirect('/login');
        }
    }

    // HOME DASHBOARD
    public function index() {
        $endDate = $_GET['end'] ?? date('Y-m-d');
        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
        
        $userId = Session::get('user_id');
        $role = Session::get('role');

        $remittances = Remittance::getList($userId, $role, $startDate, $endDate);

        $content = BASE_PATH . '/views/dashboard/index.php';
        require BASE_PATH . '/views/layouts/main.php';
    }

    // DETTAGLIO REMITTANCE
    public function viewRemittance() {
        $id = $_GET['id'] ?? null;
        if (!$id) Router::redirect('/dashboard');

        $userId = Session::get('user_id');
        $role = Session::get('role');

        // Passiamo userId e role per il controllo accessi
        $data = Remittance::getDetails($id, $userId, $role);

        // Se non troviamo i metadati, significa che l'ID non esiste o l'utente non ha permessi
        if (!$data || empty($data['meta'])) {
            Session::setFlash('danger', 'Accesso negato o elemento non trovato.');
            Router::redirect('/dashboard');
            return;
        }

        $meta = $data['meta'];
        $items = $data['items'];

        $content = BASE_PATH . '/views/dashboard/remittance.php';
        require BASE_PATH . '/views/layouts/main.php';
    }

    // DETTAGLIO DOCUMENTO
    public function viewDocument() {
        $id = $_GET['id'] ?? null;
        if (!$id) Router::redirect('/dashboard');

        $userId = Session::get('user_id');
        $role = Session::get('role');

        $data = Document::getHistory($id, $userId, $role);

        if (!$data || empty($data['doc'])) {
            Session::setFlash('danger', 'Accesso negato o documento non trovato.');
            Router::redirect('/dashboard');
            return;
        }

        $doc = $data['doc'];
        $history = $data['history'];

        $content = BASE_PATH . '/views/dashboard/document.php';
        require BASE_PATH . '/views/layouts/main.php';
    }

    // DETTAGLIO FORNITORE
    public function viewSupplier() {
        $id = $_GET['id'] ?? null;
        if (!$id) Router::redirect('/dashboard');

        $endDate = $_GET['end'] ?? date('Y-m-d');
        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-90 days'));
        
        $userId = Session::get('user_id');
        $role = Session::get('role');

        // Verifica accesso fornitore
        $supplier = Supplier::find($id, $userId, $role);

        if (!$supplier) {
            Session::setFlash('danger', 'Accesso negato o fornitore non trovato.');
            Router::redirect('/dashboard');
            return;
        }

        // Recupera documenti (il controllo accesso è implicito se siamo arrivati qui, ma lo passiamo per sicurezza)
        $documents = Document::getBySupplier($id, $startDate, $endDate);

        $content = BASE_PATH . '/views/dashboard/supplier.php';
        require BASE_PATH . '/views/layouts/main.php';
    }
}