<?php

namespace Services;

class FileParserService
{
    private $fileContent;
    private $sections = [];

    public function parseFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Arquivo não encontrado: {$filePath}");
        }

        // Lê o arquivo e converte para UTF-8
        $content = file_get_contents($filePath);
        
        // Detecta encoding e converte para UTF-8
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }
        
        $this->fileContent = $content;
        $this->extractSections();
        $this->sections['format'] = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'txt';

        return $this->sections;
    }

    private function extractSections(): void
    {
        $content = $this->fileContent;

        // ==========================================
        // SERVIDOR
        // ==========================================
        $posServidor = stripos($content, 'SERVIDOR');
        $posPrimeiroBanco = stripos($content, 'BANCO');
        
        if ($posServidor !== false && $posPrimeiroBanco !== false) {
            // Pula a linha do delimitador
            $start = strpos($content, "\n", $posServidor) + 1;
            $end = $posPrimeiroBanco;
            
            // Volta até encontrar a linha do delimitador BANCO
            while ($end > 0 && $content[$end-1] !== '#') {
                $end--;
            }
            while ($end > 0 && $content[$end-1] === '#') {
                $end--;
            }
            
            $this->sections['servidor'] = trim(substr($content, $start, $end - $start));
        } else {
            $this->sections['servidor'] = '';
        }

        // ==========================================
        // BANCO (múltiplas)
        // ==========================================
        $bancos = [];
        $pattern = '/#{30,}BANCO#{30,}/i';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        
        if (!empty($matches[0])) {
            foreach ($matches[0] as $idx => $match) {
                $start = $match[1] + strlen($match[0]);
                
                // Pula para próxima linha
                $start = strpos($content, "\n", $start) + 1;
                
                // Encontra fim (próximo BANCO ou BACKUP)
                $nextMatch = $matches[0][$idx + 1] ?? null;
                $end = $nextMatch ? $nextMatch[1] : strlen($content);
                
                // Verifica se tem BACKUP antes
                $backupPos = stripos($content, 'BACKUP', $start);
                if ($backupPos !== false && $backupPos < $end) {
                    // Volta até o delimitador
                    while ($backupPos > 0 && $content[$backupPos-1] !== '#') {
                        $backupPos--;
                    }
                    while ($backupPos > 0 && $content[$backupPos-1] === '#') {
                        $backupPos--;
                    }
                    $end = $backupPos;
                }
                
                $bancoContent = trim(substr($content, $start, $end - $start));
                if (!empty($bancoContent) && strlen($bancoContent) > 10) {
                    $bancos[] = $bancoContent;
                }
            }
        }
        
        $this->sections['banco'] = $bancos;

        // ==========================================
        // BACKUP (múltiplas)
        // ==========================================
        $backups = [];
        $pattern = '/#{30,}BACKUP/i';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        
        if (!empty($matches[0])) {
            foreach ($matches[0] as $idx => $match) {
                $start = $match[1] + strlen($match[0]);
                
                // Pula para próxima linha
                $start = strpos($content, "\n", $start) + 1;
                
                // Encontra fim
                $nextMatch = $matches[0][$idx + 1] ?? null;
                $end = $nextMatch ? $nextMatch[1] : strlen($content);
                
                // Verifica se tem BANCO antes
                $bancoPos = stripos($content, 'BANCO', $start);
                if ($bancoPos !== false && $bancoPos < $end) {
                    while ($bancoPos > 0 && $content[$bancoPos-1] !== '#') {
                        $bancoPos--;
                    }
                    while ($bancoPos > 0 && $content[$bancoPos-1] === '#') {
                        $bancoPos--;
                    }
                    $end = $bancoPos;
                }
                
                $backupContent = trim(substr($content, $start, $end - $start));
                if (!empty($backupContent) && strlen($backupContent) > 10) {
                    $backups[] = $backupContent;
                }
            }
        }
        
        $this->sections['backup'] = $backups;
    }

    public function parseSystemInfo(string $content): array
    {
        $data = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function parseDatabaseGrowth(string $content): array
    {
        $databases = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                $dbName = trim($matches[1]);
                $values = array_map('trim', explode(',', $matches[2]));

                $monthlyData = array_map(function ($val) {
                    return is_numeric($val) ? floatval($val) : 0;
                }, $values);

                $databases[$dbName] = $monthlyData;
            }
        }

        return $databases;
    }

    public function parseArchiveInfo(string $content): array
    {
        $archives = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (preg_match('/archive\s+(\/\w+)\s+\[([^\]]+)\]/', $line, $matches)) {
                $location = trim($matches[1]);
                $values = array_map('trim', explode(',', $matches[2]));
                $numericValues = array_map('floatval', array_filter($values, 'is_numeric'));

                $archives[] = [
                    'location' => $location,
                    'values' => $numericValues
                ];
            }
        }

        return $archives;
    }

    public function parseBackupInfo(string $content): array
    {
        $backups = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^([^:]+):\s*([0-9.]+)\s*(GB|MB|TB)?,?\s*(.*)$/', $line, $matches)) {
                $backups[] = [
                    'tipo'    => trim($matches[1]),
                    'tamanho' => floatval($matches[2]),
                    'unidade' => $matches[3] ?? 'GB',
                    'local'   => trim($matches[4])
                ];
            }
        }

        return $backups;
    }
}