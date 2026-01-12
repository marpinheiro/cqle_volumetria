<?php

/**
 * CQLE Softwares - Volumetria Oracle
 * Desenvolvido por: Marciano Silva
 * 
 * Ponto de entrada da aplicação
 */

// Inicia sessão
session_start();

// Define constantes
define('BASE_PATH', __DIR__);
define('APP_NAME', 'CQLE Volumetria');
define('APP_VERSION', '2.0.0');
define('APP_AUTHOR', 'Marciano Silva - CQLE Softwares');

// Configura timezone
date_default_timezone_set('America/Sao_Paulo');

// Error reporting para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader CORRIGIDO para Windows/XAMPP
spl_autoload_register(function ($class) {
    // Remove namespace base se houver
    $class = ltrim($class, '\\');

    // Converte namespace para caminho de arquivo
    // Exemplo: Controllers\VolumetriaController -> Controllers/VolumetriaController.php
    $file = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    // Debug (remova depois que funcionar)
    // echo "Tentando carregar: $file<br>";

    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    return false;
});

// Importa classes necessárias
use Controllers\VolumetriaController;

try {
    $controller = new VolumetriaController();

    // Roteamento simples
    $action = $_GET['action'] ?? 'upload';

    switch ($action) {
        case 'upload':
            $controller->showUploadPage();
            break;

        case 'process':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['volumetria_file'])) {
                $result = $controller->processFile($_FILES['volumetria_file']);

                // Exportar JSON se solicitado
                if (isset($_POST['export']) && $_POST['export'] === 'json') {
                    $controller->exportJSON($result);
                } else {
                    $controller->showResults($result);
                }
            } else {
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
    // Em caso de erro, exibe mensagem amigável
    $_SESSION['error'] = $e->getMessage();

    // Se for erro de classe não encontrada, dá mais detalhes
    if (strpos($e->getMessage(), 'not found') !== false) {
        $_SESSION['error'] .= ' - Verifique se todos os arquivos foram copiados corretamente.';
    }

    header('Location: index.php?action=upload');
}
