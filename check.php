<?php

/**
 * CQLE Volumetria - Verificador de Instalação
 * Execute este arquivo para verificar se tudo está correto
 */

define('BASE_PATH', __DIR__);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CQLE Volumetria - Verificação de Instalação</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 40px;
      margin: 0;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    h1 {
      color: #333;
      margin-bottom: 30px;
    }

    .check-item {
      padding: 15px;
      margin: 10px 0;
      border-radius: 10px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .check-ok {
      background: #d4edda;
      border-left: 4px solid #28a745;
    }

    .check-error {
      background: #f8d7da;
      border-left: 4px solid #dc3545;
    }

    .check-warning {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
    }

    .icon {
      font-size: 24px;
      min-width: 30px;
    }

    .message {
      flex: 1;
    }

    .path {
      font-family: monospace;
      background: #f5f5f5;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 12px;
    }

    .btn {
      display: inline-block;
      padding: 15px 30px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      text-decoration: none;
      border-radius: 10px;
      margin-top: 30px;
      font-weight: 600;
    }

    .summary {
      margin-top: 30px;
      padding: 20px;
      background: #e7f3ff;
      border-radius: 10px;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>🔍 Verificação de Instalação - CQLE Volumetria</h1>

    <?php
    $errors = 0;
    $warnings = 0;
    $ok = 0;

    // Lista de arquivos necessários
    $requiredFiles = [
      'index.php' => 'Arquivo principal',
      'Controllers/VolumetriaController.php' => 'Controller principal',
      'Services/FileParserService.php' => 'Parser de arquivos',
      'Services/ServerAnalyzer.php' => 'Analisador de servidor',
      'Services/DatabaseAnalyzer.php' => 'Analisador de banco',
      'Services/BackupAnalyzer.php' => 'Analisador de backup',
      'Utils/FileValidator.php' => 'Validador de arquivos',
      'Utils/Calculator.php' => 'Calculadora',
      'views/upload.php' => 'View de upload',
      'views/results.php' => 'View de resultados',
    ];

    echo '<h2>📁 Verificando Arquivos</h2>';

    foreach ($requiredFiles as $file => $description) {
      $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . $file;
      $exists = file_exists($fullPath);

      if ($exists) {
        echo '<div class="check-item check-ok">';
        echo '<span class="icon">✅</span>';
        echo '<div class="message">';
        echo '<strong>' . $description . '</strong><br>';
        echo '<span class="path">' . $file . '</span>';
        echo '</div>';
        echo '</div>';
        $ok++;
      } else {
        echo '<div class="check-item check-error">';
        echo '<span class="icon">❌</span>';
        echo '<div class="message">';
        echo '<strong>FALTANDO: ' . $description . '</strong><br>';
        echo '<span class="path">' . $file . '</span>';
        echo '</div>';
        echo '</div>';
        $errors++;
      }
    }

    // Verifica diretórios
    echo '<h2>📂 Verificando Diretórios</h2>';

    $requiredDirs = [
      'uploads' => 'Diretório de uploads temporários',
      'assets' => 'Diretório de assets',
      'assets/images' => 'Diretório de imagens',
    ];

    foreach ($requiredDirs as $dir => $description) {
      $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . $dir;
      $exists = is_dir($fullPath);
      $writable = $exists && is_writable($fullPath);

      if ($exists && $writable) {
        echo '<div class="check-item check-ok">';
        echo '<span class="icon">✅</span>';
        echo '<div class="message">';
        echo '<strong>' . $description . '</strong><br>';
        echo '<span class="path">' . $dir . '</span> (Gravável)';
        echo '</div>';
        echo '</div>';
        $ok++;
      } elseif ($exists && !$writable) {
        echo '<div class="check-item check-warning">';
        echo '<span class="icon">⚠️</span>';
        echo '<div class="message">';
        echo '<strong>SEM PERMISSÃO: ' . $description . '</strong><br>';
        echo '<span class="path">' . $dir . '</span> (Execute: chmod 777 ' . $dir . ')';
        echo '</div>';
        echo '</div>';
        $warnings++;
      } else {
        echo '<div class="check-item check-error">';
        echo '<span class="icon">❌</span>';
        echo '<div class="message">';
        echo '<strong>FALTANDO: ' . $description . '</strong><br>';
        echo '<span class="path">' . $dir . '</span>';
        echo '</div>';
        echo '</div>';
        $errors++;
      }
    }

    // Verifica PHP
    echo '<h2>⚙️ Verificando PHP</h2>';

    $phpVersion = PHP_VERSION;
    $phpOk = version_compare($phpVersion, '7.4.0', '>=');

    if ($phpOk) {
      echo '<div class="check-item check-ok">';
      echo '<span class="icon">✅</span>';
      echo '<div class="message">';
      echo '<strong>Versão do PHP: ' . $phpVersion . '</strong><br>';
      echo 'Versão adequada (>=7.4)';
      echo '</div>';
      echo '</div>';
      $ok++;
    } else {
      echo '<div class="check-item check-error">';
      echo '<span class="icon">❌</span>';
      echo '<div class="message">';
      echo '<strong>Versão do PHP: ' . $phpVersion . '</strong><br>';
      echo 'Versão inadequada. Necessário PHP 7.4 ou superior';
      echo '</div>';
      echo '</div>';
      $errors++;
    }

    // Verifica extensões
    $requiredExtensions = ['fileinfo', 'json', 'mbstring'];

    foreach ($requiredExtensions as $ext) {
      $loaded = extension_loaded($ext);

      if ($loaded) {
        echo '<div class="check-item check-ok">';
        echo '<span class="icon">✅</span>';
        echo '<div class="message">';
        echo '<strong>Extensão ' . $ext . '</strong> carregada';
        echo '</div>';
        echo '</div>';
        $ok++;
      } else {
        echo '<div class="check-item check-error">';
        echo '<span class="icon">❌</span>';
        echo '<div class="message">';
        echo '<strong>Extensão ' . $ext . '</strong> NÃO encontrada';
        echo '</div>';
        echo '</div>';
        $errors++;
      }
    }

    // Verifica configurações do PHP
    echo '<h2>⚙️ Configurações do PHP</h2>';

    $uploadMaxFilesize = ini_get('upload_max_filesize');
    $postMaxSize = ini_get('post_max_size');
    $maxExecutionTime = ini_get('max_execution_time');

    echo '<div class="check-item ' . (intval($uploadMaxFilesize) >= 100 ? 'check-ok' : 'check-warning') . '">';
    echo '<span class="icon">' . (intval($uploadMaxFilesize) >= 100 ? '✅' : '⚠️') . '</span>';
    echo '<div class="message">';
    echo '<strong>upload_max_filesize:</strong> ' . $uploadMaxFilesize;
    if (intval($uploadMaxFilesize) < 100) {
      echo '<br><small>Recomendado: 100M ou mais (edite php.ini)</small>';
      $warnings++;
    } else {
      $ok++;
    }
    echo '</div>';
    echo '</div>';

    echo '<div class="check-item ' . (intval($postMaxSize) >= 100 ? 'check-ok' : 'check-warning') . '">';
    echo '<span class="icon">' . (intval($postMaxSize) >= 100 ? '✅' : '⚠️') . '</span>';
    echo '<div class="message">';
    echo '<strong>post_max_size:</strong> ' . $postMaxSize;
    if (intval($postMaxSize) < 100) {
      echo '<br><small>Recomendado: 100M ou mais (edite php.ini)</small>';
      $warnings++;
    } else {
      $ok++;
    }
    echo '</div>';
    echo '</div>';

    // Resumo
    echo '<div class="summary">';
    echo '<h2>📊 Resumo</h2>';
    echo '<p><strong>✅ OK:</strong> ' . $ok . '</p>';
    echo '<p><strong>⚠️ Avisos:</strong> ' . $warnings . '</p>';
    echo '<p><strong>❌ Erros:</strong> ' . $errors . '</p>';

    if ($errors === 0 && $warnings === 0) {
      echo '<p style="color: #28a745; font-weight: bold;">🎉 Tudo certo! Sistema pronto para uso.</p>';
      echo '<a href="index.php" class="btn">🚀 Acessar Sistema</a>';
    } elseif ($errors === 0) {
      echo '<p style="color: #ffc107; font-weight: bold;">⚠️ Sistema funcional, mas com alguns avisos.</p>';
      echo '<a href="index.php" class="btn">🚀 Acessar Sistema Mesmo Assim</a>';
    } else {
      echo '<p style="color: #dc3545; font-weight: bold;">❌ Corrija os erros antes de prosseguir.</p>';
    }
    echo '</div>';

    echo '<hr style="margin: 30px 0;">';
    echo '<p style="text-align: center; color: #666;">';
    echo '<strong>CQLE Softwares</strong> - Desenvolvido por Marciano Silva<br>';
    echo 'Versão 2.0.0 | ' . date('Y');
    echo '</p>';
    ?>
  </div>
</body>

</html>