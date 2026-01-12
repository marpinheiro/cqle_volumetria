<?php

/**
 * ARQUIVO DE DEBUG - Coloque na raiz do projeto
 * Acesse: http://localhost/CQLE_Volumetria/debug.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ajuste o caminho se necessário
define('BASE_PATH', __DIR__);

// Autoloader
spl_autoload_register(function ($class) {
  $class = ltrim($class, '\\');
  $file = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
  if (file_exists($file)) {
    require_once $file;
    return true;
  }
  return false;
});

use Services\FileParserService;
use Services\DatabaseAnalyzer;
use Services\BackupAnalyzer;

// ⚠️ ALTERE AQUI O CAMINHO DO ARQUIVO - USE BARRA NORMAL /
$filePath = 'C:/xampp1/htdocs/CQLE_Volumetria/uploads/2706017_Gricki_dbsrv02_3.txt';

// Ou tente automaticamente procurar na pasta uploads
if (!file_exists($filePath)) {
  $uploadDir = BASE_PATH . '/uploads/';
  if (is_dir($uploadDir)) {
    $files = glob($uploadDir . '*.txt');
    if (!empty($files)) {
      $filePath = $files[0]; // Pega o primeiro arquivo .txt
      echo "<div style='background:#fff3cd;padding:10px;margin:10px;border-left:4px solid #ffc107;'>";
      echo "⚠️ Usando arquivo encontrado automaticamente: <strong>" . basename($filePath) . "</strong>";
      echo "</div>";
    }
  }
}

if (!file_exists($filePath)) {
  echo "<div style='background:#f8d7da;color:#721c24;padding:20px;margin:20px;border-left:4px solid #dc3545;font-family:monospace;'>";
  echo "<h2>❌ ERRO: Arquivo não encontrado!</h2>";
  echo "<p><strong>Caminho procurado:</strong> $filePath</p>";
  echo "<hr>";
  echo "<h3>📁 Arquivos disponíveis na pasta uploads:</h3>";

  $uploadDir = BASE_PATH . '/uploads/';
  if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    echo "<ul>";
    foreach ($files as $file) {
      if ($file !== '.' && $file !== '..') {
        echo "<li>$file</li>";
      }
    }
    echo "</ul>";
  } else {
    echo "<p style='color:red;'>Pasta uploads não existe!</p>";
  }

  echo "<hr>";
  echo "<h3>🔧 Como corrigir:</h3>";
  echo "<ol>";
  echo "<li>Copie um dos arquivos acima</li>";
  echo "<li>Edite a linha 28 do debug.php</li>";
  echo "<li>Cole o nome do arquivo correto</li>";
  echo "</ol>";
  echo "<p><strong>Exemplo:</strong></p>";
  echo "<pre style='background:#1a1a1a;color:#00ff00;padding:10px;'>";
  echo "\$filePath = 'C:/xampp1/htdocs/CQLE_Volumetria/uploads/SEU_ARQUIVO.txt';";
  echo "</pre>";
  echo "</div>";
  die();
}

echo "<style>
body { background:#1a1a1a; margin:0; padding:20px; font-family:monospace; }
.debug { background:#0d1117; color:#00ff00; padding:20px; border-radius:8px; }
.section { border:2px solid #30363d; margin:20px 0; padding:15px; border-radius:8px; }
.success { color:#00ff00; }
.error { color:#ff4444; }
.warning { color:#ffaa00; }
.info { color:#00aaff; }
h2 { color:#58a6ff; border-bottom:2px solid #30363d; padding-bottom:10px; }
pre { background:#161b22; padding:10px; border-radius:4px; overflow-x:auto; }
</style>";

echo "<div class='debug'>";
echo "<h1 style='color:#58a6ff;text-align:center;'>🔍 DEBUG - CQLE VOLUMETRIA</h1>";
echo "<hr style='border-color:#30363d;'>";

$parser = new FileParserService();
$parsed = $parser->parseFile($filePath);

echo "<div class='section'>";
echo "<h2>📁 ARQUIVO PARSEADO</h2>";
echo "<p class='success'>✅ Arquivo lido com sucesso!</p>";
echo "<p><strong>Caminho:</strong> $filePath</p>";
echo "<p><strong>Tamanho:</strong> " . filesize($filePath) . " bytes</p>";
echo "</div>";

// ==========================================
// SERVIDOR
// ==========================================
echo "<div class='section'>";
echo "<h2>🖥️ SEÇÃO: SERVIDOR</h2>";
echo "<p><strong>Tamanho:</strong> " . strlen($parsed['servidor']) . " caracteres</p>";
echo "<p><strong>Primeiros 500 caracteres:</strong></p>";
echo "<pre>" . htmlspecialchars(substr($parsed['servidor'], 0, 500)) . "\n...</pre>";
echo "</div>";

// ==========================================
// BANCO
// ==========================================
echo "<div class='section'>";
echo "<h2>🗄️ SEÇÃO: BANCO</h2>";
if (is_array($parsed['banco'])) {
  echo "<p class='success'>✅ É um array com " . count($parsed['banco']) . " seções</p>";

  foreach ($parsed['banco'] as $idx => $banco) {
    echo "<div style='border-left:3px solid #58a6ff;padding-left:15px;margin:15px 0;'>";
    echo "<h3 class='info'>--- BANCO #" . ($idx + 1) . " ---</h3>";
    echo "<p><strong>Tamanho:</strong> " . strlen($banco) . " caracteres</p>";
    echo "<p><strong>Primeiros 400 caracteres:</strong></p>";
    echo "<pre>" . htmlspecialchars(substr($banco, 0, 400)) . "</pre>";

    // Testa detecção de nome
    if (preg_match('/Nome\s+Inst[âaà]ncia\s*:\s*(\S+)/iu', $banco, $m)) {
      echo "<p class='success'>✅ <strong>Nome detectado:</strong> " . $m[1] . "</p>";
    } else {
      echo "<p class='error'>❌ Nome NÃO detectado</p>";
    }

    // Testa detecção de tipo
    if (preg_match('/^Tipo\s*:\s*(.+)$/im', $banco, $m)) {
      echo "<p class='success'>✅ <strong>Tipo detectado:</strong> " . trim($m[1]) . "</p>";
    } else {
      echo "<p class='error'>❌ Tipo NÃO detectado</p>";
    }

    echo "</div>";
  }
} else {
  echo "<p class='error'>❌ NÃO É ARRAY!</p>";
  echo "<p><strong>Tipo:</strong> " . gettype($parsed['banco']) . "</p>";
  echo "<pre>" . htmlspecialchars(substr($parsed['banco'], 0, 400)) . "</pre>";
}
echo "</div>";

// ==========================================
// BACKUP
// ==========================================
echo "<div class='section'>";
echo "<h2>💾 SEÇÃO: BACKUP</h2>";
if (is_array($parsed['backup'])) {
  echo "<p class='success'>✅ É um array com " . count($parsed['backup']) . " seções</p>";

  foreach ($parsed['backup'] as $idx => $backup) {
    echo "<div style='border-left:3px solid #ffaa00;padding-left:15px;margin:15px 0;'>";
    echo "<h3 class='warning'>--- BACKUP #" . ($idx + 1) . " ---</h3>";
    echo "<p><strong>Tamanho:</strong> " . strlen($backup) . " caracteres</p>";
    echo "<p><strong>Primeiros 400 caracteres:</strong></p>";
    echo "<pre>" . htmlspecialchars(substr($backup, 0, 400)) . "</pre>";
    echo "</div>";
  }
} else {
  echo "<p class='error'>❌ NÃO É ARRAY!</p>";
  echo "<p><strong>Tipo:</strong> " . gettype($parsed['backup']) . "</p>";
  if (empty($parsed['backup'])) {
    echo "<p class='error'>⚠️ VAZIO!</p>";
  } else {
    echo "<pre>" . htmlspecialchars(substr($parsed['backup'], 0, 400)) . "</pre>";
  }
}
echo "</div>";

// ==========================================
// TESTA ANALYZERS
// ==========================================
echo "<div class='section'>";
echo "<h2>🔍 TESTANDO ANALYZERS</h2>";

$dbAnalyzer = new DatabaseAnalyzer();
$resultBanco = $dbAnalyzer->analyze($parsed['banco']);

echo "<div style='border:2px solid #00ff00;padding:15px;margin:15px 0;border-radius:8px;'>";
echo "<h3 class='success'>📊 DATABASE ANALYZER</h3>";
echo "<p><strong>Status:</strong> " . $resultBanco['status'] . "</p>";
echo "<p><strong>Qtd Instâncias:</strong> " . $resultBanco['qtd_instancias'] . "</p>";

if (!empty($resultBanco['instancias'])) {
  foreach ($resultBanco['instancias'] as $idx => $inst) {
    echo "<div style='background:#161b22;padding:10px;margin:10px 0;border-radius:4px;'>";
    echo "<h4>Instância #" . ($idx + 1) . "</h4>";
    echo "<ul>";
    echo "<li><strong>Nome:</strong> " . $inst['instancia'] . "</li>";
    echo "<li><strong>Tipo:</strong> " . $inst['tipo'] . "</li>";
    echo "<li><strong>Tamanho Total:</strong> " . $inst['tamanho_total_gb'] . " GB</li>";
    echo "<li><strong>Crescimento:</strong> " . $inst['crescimento'] . " GB/mês</li>";
    echo "<li><strong>Archives:</strong> " . $inst['geracao_archives_formatted'] . "</li>";
    echo "<li><strong>Datafiles:</strong> " . $inst['tamanho_datafiles_gb'] . " GB</li>";
    echo "</ul>";
    echo "</div>";
  }
} else {
  echo "<p class='error'>❌ Nenhuma instância detectada!</p>";
}
echo "</div>";

$backupAnalyzer = new BackupAnalyzer();
$resultBackup = $backupAnalyzer->analyze($parsed['backup']);

echo "<div style='border:2px solid #ffaa00;padding:15px;margin:15px 0;border-radius:8px;'>";
echo "<h3 class='warning'>💾 BACKUP ANALYZER</h3>";
echo "<p><strong>Status:</strong> " . $resultBackup['status'] . "</p>";
echo "<p><strong>Total Backups:</strong> " . $resultBackup['total_backups'] . "</p>";
echo "<p><strong>Tipos Detectados:</strong> " . $resultBackup['tipos_detectados'] . "</p>";

if (!empty($resultBackup['backups'])) {
  foreach ($resultBackup['backups'] as $idx => $bkp) {
    echo "<div style='background:#161b22;padding:10px;margin:10px 0;border-radius:4px;'>";
    echo "<h4>Backup #" . ($idx + 1) . "</h4>";
    echo "<ul>";
    echo "<li><strong>Tipo:</strong> " . $bkp['tipo'] . "</li>";
    echo "<li><strong>Tamanho:</strong> " . $bkp['tamanho_formatado'] . "</li>";
    echo "<li><strong>Diretório:</strong> " . $bkp['diretorio'] . "</li>";
    echo "<li><strong>Horário:</strong> " . $bkp['horario_inicio'] . "</li>";
    echo "<li><strong>Duração:</strong> " . $bkp['duracao_media'] . "</li>";
    echo "</ul>";
    echo "</div>";
  }
} else {
  echo "<p class='error'>❌ Nenhum backup detectado!</p>";
}
echo "</div>";

echo "</div>";

echo "<div style='text-align:center;margin-top:30px;padding:20px;border-top:2px solid #30363d;'>";
echo "<p style='color:#58a6ff;'>✅ FIM DO DEBUG</p>";
echo "</div>";

echo "</div>";
