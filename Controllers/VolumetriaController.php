<?php

namespace Controllers;

use Services\FileParserService;
use Services\ServerAnalyzer;
use Services\DatabaseAnalyzer;
use Services\BackupAnalyzer;
use Services\VolumetriaAnalyzer;
use Utils\FileValidator;

class VolumetriaController
{
    private $parser;
    private $serverAnalyzer;
    private $dbAnalyzer;
    private $backupAnalyzer;
    private $volumetriaAnalyzer;
    private $validator;

    public function __construct()
    {
        $this->parser = new FileParserService();
        $this->serverAnalyzer = new ServerAnalyzer();
        $this->dbAnalyzer = new DatabaseAnalyzer();
        $this->backupAnalyzer = new BackupAnalyzer();
        $this->volumetriaAnalyzer = new VolumetriaAnalyzer();
        $this->validator = new FileValidator();
    }

    public function showUploadPage(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require BASE_PATH . '/views/upload.php';
    }

    public function processFile(array $file): array
    {
        try {
            $validation = $this->validator->validate($file);
            if (!$validation['valid']) {
                throw new \Exception($validation['message']);
            }

            $uploadDir = BASE_PATH . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid('volumetria_') . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new \Exception('Erro ao salvar arquivo');
            }

            $parsedData = $this->parser->parseFile($filePath);

            $servidorData = $parsedData['servidor'] ?? '';
            $bancoData    = $parsedData['banco']    ?? [];
            $backupData   = $parsedData['backup']   ?? [];

            $servidorAnalysis = $this->serverAnalyzer->analyze($servidorData);
            $bancoAnalysis = $this->dbAnalyzer->analyze($bancoData);
            $backupAnalysis = $this->backupAnalyzer->analyze($backupData);

            $result = [
                'success' => true,
                'arquivo_original' => $file['name'],
                'formato_detectado' => $parsedData['format'] ?? 'desconhecido',
                'data_processamento' => date('d/m/Y H:i:s'),
                'servidor' => $servidorAnalysis,
                'banco'    => $bancoAnalysis,
                'backup'   => $backupAnalysis,
                'resumo'   => $this->generateSummary($servidorAnalysis, $bancoAnalysis, $backupAnalysis),
                'raw_data' => $parsedData
            ];

            unlink($filePath);

            $_SESSION['last_result'] = $result;

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function showResults(array $result): void
    {
        require BASE_PATH . '/views/results.php';
    }

    /**
     * NOVO MÉTODO: Exibe estudo de volumetria
     */
    public function showVolumetryStudy(array $result): void
    {
        try {
            // Executa análise de volumetria
            $volumetriaAnalysis = $this->volumetriaAnalyzer->analyzeVolumetry(
                $result['servidor'] ?? [],
                $result['banco'] ?? [],
                $result['backup'] ?? []
            );

            // Passa dados para a view
            $analysis = $volumetriaAnalysis;
            $originalResult = $result;

            require BASE_PATH . '/views/volumetria.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao processar estudo de volumetria: ' . $e->getMessage();
            header('Location: index.php');
        }
    }

    public function export(array $data, string $format = 'json'): void
    {
        switch ($format) {
            case 'json':
                $this->exportJSON($data);
                break;
            case 'txt':
                $this->exportTXT($data);
                break;
            default:
                $this->exportJSON($data);
        }
    }

    public function exportJSON(array $data): void
    {
        $filename = 'volumetria_' . date('Ymd_His') . '.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function exportTXT(array $data): void
    {
        $filename = 'volumetria_' . date('Ymd_His') . '.txt';

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = $this->formatDataAsTXT($data);

        echo $output;
        exit;
    }

    private function formatDataAsTXT(array $data): string
    {
        $txt = str_repeat('=', 100) . "\n";
        $txt .= "                    CQLE VOLUMETRIA - RELATÓRIO PROFISSIONAL DE ANÁLISE\n";
        $txt .= "                                    Versão 2.0 PRO\n";
        $txt .= str_repeat('=', 100) . "\n";
        $txt .= "Gerado em: " . ($data['data_processamento'] ?? 'N/D') . "\n";
        $txt .= "Arquivo: " . ($data['arquivo_original'] ?? 'N/D') . "\n";
        $txt .= "Formato: " . strtoupper($data['formato_detectado'] ?? 'DESCONHECIDO') . "\n";
        $txt .= str_repeat('=', 100) . "\n\n";

        // SERVIDOR
        $txt .= str_repeat('━', 100) . "\n";
        $txt .= "█ SERVIDOR\n";
        $txt .= str_repeat('━', 100) . "\n\n";

        $servidor = $data['servidor'] ?? [];
        $camposServidor = [
            'Hostname' => $servidor['Hostname'] ?? 'N/D',
            'IP' => $servidor['IP'] ?? 'N/D',
            'S.O' => $servidor['S.O'] ?? 'N/D',
            'Versão Banco' => $servidor['Versão Banco'] ?? 'N/D',
            'Fabricante' => $servidor['Fabricante'] ?? 'N/D',
            'Modelo' => $servidor['Modelo'] ?? 'N/D',
            'Service Tag' => $servidor['Service Tag'] ?? 'N/D',
            'Qtd. Processadores' => $servidor['Qtd. Proc'] ?? 'N/D',
            'Modelo Processador' => $servidor['Modelo. Proc'] ?? 'N/D',
            'Multi-Processamento' => $servidor['Multi-Processamento'] ?? 'N/D',
            'Memória' => $servidor['Memória'] ?? 'N/D'
        ];

        foreach ($camposServidor as $label => $value) {
            $txt .= sprintf("%-25s: %s\n", $label, $value);
        }
        $txt .= "\n";

        // FILESYSTEM
        if (isset($servidor['filesystem_analysis']['filesystems']) && !empty($servidor['filesystem_analysis']['filesystems'])) {
            $txt .= "┌─ FILESYSTEM ─────────────────────────────────────────────────────────────────────────────────┐\n";
            $txt .= sprintf("│ Status Geral: %-80s │\n", $servidor['filesystem_analysis']['status_geral']);
            $txt .= "├──────────────────────────────────────────────────────────────────────────────────────────────┤\n";
            $txt .= sprintf(
                "│ %-35s │ %10s │ %10s │ %10s │ %8s │\n",
                "MOUNT POINT",
                "TAMANHO",
                "USADO",
                "LIVRE",
                "USO %"
            );
            $txt .= "├──────────────────────────────────────────────────────────────────────────────────────────────┤\n";

            foreach ($servidor['filesystem_analysis']['filesystems'] as $fs) {
                $mountPoint = $fs['mounted_on'] ?? $fs['mount_point'] ?? 'N/D';
                $txt .= sprintf(
                    "│ %-35s │ %10s │ %10s │ %10s │ %7s%% │\n",
                    substr($mountPoint, 0, 35),
                    $fs['size'] ?? 'N/D',
                    $fs['used'] ?? 'N/D',
                    $fs['avail'] ?? 'N/D',
                    $fs['use_percent'] ?? 0
                );
            }
            $txt .= "└──────────────────────────────────────────────────────────────────────────────────────────────┘\n\n";
        }

        // BANCO DE DADOS
        $txt .= str_repeat('━', 100) . "\n";
        $txt .= "█ BANCO DE DADOS\n";
        $txt .= str_repeat('━', 100) . "\n\n";

        $banco = $data['banco'] ?? [];
        $instancias = $banco['instancias'] ?? [];

        if (!empty($instancias)) {
            foreach ($instancias as $idx => $inst) {
                $txt .= sprintf("┌─ INSTÂNCIA #%d ───────────────────────────────────────────────────────────────────────────┐\n", $idx + 1);
                $txt .= sprintf("│ Nome: %-85s │\n", $inst['instancia'] ?? 'N/D');
                $txt .= sprintf("│ Tipo: %-85s │\n", $inst['tipo'] ?? 'N/D');
                $txt .= "├──────────────────────────────────────────────────────────────────────────────────────────────┤\n";
                $txt .= sprintf("│ Tamanho Total:          %-68s │\n", number_format($inst['tamanho_total_gb'] ?? 0, 2, ',', '.') . ' GB');
                $txt .= sprintf("│ Crescimento Mensal:     %-68s │\n", number_format($inst['crescimento'] ?? 0, 2, ',', '.') . ' GB/mês');
                $txt .= sprintf("│ Geração de Archives:    %-68s │\n", $inst['geracao_archives_formatted'] ?? '0 MB');
                $txt .= sprintf("│ Tamanho de Datafiles:   %-68s │\n", number_format($inst['tamanho_datafiles_gb'] ?? 0, 2, ',', '.') . ' GB');
                $txt .= "└──────────────────────────────────────────────────────────────────────────────────────────────┘\n\n";
            }
        } else {
            $txt .= "Nenhuma instância detectada.\n\n";
        }

        // BACKUP
        $txt .= str_repeat('━', 100) . "\n";
        $txt .= "█ BACKUP\n";
        $txt .= str_repeat('━', 100) . "\n\n";

        $backup = $data['backup'] ?? [];
        $txt .= sprintf("Status: %s\n", strtoupper($backup['status'] ?? 'NÃO CONFIGURADO'));
        $txt .= sprintf("Total de Backups Detectados: %d\n", $backup['total_backups'] ?? 0);

        if (isset($backup['backups']) && !empty($backup['backups'])) {
            $txt .= sprintf("Tipos: %s\n", $backup['tipos_detectados'] ?? 'N/D');
            $txt .= sprintf("Tamanho Total: %s\n\n", $backup['tamanho_total_formatado'] ?? '0 GB');

            foreach ($backup['backups'] as $idx => $bkp) {
                $txt .= sprintf("┌─ BACKUP #%d ─────────────────────────────────────────────────────────────────────────────┐\n", $idx + 1);
                $txt .= sprintf("│ Tipo:         %-79s │\n", $bkp['tipo'] ?? 'N/D');
                $txt .= sprintf("│ Diretório:    %-79s │\n", substr($bkp['diretorio'] ?? 'N/D', 0, 79));
                $txt .= sprintf("│ Tamanho:      %-79s │\n", $bkp['tamanho_formatado'] ?? 'N/D');
                $txt .= sprintf("│ Horário:      %-79s │\n", $bkp['horario_inicio'] ?? 'N/D');
                $txt .= sprintf("│ Duração:      %-79s │\n", $bkp['duracao_media'] ?? 'N/D');
                $txt .= "└──────────────────────────────────────────────────────────────────────────────────────────────┘\n\n";
            }
        } else {
            $txt .= "⚠ CRÍTICO: Nenhum backup configurado detectado!\n\n";
        }

        // RODAPÉ
        $txt .= str_repeat('=', 100) . "\n";
        $txt .= "                    Relatório gerado por CQLE Softwares v2.0 PRO\n";
        $txt .= "                         Desenvolvido por: Marciano Silva\n";
        $txt .= "                              www.cqlesoftwares.com.br\n";
        $txt .= str_repeat('=', 100) . "\n";

        return $txt;
    }

    private function generateSummary($servidor, $banco, $backup): array
    {
        $summary = [
            'servidor' => [
                'hostname' => $servidor['Hostname'] ?? 'N/D',
                'memoria_total' => $servidor['Memória'] ?? 'N/D',
                'status_filesystem' => $servidor['filesystem_analysis']['status_geral'] ?? 'N/D',
                'total_filesystems' => $servidor['filesystem_analysis']['total_filesystems'] ?? 0
            ],
            'banco' => [
                'qtd_instancias' => $banco['qtd_instancias'] ?? 0,
                'media_crescimento' => isset($banco['media_crescimento'])
                    ? number_format($banco['media_crescimento'], 2, ',', '.') . ' GB/mês'
                    : 'N/D',
                'media_archives' => $banco['media_archives_formatted'] ?? 'N/D',
                'status' => $banco['status'] ?? 'N/D'
            ],
            'backup' => [
                'status' => $backup['status'] ?? 'NÃO CONFIGURADO',
                'total_backups' => $backup['total_backups'] ?? 0,
                'tipos' => $backup['tipos_detectados'] ?? 'N/D',
                'tamanho_total' => $backup['tamanho_total_formatado'] ?? '0 GB'
            ],
            'analise_geral' => [
                'nivel_criticidade' => $this->calcularCriticidade($servidor, $banco, $backup),
                'recomendacoes' => $this->gerarRecomendacoes($servidor, $banco, $backup)
            ]
        ];

        return $summary;
    }

    private function calcularCriticidade($servidor, $banco, $backup): string
    {
        $pontos = 0;

        if (isset($servidor['filesystem_analysis']['critical']) && count($servidor['filesystem_analysis']['critical']) > 0) {
            $pontos += 3;
        }

        if (($backup['total_backups'] ?? 0) == 0) {
            $pontos += 3;
        }

        if (($banco['media_crescimento'] ?? 0) > 200) {
            $pontos += 2;
        }

        if (($banco['media_archives'] ?? 0) > 100 * 1024) {
            $pontos += 1;
        }

        if ($pontos >= 6) return 'CRÍTICO';
        if ($pontos >= 3) return 'ATENÇÃO';
        return 'SAUDÁVEL';
    }

    private function gerarRecomendacoes($servidor, $banco, $backup): array
    {
        $recomendacoes = [];

        if (isset($servidor['filesystem_analysis']['critical']) && count($servidor['filesystem_analysis']['critical']) > 0) {
            $recomendacoes[] = 'URGENTE: Expandir filesystems críticos (uso >= 90%)';
        } elseif (isset($servidor['filesystem_analysis']['warnings']) && count($servidor['filesystem_analysis']['warnings']) > 0) {
            $recomendacoes[] = 'ATENÇÃO: Monitorar filesystems em alerta (uso >= 75%)';
        }

        if (($backup['total_backups'] ?? 0) == 0) {
            $recomendacoes[] = 'CRÍTICO: Implementar estratégia de backup imediatamente!';
        } elseif (($backup['total_backups'] ?? 0) == 1) {
            $recomendacoes[] = 'Recomendado: Adicionar backup redundante (RMAN + DataPump)';
        }

        if (($banco['media_crescimento'] ?? 0) > 300) {
            $recomendacoes[] = 'Crescimento acelerado detectado: Planejar expansão de storage';
        }

        $archivesMB = $banco['media_archives'] ?? 0;
        if ($archivesMB > 150 * 1024) {
            $recomendacoes[] = 'Geração de archives muito alta: Avaliar política de retenção';
        }

        if (empty($recomendacoes)) {
            $recomendacoes[] = 'Sistema operando dentro dos parâmetros normais';
        }

        return $recomendacoes;
    }
}
