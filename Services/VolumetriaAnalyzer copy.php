<?php

namespace Services;

/**
 * Analisador de Volumetria e Previsão de Crescimento
 * Desenvolvido por: Marciano Silva - CQLE Softwares
 */
class VolumetriaAnalyzer
{
    public const MARGEM_LIVRE_PERCENTUAL = 20; // Altere aqui o % desejado de espaço livre final
    private const FATOR_CRESCIMENTO_ARQUIVOS = 0.0; // 0 = constante | >0 = crescimento proporcional

    public function analyzeVolumetry(array $serverData, array $databaseData, array $backupData): array
    {
        $partitions = $this->groupByPartition($serverData, $databaseData, $backupData);

        $partitions = array_filter($partitions, function ($data) {
            return $data['datafiles_gb'] > 0
                || $data['archives_gb_diario'] > 0
                || $data['backups_gb_diario'] > 0;
        });

        $analysis = [];
        foreach ($partitions as $mountPoint => $data) {
            $analysis[$mountPoint] = $this->analyzePartition($mountPoint, $data, $databaseData);
        }

        uasort($analysis, function ($a, $b) {
            return ($a['meses_ate_esgotamento'] ?? 999) <=> ($b['meses_ate_esgotamento'] ?? 999);
        });

        return $analysis;
    }

    private function groupByPartition(array $serverData, array $databaseData, array $backupData): array
    {
        $partitions = [];

        if (isset($serverData['filesystem_analysis']['filesystems'])) {
            foreach ($serverData['filesystem_analysis']['filesystems'] as $fs) {
                $mountPoint = $fs['mounted_on'] ?? $fs['mount_point'] ?? $fs['device'] ?? 'unknown';

                if (!isset($partitions[$mountPoint])) {
                    $partitions[$mountPoint] = [
                        'tipo' => $this->detectPartitionType($mountPoint),
                        'tamanho_total_gb' => $this->convertToGB($fs['size'] ?? '0'),
                        'usado_gb' => $this->convertToGB($fs['used'] ?? '0'),
                        'livre_gb' => $this->convertToGB($fs['avail'] ?? '0'),
                        'uso_percent' => $fs['use_percent'] ?? 0,
                        'datafiles_gb' => 0,
                        'archives_gb_diario' => 0,
                        'backups_gb_diario' => 0,
                        'crescimento_mensal_gb' => 0,
                        'arquivos' => []
                    ];
                }
            }
        }

        $instancias = $databaseData['instancias'] ?? [];
        foreach ($instancias as $instancia) {
            $datafiles = $instancia['datafiles'] ?? [];

            foreach ($datafiles as $df) {
                $path = $df['fn'] ?? '';
                $rootDir = $this->extractRootDirectory($path);

                if ($rootDir && isset($partitions[$rootDir])) {
                    $tamanhoGB = ($df['mb_used'] ?? 0) / 1024;
                    $partitions[$rootDir]['datafiles_gb'] += $tamanhoGB;
                    $partitions[$rootDir]['arquivos'][] = [
                        'tipo' => 'datafile',
                        'caminho' => $path,
                        'tamanho_gb' => $tamanhoGB
                    ];
                }
            }

            $crescimentoMensal = $instancia['crescimento'] ?? 0;
            if ($crescimentoMensal > 0) {
                foreach ($partitions as $mp => &$part) {
                    if ($part['datafiles_gb'] > 0) {
                        $part['crescimento_mensal_gb'] = $crescimentoMensal;
                    }
                }
            }

            $archivesMB = $instancia['archive_size_daily_mb'] ?? 0;
            $archivesGB = $archivesMB / 1024;
            $archiveDir = $instancia['archive_location'] ?? null;

            if ($archiveDir && $archivesGB > 0) {
                $rootDir = $this->extractRootDirectory($archiveDir);
                if ($rootDir && isset($partitions[$rootDir])) {
                    $partitions[$rootDir]['archives_gb_diario'] += $archivesGB;
                }
            }
        }

        if (isset($backupData['backups'])) {
            foreach ($backupData['backups'] as $bkp) {
                $dir = $bkp['diretorio'] ?? '';
                $rootDir = $this->extractRootDirectory($dir);

                if ($rootDir && isset($partitions[$rootDir])) {
                    $partitions[$rootDir]['backups_gb_diario'] += $bkp['tamanho_gb'] ?? 0;
                    $partitions[$rootDir]['arquivos'][] = [
                        'tipo' => 'backup',
                        'caminho' => $dir,
                        'tamanho_gb' => $bkp['tamanho_gb'] ?? 0
                    ];
                }
            }
        }

        return $partitions;
    }

