<?php

// =====================================================
// JNE SoftMS - Main Entry Point
// =====================================================

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Get environment path
$root = dirname(dirname(__FILE__));

// Load environment variables
if (file_exists($root . '/.env')) {
    $env = parse_ini_file($root . '/.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Load configuration
require_once $root . '/app/config/Config.php';
require_once $root . '/app/config/Database.php';
require_once $root . '/app/helpers/Helper.php';
require_once $root . '/app/helpers/Response.php';
require_once $root . '/app/middleware/AuthMiddleware.php';
require_once $root . '/app/middleware/ValidationMiddleware.php';

// Load models (auto-load)
$modelPath = $root . '/app/models/';
if (is_dir($modelPath)) {
    foreach (glob($modelPath . '*.php') as $file) {
        require_once $file;
    }
}

// =====================================================
// Router Configuration
// =====================================================

$request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

// Remove base path
$base_path = strtolower('jne-sofms-main');
if (in_array($base_path, $request_uri)) {
    $key = array_search($base_path, $request_uri);
    array_splice($request_uri, 0, $key + 2); // Remove base path and 'public'
}

$action = isset($request_uri[0]) && !empty($request_uri[0]) ? $request_uri[0] : 'dashboard';
$method = isset($request_uri[1]) ? $request_uri[1] : 'index';

// =====================================================
// Authentication Check
// =====================================================

$public_pages = ['login', 'logout', 'api'];
$auth_required = !in_array($action, $public_pages);

if ($auth_required) {
    \App\Middleware\AuthMiddleware::session();
    \App\Middleware\AuthMiddleware::guard();
}

// =====================================================
// Route Handler
// =====================================================

try {
    switch ($action) {
        // ===== PUBLIC ROUTES =====
        case 'login':
            require_once $root . '/app/controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLogin();
            }
            break;

        case 'logout':
            require_once $root . '/app/controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->logout();
            break;

        // ===== PROTECTED ROUTES =====
        case 'dashboard':
            require_once $root . '/app/controllers/DashboardController.php';
            $controller = new \App\Controllers\DashboardController();
            
            switch ($method) {
                case 'index':
                default:
                    $controller->index();
                    break;
                case 'summary':
                    $controller->summary();
                    break;
            }
            break;

        case 'opening-cash':
            require_once $root . '/app/controllers/OpeningCashController.php';
            $controller = new \App\Controllers\OpeningCashController();
            
            switch ($method) {
                case 'index':
                default:
                    $controller->index();
                    break;
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->store();
                    } else {
                        $controller->create();
                    }
                    break;
                case 'show':
                    $id = $request_uri[2] ?? null;
                    $controller->show($id);
                    break;
                case 'close':
                    $id = $request_uri[2] ?? null;
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->close($id);
                    }
                    break;
            }
            break;

        case 'income':
            require_once $root . '/app/controllers/IncomeController.php';
            $controller = new \App\Controllers\IncomeController();
            
            switch ($method) {
                case 'index':
                default:
                    $controller->index();
                    break;
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->store();
                    } else {
                        $controller->create();
                    }
                    break;
                case 'edit':
                    $id = $request_uri[2] ?? null;
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->update($id);
                    } else {
                        $controller->edit($id);
                    }
                    break;
                case 'delete':
                    $id = $request_uri[2] ?? null;
                    $controller->delete($id);
                    break;
            }
            break;

        case 'expense':
            require_once $root . '/app/controllers/ExpenseController.php';
            $controller = new \App\Controllers\ExpenseController();
            
            switch ($method) {
                case 'index':
                default:
                    $controller->index();
                    break;
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->store();
                    } else {
                        $controller->create();
                    }
                    break;
                case 'edit':
                    $id = $request_uri[2] ?? null;
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->update($id);
                    } else {
                        $controller->edit($id);
                    }
                    break;
                case 'approve':
                    $id = $request_uri[2] ?? null;
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->approve($id);
                    }
                    break;
                case 'reject':
                    $id = $request_uri[2] ?? null;
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->reject($id);
                    }
                    break;
                case 'delete':
                    $id = $request_uri[2] ?? null;
                    $controller->delete($id);
                    break;
            }
            break;

        case 'report':
            require_once $root . '/app/controllers/ReportController.php';
            $controller = new \App\Controllers\ReportController();
            
            switch ($method) {
                case 'daily':
                    $controller->daily();
                    break;
                case 'weekly':
                    $controller->weekly();
                    break;
                case 'monthly':
                    $controller->monthly();
                    break;
                case 'export':
                    $type = $request_uri[2] ?? 'pdf';
                    $period = $request_uri[3] ?? 'daily';
                    $controller->export($type, $period);
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'settings':
            require_once $root . '/app/controllers/SettingController.php';
            $controller = new \App\Controllers\SettingController();
            
            switch ($method) {
                case 'users':
                    $controller->users();
                    break;
                case 'categories':
                    $controller->categories();
                    break;
                case 'payment-methods':
                    $controller->paymentMethods();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'profile':
            require_once $root . '/app/controllers/ProfileController.php';
            $controller = new \App\Controllers\ProfileController();
            
            switch ($method) {
                case 'edit':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->update();
                    } else {
                        $controller->edit();
                    }
                    break;
                case 'change-password':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->changePassword();
                    } else {
                        $controller->showChangePassword();
                    }
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        // ===== API ROUTES =====
        case 'api':
            header('Content-Type: application/json');
            
            $endpoint = $method;
            $submethod = $request_uri[2] ?? null;
            
            require_once $root . '/app/controllers/ApiController.php';
            $controller = new \App\Controllers\ApiController();
            $controller->handle($endpoint, $submethod);
            break;

        // ===== DEFAULT (404) =====
        default:
            header("HTTP/1.0 404 Not Found");
            echo json_encode([
                'status' => 'error',
                'message' => 'Route not found: ' . $action
            ]);
            break;
    }
} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
