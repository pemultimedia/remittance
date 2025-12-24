<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve() {
        // Ottieni il percorso richiesto
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // GESTIONE SOTTOCARTELLE (Importante se non sei su un dominio root)
        // Se lo script è in /remittance/public/index.php, dobbiamo rimuovere /remittance/public dall'URI
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        
        // Normalizza i percorsi (sostituisci backslash con slash per Windows)
        $requestUri = str_replace('\\', '/', $requestUri);
        $scriptName = str_replace('\\', '/', $scriptName);

        // Rimuovi la base path dell'applicazione dall'URI richiesto
        if (strpos($requestUri, $scriptName) === 0) {
            $path = substr($requestUri, strlen($scriptName));
        } else {
            $path = $requestUri;
        }

        // Assicurati che il path inizi con / e non finisca con / (tranne se è solo /)
        $path = '/' . trim($path, '/');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            // DEBUG: Decommenta queste righe se vedi ancora 404 per capire cosa sta leggendo il router
            // echo "Method: $method <br>";
            // echo "Request URI: $requestUri <br>";
            // echo "Script Name: $scriptName <br>";
            // echo "Calculated Path: $path <br>";
            // echo "Available Routes: <pre>" . print_r(array_keys($this->routes[$method]), true) . "</pre>";
            
            http_response_code(404);
            echo "404 - Pagina non trovata";
            return;
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $action = $callback[1];
            call_user_func([$controller, $action]);
        } elseif (is_callable($callback)) {
            call_user_func($callback);
        }
    }
    
    public static function redirect($path) {
        header("Location: $path");
        exit;
    }
}