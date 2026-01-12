<?php

/**
 * CQLE Volumetria - Script de Instalação
 * Execute este arquivo APENAS UMA VEZ para criar a estrutura
 */

define('BASE_PATH', __DIR__);

// Cria diretórios necessários
$directories = [
  'Controllers',
  'Services',
  'Utils',
  'views',
  'assets',
  'assets/css',
  'assets/js',
  'assets/images',
  'uploads',
];

echo "🚀 Instalando CQLE Volumetria...\n\n";

foreach ($directories as $dir) {
  $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . $dir;

  if (!is_dir($fullPath)) {
    if (mkdir($fullPath, 0755, true)) {
      echo "✅ Criado: $dir\n";
    } else {
      echo "❌ ERRO ao criar: $dir\n";
    }
  } else {
    echo "ℹ️  Já existe: $dir\n";
  }
}

// Tenta dar permissão de escrita em uploads
$uploadsPath = BASE_PATH . DIRECTORY_SEPARATOR . 'uploads';
if (is_dir($uploadsPath)) {
  if (chmod($uploadsPath, 0777)) {
    echo "✅ Permissões configuradas para: uploads\n";
  } else {
    echo "⚠️  Execute manualmente: chmod 777 uploads\n";
  }
}

echo "\n✨ Instalação concluída!\n\n";
echo "📋 Próximos passos:\n";
echo "1. Copie todos os arquivos PHP para suas respectivas pastas\n";
echo "2. Execute check.php para verificar a instalação\n";
echo "3. Acesse index.php para usar o sistema\n\n";
echo "🏆 Desenvolvido por Marciano Silva - CQLE Softwares\n";
