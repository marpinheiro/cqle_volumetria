<?php

namespace Services;

/**
 * Analisador de Volumetria e Previsão de Crescimento
 * Desenvolvido por: Marciano Silva - CQLE Softwares
 */
class VolumetriaAnalyzer
{
    /**
     * Analisa volumetria completa do ambiente
     * 
     * @param array $serverData Dados do servidor (filesystem/ASM)
     * @param array $databaseData Dados do banco (datafiles, crescimento)
     * @param array $backupData Dados de backup
     * @return array Análise completa por partição
     */
    public function analyzeVolumetry(array $serverData, array $databaseData, array $backupData): array
    {
        // 1. Agrupa dados por partição/ASM
        $partitions = $this->groupByPartition($serverData, $databaseData, $backupData);
        
        // 2. Calcula crescimento e previsões para cada partição
        $analysis = [];
        foreach ($partitions as $mountPoint => $data) {
            $analysis[$mountPoint] = $this->analyzePartition($mountPoint, $data, $databaseData);
        }
        
        // 3. Ordena por criticidade (menor tempo até esgotamento)
        uasort($analysis, function($a, $b) {
            return ($a['meses_ate_esgotamento'] ?? 999) <=> ($b['meses_ate_esgotamento'] ?? 999);
        });
        
        return $analysis;
    }