    private function analyzePartition(string $mountPoint, array $data, array $databaseData): array
    {
        $totalGB = $data['tamanho_total_gb'] ?? 0;
        $usadoGB = $data['usado_gb'] ?? 0;
        $livreGB = $data['livre_gb'] ?? 0;

        $datafilesGB = $data['datafiles_gb'] ?? 0;
        $archivesDiario = $data['archives_gb_diario'] ?? 0;
        $backupsDiario = $data['backups_gb_diario'] ?? 0;
        $crescimentoMensalDatafiles = $data['crescimento_mensal_gb'] ?? 0;

        $crescimentoAdicionalMensal = ($archivesDiario + $backupsDiario) * 30 * self::FATOR_CRESCIMENTO_ARQUIVOS;
        $crescimentoTotalMensal = $crescimentoMensalDatafiles + $crescimentoAdicionalMensal;

        $outrosGB = max(0, $usadoGB - $datafilesGB - $archivesDiario - $backupsDiario);

        $mesesAteEsgotamento = null;
        if ($crescimentoTotalMensal > 0 && $livreGB > 0) {
            $mesesAteEsgotamento = ceil($livreGB / $crescimentoTotalMensal);
        }

        $expansoes = [];
        $p = self::MARGEM_LIVRE_PERCENTUAL / 100;

        foreach ([12, 24, 36] as $meses) {
            $crescimentoTotal = $crescimentoTotalMensal * $meses;
            $usadoFuturo = $usadoGB + $crescimentoTotal;

            $numerador = ($p * $totalGB) - $livreGB + $crescimentoTotal;
            $expansaoNecessaria = max(0, $numerador / (1 - $p));

            $totalNecessario = $totalGB + $expansaoNecessaria;
            $margemLivreFinal = $totalNecessario - $usadoFuturo;
            $percentualLivreFinal = ($totalNecessario > 0) ? ($margemLivreFinal / $totalNecessario) * 100 : 0;

            $expansoes[$meses] = [
                'crescimento_previsto_gb' => round($crescimentoTotal, 2),
                'usado_futuro_gb' => round($usadoFuturo, 2),
                'total_necessario_gb' => round($totalNecessario, 2),
                'expansao_necessaria_gb' => round($expansaoNecessaria, 2),
                'margem_livre_gb' => round($margemLivreFinal, 2),
                'percentual_livre_final' => round($percentualLivreFinal, 1)
            ];
        }

        $evolucao = $this->calculateGrowthEvolution($usadoGB, $totalGB, $crescimentoTotalMensal, 36);

        return [
            'mount_point' => $mountPoint,
            'tipo' => $data['tipo'],
            'capacidade' => [
                'total_gb' => round($totalGB, 2),
                'usado_gb' => round($usadoGB, 2),
                'livre_gb' => round($livreGB, 2),
                'uso_percent' => round($data['uso_percent'] ?? 0, 2)
            ],
            'consumo_detalhado' => [
                'datafiles_gb' => round($datafilesGB, 2),
                'archives_diario_gb' => round($archivesDiario, 2),
                'backups_diario_gb' => round($backupsDiario, 2),
                'outros_gb' => round($outrosGB, 2)
            ],
            'crescimento' => [
                'mensal_gb' => round($crescimentoTotalMensal, 2),
                'anual_gb' => round($crescimentoTotalMensal * 12, 2)
            ],
            'previsao' => [
                'meses_ate_esgotamento' => $mesesAteEsgotamento,
                'data_esgotamento' => $mesesAteEsgotamento ? date('m/Y', strtotime("+{$mesesAteEsgotamento} months")) : 'N/A',
                'nivel_criticidade' => $this->calculateCriticality($mesesAteEsgotamento, $data['uso_percent'])
            ],
            'expansao_recomendada' => $expansoes,
            'evolucao_crescimento' => $evolucao,
            'arquivos_detalhados' => array_slice($data['arquivos'] ?? [], 0, 20)
        ];
    }

    private function calculateGrowthEvolution(float $usadoAtualGB, float $capacidadeAtualGB, float $crescimentoMensal, int $meses): array
    {
        $evolucao = [];
        $p = self::MARGEM_LIVRE_PERCENTUAL / 100;

        for ($i = 0; $i <= $meses; $i++) {
            $dataLabel = date('M/y', strtotime("+{$i} months"));
            $usadoProjetado = $usadoAtualGB + ($crescimentoMensal * $i);

            $numerador = ($p * $capacidadeAtualGB) - ($capacidadeAtualGB - $usadoAtualGB) + ($crescimentoMensal * $i);
            $expansao = max(0, $numerador / (1 - $p));
            $necessario = $capacidadeAtualGB + $expansao;

            $evolucao[] = [
                'mes' => $dataLabel,
                'usado_gb' => round($usadoProjetado, 2),
                'capacidade_atual_gb' => round($capacidadeAtualGB, 2),
                'necessario_gb' => round($necessario, 2)
            ];
        }

        return $evolucao;
    }

    private function detectPartitionType(string $mountPoint): string
    {
        if (strpos($mountPoint, '+') === 0) {
            return 'ASM';
        }
        return 'Filesystem';
    }

    private function extractRootDirectory(string $path): ?string
    {
        if (empty($path)) return null;

        if (strpos($path, '+') === 0) {
            preg_match('/(\+[^\/]+)/', $path, $matches);
            return $matches[1] ?? null;
        }

        preg_match('#^(/[^/]+)#', $path, $matches);
        return $matches[1] ?? null;
    }

    private function convertToGB(string $size): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*([KMGT])?/i', $size, $m)) {
            $value = (float)$m[1];
            $unit = strtoupper($m[2] ?? 'G');

            return match ($unit) {
                'T' => $value * 1024,
                'G' => $value,
                'M' => $value / 1024,
                'K' => $value / (1024 * 1024),
                default => $value
            };
        }
        return 0;
    }

    private function calculateCriticality(?int $mesesAteEsgotamento, float $usoPercent): string
    {
        if ($mesesAteEsgotamento === null) {
            return $usoPercent >= 90 ? 'CRÍTICO' : ($usoPercent >= 75 ? 'ATENÇÃO' : 'ESTÁVEL');
        }

        if ($mesesAteEsgotamento <= 6 || $usoPercent >= 90) {
            return 'CRÍTICO';
        } elseif ($mesesAteEsgotamento <= 12 || $usoPercent >= 75) {
            return 'ATENÇÃO';
        } elseif ($mesesAteEsgotamento <= 24) {
            return 'MONITORAR';
        }

        return 'ESTÁVEL';
    }
}
