<?php

namespace Services;

class DatabaseAnalyzer
{
  public function analyze($bancoContent): array
  {
    $contents = is_array($bancoContent) ? $bancoContent : [$bancoContent];
    $instancias = [];

    foreach ($contents as $content) {
      if (empty(trim($content))) continue;

      $lines = explode("\n", $content);
      $data = [];
      $monthGrown = [];
      $dd = [];
      $datafiles = [];

      foreach ($lines as $idx => $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Nome Instância - SUPER TOLERANTE
        if (preg_match('/Nome\s+Inst[âaà]ncia\s*:\s*(\S+)/iu', $line, $m)) {
          $data['instancia'] = $m[1];
          continue;
        }

        // Tipo - ignora tipos de backup
        if (preg_match('/^Tipo\s*:\s*(.+)$/i', $line, $m)) {
          $tipo = trim($m[1]);
          $tiposBackup = ['fullbackup', 'dbnoite', 'dpnoite', 'dpdia', 'rman', 'incremental', 'differential'];
          if (!in_array(strtolower($tipo), $tiposBackup)) {
            $data['tipo'] = $tipo;
          }
          continue;
        }

        // Database Size
        if (stripos($line, 'Database Size') !== false) {
          if (isset($lines[$idx + 1])) {
            $nextLine = trim($lines[$idx + 1]);
            $parts = preg_split('/\s*\|\s*/', $nextLine);
            if (count($parts) >= 3) {
              $data['tamanho_total_gb'] = $this->extractGB($parts[0]);
              $data['used_gb'] = $this->extractGB($parts[1]);
              $data['free_gb'] = $this->extractGB($parts[2]);
            }
          }
          continue;
        }

        // Crescimento - linhas que começam com mês/ano
        if (preg_match('/^([A-Z][a-z]{2}\d{4})\s*\|/i', $line)) {
          $parts = preg_split('/\s*\|\s*/', $line);
          if (count($parts) >= 3) {
            $monthGrownStr = trim($parts[2]);
            if (preg_match('/([+-]?\d+[,.]?\d*)\s*M/i', $monthGrownStr, $m)) {
              $val = str_replace(',', '', $m[1]);
              $valFloat = floatval($val);
              if ($valFloat > 0) {
                $monthGrown[] = $valFloat;
              }
            }
          }
          continue;
        }

        // Archives - linha DD = [...]
        if (preg_match('/^DD\s*=\s*\[(.+)\]/i', $line, $m)) {
          preg_match_all('/(\d+[,.]?\d*)\s*M/i', $m[1], $matches);
          foreach ($matches[1] as $val) {
            $clean = str_replace(',', '', $val);
            if (is_numeric($clean)) {
              $dd[] = floatval($clean);
            }
          }
          continue;
        }

        // Datafiles - linhas com FID|TBS|...
        if (preg_match('/^\s*(\d+)\s*\|/i', $line)) {
          $parts = preg_split('/\s*\|\s*/', $line);
          if (count($parts) >= 6 && !stripos($line, 'FID')) {
            $datafiles[] = [
              'fid' => trim($parts[0]),
              'tbs' => trim($parts[1]),
              'fn'  => trim($parts[2]),
              'pct' => trim($parts[3]),
              'mb_used' => floatval(str_replace(',', '', trim($parts[4]))),
              'mb_max' => floatval(str_replace(',', '', trim($parts[5])))
            ];
          }
          continue;
        }
      }

      // CÁLCULOS
      $last12 = array_slice($monthGrown, -12);
      $positivos = array_filter($last12, fn($v) => $v > 0);
      $mediaCrescimentoMB = count($positivos) > 0 ? array_sum($positivos) / count($positivos) : 0;
      $mediaCrescimentoGB = $mediaCrescimentoMB / 1024;

      if (count($dd) >= 2) {
        $dd[count($dd) - 1] = $dd[count($dd) - 2];
      }
      $mediaArchivesMB = count($dd) > 0 ? array_sum($dd) / count($dd) : 0;

      $tamanhoDatafilesMB = array_sum(array_column($datafiles, 'mb_used'));
      $tamanhoDatafilesGB = $tamanhoDatafilesMB / 1024;

      $instanciaData = [
        'instancia' => $data['instancia'] ?? 'N/D',
        'tipo' => $data['tipo'] ?? 'N/D',
        'tamanho_total_gb' => $data['tamanho_total_gb'] ?? 0,
        'used_gb' => $data['used_gb'] ?? 0,
        'free_gb' => $data['free_gb'] ?? 0,
        'crescimento' => round($mediaCrescimentoGB, 2),
        'meses_validos' => count($positivos),
        'geracao_archives' => round($mediaArchivesMB, 2),
        'geracao_archives_formatted' => $this->formatSize($mediaArchivesMB),
        'tamanho_datafiles_gb' => round($tamanhoDatafilesGB, 2)
      ];

      $instancias[] = $instanciaData;
    }

    $qtd = count($instancias);
    $mediaCrescimentoFinal = $qtd > 0 ? array_sum(array_column($instancias, 'crescimento')) / $qtd : 0;
    $mediaArchivesFinal = $qtd > 0 ? array_sum(array_column($instancias, 'geracao_archives')) / $qtd : 0;
    $mediaDatafilesFinal = $qtd > 0 ? array_sum(array_column($instancias, 'tamanho_datafiles_gb')) / $qtd : 0;

    return [
      'status' => $qtd > 0 ? 'analisado' : 'sem_dados',
      'instancias' => $instancias,
      'media_crescimento' => round($mediaCrescimentoFinal, 2),
      'media_archives' => round($mediaArchivesFinal, 2),
      'media_archives_formatted' => $this->formatSize($mediaArchivesFinal),
      'media_datafiles_gb' => round($mediaDatafilesFinal, 2),
      'qtd_instancias' => $qtd
    ];
  }

  private function extractGB(string $str): float
  {
    $str = trim($str);
    if (preg_match('/(\d+[,.]?\d*)\s*(GB|G|MB|M|TB|T)?/i', $str, $m)) {
      $value = floatval(str_replace(',', '', $m[1]));
      $unit = strtoupper($m[2] ?? 'GB');

      return match ($unit) {
        'TB', 'T' => $value * 1024,
        'GB', 'G' => $value,
        'MB', 'M' => $value / 1024,
        default => $value
      };
    }
    return 0.0;
  }

  private function formatSize(float $sizeMB): string
  {
    if ($sizeMB >= 1024 * 1024) {
      return number_format($sizeMB / (1024 * 1024), 2, ',', '.') . ' TB';
    } elseif ($sizeMB >= 1024) {
      return number_format($sizeMB / 1024, 2, ',', '.') . ' GB';
    } else {
      return number_format($sizeMB, 2, ',', '.') . ' MB';
    }
  }

  private function convertToGB(string $sizeStr): float
  {
    $sizeStr = trim($sizeStr);
    if (preg_match('/(\d+(?:\.\d+)?)\s*([GMKTgmkt]?[Bb]?)/', $sizeStr, $m)) {
      $value = (float)$m[1];
      $unit = strtoupper($m[2]);
      return match ($unit) {
        'TB', 'T' => $value * 1024,
        'GB', 'G' => $value,
        'MB', 'M' => $value / 1024,
        'KB', 'K' => $value / (1024 * 1024),
        default => $value
      };
    }
    return 0.0;
  }
}
