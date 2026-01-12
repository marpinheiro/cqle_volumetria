<?php

$arquivo = __DIR__ . '/Services/FileParserService.php';

echo "<pre>";
echo "Caminho completo que o PHP está procurando:\n";
echo $arquivo . "\n\n";

if (file_exists($arquivo)) {
  echo "O arquivo EXISTE!\n";
  echo "Tamanho: " . filesize($arquivo) . " bytes\n";
} else {
  echo "O arquivo NÃO existe nesse caminho.\n";
  echo "Possíveis problemas:\n";
  echo " - Pasta errada\n";
  echo " - Nome do arquivo diferente (maiúscula/minúscula)\n";
  echo " - Extensão oculta ou errada (.txt, .ph, etc)\n";
}

echo "</pre>";
