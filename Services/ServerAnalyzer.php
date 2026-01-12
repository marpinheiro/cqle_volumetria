<?php

namespace Services;

class ServerAnalyzer
{
    public function analyze(string $serverContent): array
    {
        if (empty(trim($serverContent))) {
            return [
                'status' => 'sem_dados',
                'memoria_status' => [
                    'total_gb' => 'N/D',
                    'status' => 'não detectado'
                ],
                'filesystem_analysis' => [
                    'status_geral' => 'não detectado',
                    'filesystems' => []
                ]
            ];
        }

        $data = [];
        $filesystem = [];
        $lines = explode("\n", $serverContent);
        $inFilesystemTable = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Parsing chave:valor
            if (strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                $key = trim($parts[0]);
                $value = trim($parts[1] ?? '');

                $data[$key] = $value;

                if (stripos($key, 'Memória') !== false) {
                    $data['Memória'] = $value;
                }
                if (stripos($key, 'Versão Banco') !== false || stripos($key, 'Versão') !== false) {
                    $data['Versão Banco'] = $value;
                }
            }

            // Cabeçalho da tabela de filesystem
            elseif (strpos($line, 'Filesystem') === 0) {
                $inFilesystemTable = true;
            }

            // Linhas de filesystem
            elseif ($inFilesystemTable && strpos($line, '/dev/') === 0) {
                $parts = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);

                if (count($parts) >= 6) {
                    $fs = [
                        'device'       => $parts[0],
                        'type'         => $parts[1],
                        'size'         => $parts[2],
                        'used'         => $parts[3],
                        'avail'        => $parts[4],
                        'use_percent'  => (int) rtrim($parts[5], '%'),
                        'mount_point'  => end($parts),
                        'mounted_on'   => end($parts)
                    ];
                    $filesystem[] = $fs;
                }
            }
        }

        $analysis = $data;
        $analysis['filesystem_analysis'] = $this->analyzeFilesystem($filesystem);

        // Memória
        $memRaw = $data['Memória'] ?? null;
        $memoryGB = 0;
        if ($memRaw) {
            $memClean = preg_replace('/[^0-9.]/', '', $memRaw);
            $memoryGB = (int) $memClean;
        }
        $analysis['memoria_status'] = $this->analyzeMemory($memoryGB);

        return $analysis;
    }

    private function analyzeMemory(int $memoryGB): array
    {
        $status = $memoryGB === 0 ? 'não detectado' : ($memoryGB >= 64 ? 'alta' : ($memoryGB >= 16 ? 'adequada' : 'baixa'));
        $recommendation = match ($status) {
            'não detectado' => 'Memória não identificada',
            'baixa' => 'Aumentar memória urgente',
            'adequada' => 'Adequada para cargas médias',
            'alta' => 'Robusta para grandes cargas',
            default => ''
        };

        return [
            'total_gb' => $memoryGB,
            'status' => $status,
            'recommendation' => $recommendation
        ];
    }

    private function analyzeFilesystem(array $filesystem): array
    {
        if (empty($filesystem)) {
            return ['status_geral' => 'não detectado', 'filesystems' => []];
        }

        $critical = $warnings = $healthy = [];

        foreach ($filesystem as $fs) {
            $usage = $fs['use_percent'] ?? 0;
            $fs['status'] = $usage >= 90 ? 'critical' : ($usage >= 75 ? 'warning' : 'healthy');

            if ($usage >= 90) $critical[] = $fs;
            elseif ($usage >= 75) $warnings[] = $fs;
            else $healthy[] = $fs;
        }

        $status = count($critical) > 0 ? 'CRÍTICO' : (count($warnings) > 0 ? 'ATENÇÃO' : 'SAUDÁVEL');

        return [
            'total_filesystems' => count($filesystem),
            'critical' => $critical,
            'warnings' => $warnings,
            'healthy' => $healthy,
            'status_geral' => $status,
            'filesystems' => $filesystem
        ];
    }
}
