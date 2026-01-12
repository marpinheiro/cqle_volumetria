<?php

namespace Services;

class BackupAnalyzer
{
  public function analyze($backupContent): array
  {
    $contents = is_array($backupContent) ? $backupContent : [$backupContent];
    
    $allBackups = [];
    $tiposDetectados = [];

    foreach ($contents as $content) {
      if (empty(trim($content))) continue;

      $lines = explode("\n", $content);
      $currentBackup = null;

      foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Para de processar se encontrar seção de processadores ou outra seção
        if (stripos($line, '###') !== false && stripos($line, 'Processadores') !== false) {
            break;
        }

        // Linha informativa "Tipos de Backup:"
        if (preg_match('/Tipos\s+de\s+Backup[:\s]/iu', $line)) {
          if (preg_match('/Tipos\s+de\s+Backup[:\s]+(.+)/iu', $line, $m)) {
            $tipos = preg_split('/\s*[-,]\s*/', trim($m[1]));
            foreach ($tipos as $t) {
              $t = trim($t);
              if (!empty($t) && !in_array($t, $tiposDetectados)) {
                $tiposDetectados[] = $t;
              }
            }
          }
          continue;
        }

        // Início de novo backup: "Tipo: xxx"
        if (preg_match('/^Tipo\s*:\s*(.+)$/iu', $line, $m)) {
          // Salva backup anterior
          if ($currentBackup !== null && !empty($currentBackup['tipo'])) {
            $allBackups[] = $currentBackup;
          }

          $tipo = trim($m[1]);
          
          if (!in_array($tipo, $tiposDetectados)) {
            $tiposDetectados[] = $tipo;
          }

          $currentBackup = [
            'tipo' => $tipo,
            'diretorio' => 'N/D',
            'tamanho_gb' => 0,
            'tamanho_formatado' => 'N/D',
            'horario_inicio' => 'N/D',
            'duracao_media' => 'N/D'
          ];
          continue;
        }

        if ($currentBackup === null) continue;

        // Diretório - SUPER TOLERANTE para acentos
        if (preg_match('/^Diret.?rio\s*:\s*(.+)$/iu', $line, $m)) {
          $currentBackup['diretorio'] = trim($m[1]);
          continue;
        }

        // Tamanho
        if (preg_match('/^Tamanho\s*:\s*(\d+[,.]?\d*)\s*(GB|G|MB|M|TB|T)?/iu', $line, $m)) {
          $tamanhoGB = $this->extractSizeInGB($m[1], $m[2] ?? 'GB');
          $currentBackup['tamanho_gb'] = $tamanhoGB;
          $currentBackup['tamanho_formatado'] = $this->formatSize($tamanhoGB);
          continue;
        }

        // Horário - SUPER TOLERANTE
        if (preg_match('/^Hor.?rio\s+de\s+inicio\s*:\s*(.+)$/iu', $line, $m)) {
          $currentBackup['horario_inicio'] = trim($m[1]);
          continue;
        }

        // Duração - SUPER TOLERANTE
        if (preg_match('/^Dura.{1,3}o\s+m.?dia\s*:\s*(.+)$/iu', $line, $m)) {
          $currentBackup['duracao_media'] = trim($m[1]);
          continue;
        }
      }

      // Adiciona último backup
      if ($currentBackup !== null && !empty($currentBackup['tipo'])) {
        $allBackups[] = $currentBackup;
      }
    }

    $totalBackups = count($allBackups);
    $tamanhoTotal = array_sum(array_column($allBackups, 'tamanho_gb'));
    
    if ($totalBackups === 0) {
      return [
        'status' => 'NAO_CONFIGURADO',
        'status_class' => 'critico',
        'total_backups' => 0,
        'tipos_detectados' => !empty($tiposDetectados) ? implode(', ', $tiposDetectados) : 'N/D',
        'backups' => [],
        'tamanho_total_gb' => 0,
        'tamanho_total_formatado' => '0 GB',
        'recommendation' => 'CRÍTICO: Nenhum backup configurado detectado!'
      ];
    }

    $status = 'CONFIGURADO';
    $statusClass = $totalBackups >= 2 ? 'excelente' : ($totalBackups == 1 ? 'atencao' : 'critico');
    
    $recommendation = match($totalBackups) {
      0 => 'CRÍTICO: Nenhum backup detectado. Configure backup imediatamente!',
      1 => 'ATENÇÃO: Apenas 1 tipo de backup. Recomenda-se redundância (RMAN + DataPump).',
      default => 'EXCELENTE: Múltiplos tipos de backup configurados. Verifique testes de restore.'
    };

    if (empty($tiposDetectados)) {
      $tiposDetectados = array_unique(array_column($allBackups, 'tipo'));
    }

    return [
      'status' => $status,
      'status_class' => $statusClass,
      'total_backups' => $totalBackups,
      'tipos_detectados' => implode(', ', $tiposDetectados),
      'backups' => $allBackups,
      'tamanho_total_gb' => round($tamanhoTotal, 2),
      'tamanho_total_formatado' => $this->formatSize($tamanhoTotal),
      'recommendation' => $recommendation
    ];
  }

  private function extractSizeInGB(string $value, string $unit = 'GB'): float
  {
    $value = floatval(str_replace(',', '', $value));
    $unit = strtoupper(trim($unit));

    return match ($unit) {
      'TB', 'T' => $value * 1024,
      'GB', 'G' => $value,
      'MB', 'M' => $value / 1024,
      'KB', 'K' => $value / (1024 * 1024),
      default => $value
    };
  }

  private function formatSize(float $sizeGB): string
  {
    if ($sizeGB >= 1024) {
      return number_format($sizeGB / 1024, 2, ',', '.') . ' TB';
    } elseif ($sizeGB >= 1) {
      return number_format($sizeGB, 2, ',', '.') . ' GB';
    } else {
      return number_format($sizeGB * 1024, 2, ',', '.') . ' MB';
    }
  }
}