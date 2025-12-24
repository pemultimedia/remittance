<?php
namespace App\Controllers;
use App\Core\Session;
use App\Core\Router;
use App\Models\User;

class AuthController {
    public function login() {
        if (Session::isLoggedIn()) {
            Router::redirect('/dashboard');
        }
        // Usa BASE_PATH definito in index.php
        $content = BASE_PATH . '/views/auth/login.php';
        require BASE_PATH . '/views/layouts/main.php';
    }

    public function authenticate() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('role', $user['role']);
            Router::redirect('/dashboard');
        } else {
            Session::setFlash('danger', 'Credenziali non valide');
            Router::redirect('/login');
        }
    }

    public function logout() {
        Session::remove('user_id');
        Session::remove('username');
        Session::remove('role');
        Router::redirect('/login');
    }
}