    /**
     * Agrupa todos os dados por partição/diretório raiz
     */
    private function groupByPartition(array $serverData, array $databaseData, array $backupData): array
    {
        $partitions = [];

        // 1. FILESYSTEM/ASM do servidor
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

        // 2. DATAFILES - agrupa por diretório raiz
        $instancias = $databaseData['instancias'] ?? [];
        foreach ($instancias as $instancia) {
            $datafiles = $this->extractDatafilesFromRawData($databaseData);
            
            foreach ($datafiles as $df) {
                $path = $df['fn'] ?? '';
                $rootDir = $this->extractRootDirectory($path);
                
                if ($rootDir && isset($partitions[$rootDir])) {
                    $partitions[$rootDir]['datafiles_gb'] += ($df['mb_used'] ?? 0) / 1024;
                    $partitions[$rootDir]['arquivos'][] = [
                        'tipo' => 'datafile',
                        'caminho' => $path,
                        'tamanho_gb' => ($df['mb_used'] ?? 0) / 1024
                    ];
                }
            }
            
            // Crescimento mensal (distribui proporcionalmente aos datafiles)
            $crescimentoMensal = $instancia['crescimento'] ?? 0;
            if ($crescimentoMensal > 0) {
                foreach ($partitions as $mp => &$part) {
                    if ($part['datafiles_gb'] > 0) {
                        $part['crescimento_mensal_gb'] += $crescimentoMensal;
                    }
                }
            }
            
            // Archives (diário) - localiza pelo "Local dos archives"
            $archiveDir = $this->extractArchiveDirectory($databaseData);
            $archivesGBDiario = ($instancia['geracao_archives'] ?? 0) / 1024; // MB para GB
            
            if ($archiveDir) {
                $rootDir = $this->extractRootDirectory($archiveDir);
                if ($rootDir && isset($partitions[$rootDir])) {
                    $partitions[$rootDir]['archives_gb_diario'] += $archivesGBDiario;
                }
            }
        }

        // 3. BACKUPS - agrupa por diretório
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

    /**
     * Analisa uma partição específica e faz projeções
     */
    private function analyzePartition(string $mountPoint, array $data, array $databaseData): array
    {
        $totalGB = $data['tamanho_total_gb'] ?? 0;
        $usadoGB = $data['usado_gb'] ?? 0;
        $livreGB = $data['livre_gb'] ?? 0;
        
        // Consumo atual detalhado
        $datafilesGB = $data['datafiles_gb'] ?? 0;
        $archivesDiario = $data['archives_gb_diario'] ?? 0;
        $backupsDiario = $data['backups_gb_diario'] ?? 0;
        $crescimentoMensal = $data['crescimento_mensal_gb'] ?? 0;
        
        // Outros dados ocupando espaço
        $outrosGB = max(0, $usadoGB - $datafilesGB - $archivesDiario - $backupsDiario);
        
        // PREVISÃO DE ESGOTAMENTO
        $mesesAteEsgotamento = null;
        if ($crescimentoMensal > 0 && $livreGB > 0) {
            $mesesAteEsgotamento = ceil($livreGB / $crescimentoMensal);
        }
        
        // CÁLCULO DE EXPANSÃO para 12, 24, 36 meses (80% de margem livre)
        $expansoes = [];
        foreach ([12, 24, 36] as $meses) {
            $crescimentoTotal = $crescimentoMensal * $meses;
            $espacoNecessario = $usadoGB + $crescimentoTotal;
            
            // Para ter 80% livre, o total deve ser: necessário / 0.20
            $totalNecessario = $espacoNecessario / 0.20;
            $expansaoNecessaria = max(0, $totalNecessario - $totalGB);
            
            $expansoes[$meses] = [
                'crescimento_previsto_gb' => round($crescimentoTotal, 2),
                'espaco_necessario_gb' => round($espacoNecessario, 2),
                'total_ideal_gb' => round($totalNecessario, 2),
                'expansao_necessaria_gb' => round($expansaoNecessaria, 2),
                'uso_final_percent' => 20 // 80% livre = 20% usado
            ];
        }
        
        // EVOLUÇÃO DE CRESCIMENTO (últimos 12 meses)
        $evolucao = $this->calculateGrowthEvolution($databaseData, $mesesAteEsgotamento ?? 24);
        
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
                'mensal_gb' => round($crescimentoMensal, 2),
                'anual_gb' => round($crescimentoMensal * 12, 2)
            ],
            'previsao' => [
                'meses_ate_esgotamento' => $mesesAteEsgotamento,
                'data_esgotamento' => $mesesAteEsgotamento ? date('m/Y', strtotime("+{$mesesAteEsgotamento} months")) : 'N/A',
                'nivel_criticidade' => $this->calculateCriticality($mesesAteEsgotamento, $data['uso_percent'])
            ],
            'expansao_recomendada' => $expansoes,
            'evolucao_crescimento' => $evolucao,
            'arquivos_detalhados' => array_slice($data['arquivos'] ?? [], 0, 20) // Limita a 20 para performance
        ];
    }

    /**
     * Calcula evolução de crescimento
     */
    private function calculateGrowthEvolution(array $databaseData, int $mesesProjecao): array
    {
        // Aqui você pode extrair dados históricos reais se disponível
        // Por enquanto, retorna projeção baseada no crescimento médio
        
        $crescimentoMensal = 0;
        if (!empty($databaseData['instancias'])) {
            $crescimentoMensal = $databaseData['instancias'][0]['crescimento'] ?? 0;
        }
        
        $evolucao = [];
        $baseGB = 0;
        
        for ($i = 0; $i <= min($mesesProjecao, 36); $i++) {
            $dataLabel = date('M/y', strtotime("+{$i} months"));
            $evolucao[] = [
                'mes' => $dataLabel,
                'tamanho_gb' => round($baseGB + ($crescimentoMensal * $i), 2)
            ];
        }
        
        return $evolucao;
    }

    /**
     * Detecta tipo de partição (Filesystem ou ASM)
     */
    private function detectPartitionType(string $mountPoint): string
    {
        if (strpos($mountPoint, '+') === 0) {
            return 'ASM';
        }
        return 'Filesystem';
    }

    /**
     * Extrai diretório raiz de um caminho
     */
    private function extractRootDirectory(string $path): ?string
    {
        if (empty($path)) return null;
        
        // ASM: +DATA, +FRA, etc
        if (strpos($path, '+') === 0) {
            preg_match('/(\+[^\/]+)/', $path, $matches);
            return $matches[1] ?? null;
        }
        
        // Filesystem: /u01, /oracle, etc
        preg_match('#^(/[^/]+)#', $path, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Converte string de tamanho para GB
     */
    private function convertToGB(string $size): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*([KMGT])?/i', $size, $m)) {
            $value = (float)$m[1];
            $unit = strtoupper($m[2] ?? 'G');
            
            return match($unit) {
                'T' => $value * 1024,
                'G' => $value,
                'M' => $value / 1024,
                'K' => $value / (1024 * 1024),
                default => $value
            };
        }
        return 0;
    }

    /**
     * Extrai datafiles do dado bruto
     */
    private function extractDatafilesFromRawData(array $databaseData): array
    {
        $allDatafiles = [];
        
        if (isset($databaseData['instancias'])) {
            foreach ($databaseData['instancias'] as $instancia) {
                if (isset($instancia['datafiles'])) {
                    $allDatafiles = array_merge($allDatafiles, $instancia['datafiles']);
                }
            }
        }
        
        return $allDatafiles;
    }

    /**
     * Extrai diretório de archives
     */
    private function extractArchiveDirectory(array $databaseData): ?string
    {
        if (isset($databaseData['instancias'])) {
            foreach ($databaseData['instancias'] as $instancia) {
                if (!empty($instancia['archive_location'])) {
                    return $instancia['archive_location'];
                }
            }
        }
        
        return null;
    }

    /**
     * Calcula nível de criticidade
     */
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