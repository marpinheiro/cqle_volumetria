<?php

/**
 * CQLE Softwares - Volumetria Oracle
 * Desenvolvido por: Marciano Silva
 */

session_start();

define('BASE_PATH', __DIR__);
define('APP_NAME', 'CQLE Volumetria');
define('APP_VERSION', '2.0.0');
define('APP_AUTHOR', 'Marciano Silva - CQLE Softwares');

date_default_timezone_set('America/Sao_Paulo');

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $file = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    return false;
});

use Controllers\VolumetriaController;

try {
    $controller = new VolumetriaController();

    $action = $_GET['action'] ?? 'upload';

    switch ($action) {
        case 'upload':
            $controller->showUploadPage();
            break;

        case 'process':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['volumetria_file'])) {
                $result = $controller->processFile($_FILES['volumetria_file']);

                if (isset($_POST['export']) && $_POST['export'] === 'json') {
                    $controller->exportJSON($result);
                } else {
                    $controller->showResults($result);
                }
            } else {
                header('Location: index.php');
            }
            break;

        case 'volumetria':
            // NOVA ROTA: Estudo de Volumetria
            if (isset($_SESSION['last_result'])) {
                $controller->showVolumetryStudy($_SESSION['last_result']);
            } else {
                $_SESSION['error'] = 'Nenhum resultado disponível. Faça uma análise primeiro.';
                header('Location: index.php');
            }
            break;

        case 'export':
            if (isset($_SESSION['last_result'])) {
                $format = $_GET['format'] ?? 'json';
                $controller->export($_SESSION['last_result'], $format);
            } else {
                $_SESSION['error'] = 'Nenhum resultado disponível para exportação';
                header('Location: index.php');
            }
            break;

        default:
            header('Location: index.php');
            break;
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();

    if (strpos($e->getMessage(), 'not found') !== false) {
        $_SESSION['error'] .= ' - Verifique se todos os arquivos foram copiados corretamente.';
    }

    header('Location: index.php?action=upload');
}
