<?php
namespace App\Controllers;
use App\Core\Session;
use App\Core\Router;
use App\Models\Supplier;

class SupplierController {
    
    public function __construct() {
        if (!Session::isLoggedIn()) {
            Router::redirect('/login');
        }
    }

    public function index() {
        $suppliers = Supplier::getAll();
        // Percorsi assoluti
        $content = BASE_PATH . '/views/suppliers/index.php';
        require BASE_PATH . '/views/layouts/main.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) Router::redirect('/suppliers');

        $supplier = Supplier::find($id);
        if (!$supplier) Router::redirect('/suppliers');

        $content = BASE_PATH . '/views/suppliers/edit.php';
        require BASE_PATH . '/views/layouts/main.php';
    }

    public function update() {
        $id = $_POST['id'] ?? null;
        if ($id) {
            Supplier::updateGoogleConfig($id, [
                'name' => $_POST['name'],
                'google_spreadsheet_id' => $_POST['google_spreadsheet_id'],
                'google_sheet_invoices' => $_POST['google_sheet_invoices'],
                'google_sheet_credit_notes' => $_POST['google_sheet_credit_notes']
            ]);
            Session::setFlash('success', 'Fornitore aggiornato con successo.');
        }
        Router::redirect('/suppliers');
    }
}