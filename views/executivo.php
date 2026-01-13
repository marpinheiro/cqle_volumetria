<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatório Executivo de Volumetria - <?= htmlspecialchars($clienteNome ?? 'Cliente') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 40px;
      background: #f8fafc;
      color: #1a202c;
      line-height: 1.6;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    h1 {
      text-align: center;
      color: #059669;
      margin-bottom: 8px;
      font-size: 32px;
    }

    .subtitle {
      text-align: center;
      color: #64748b;
      font-size: 18px;
      margin-bottom: 40px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .info-card {
      background: #f1f5f9;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .info-card h3 {
      margin: 0 0 12px;
      font-size: 14px;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .info-value {
      font-size: 28px;
      font-weight: 800;
      color: #059669;
    }

    .section {
      margin-bottom: 60px;
    }

    .section h2 {
      color: #1a202c;
      border-bottom: 3px solid #10b981;
      padding-bottom: 10px;
      margin-bottom: 24px;
      font-size: 24px;
    }

    .chart-container {
      background: white;
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 40px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 15px;
    }

    th,
    td {
      padding: 14px;
      text-align: center;
      border-bottom: 1px solid #e2e8f0;
    }

    th {
      background: #059669;
      color: white;
      font-weight: 600;
    }

    .highlight {
      background: #ecfdf5;
      font-weight: bold;
    }

    .footer {
      text-align: center;
      margin-top: 60px;
      color: #94a3b8;
      font-size: 14px;
      border-top: 1px solid #e2e8f0;
      padding-top: 20px;
    }

    .btn {
      display: inline-block;
      margin: 10px 5px;
      padding: 12px 24px;
      background: #10b981;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
    }

    .btn:hover {
      background: #059669;
    }

    .btn-pdf {
      background: #8b5cf6;
    }

    .btn-pdf:hover {
      background: #7c3aed;
    }

    .instance-section {
      margin-bottom: 60px;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 24px;
      background: #fafafa;
    }

    .instance-title {
      color: #059669;
      font-size: 26px;
      margin-bottom: 20px;
      border-bottom: 2px solid #10b981;
      padding-bottom: 8px;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Relatório Executivo de Volumetria</h1>
    <p class="subtitle">
      Cliente: <strong><?= htmlspecialchars($clienteNome ?? 'Não identificado') ?></strong> |
      Ticket: <strong><?= htmlspecialchars($ticket ?? 'Não identificado') ?></strong> |
      Servidor: <strong><?= htmlspecialchars($servidor ?? 'Não identificado') ?></strong><br>
      Data de Geração: <?= date('d/m/Y H:i') ?>
    </p>

    <!-- Informações Gerais -->
    <div class="section">
      <h2>Informações Gerais</h2>
      <p><strong>Crescimento Médio Mensal (da coleta):</strong> <?= htmlspecialchars($result['banco']['media_crescimento'] ?? 'N/D') ?></p>
      <p><strong>Média de Archives (da coleta):</strong> <?= htmlspecialchars($result['banco']['media_archives'] ?? 'N/D') ?></p>
      <p><strong>Partições Analisadas:</strong> <?= implode(', ', array_keys($analysis ?? [])) ?: 'Nenhuma partição encontrada' ?></p>
      <p><strong>Instâncias/PDBs:</strong> <?= implode(', ', array_column($result['banco']['instancias'] ?? [], 'instancia')) ?: 'Nenhuma instância encontrada' ?></p>
    </div>

    <?php foreach ($analysis as $mountPoint => $partition): ?>
      <div class="instance-section">
        <h3 class="instance-title">Instância/PDB: <?= htmlspecialchars($partition['mount_point']) ?> (Tipo: <?= htmlspecialchars($partition['tipo']) ?>)</h3>

        <!-- 1. Resumo Geral -->
        <div class="section">
          <h2>1. Resumo Atual</h2>
          <div class="info-grid">
            <div class="info-card">
              <h3>Capacidade Total</h3>
              <div class="info-value"><?= number_format($partition['capacidade']['total_gb'], 2, ',', '.') ?> GB</div>
            </div>
            <div class="info-card">
              <h3>Espaço Usado</h3>
              <div class="info-value"><?= number_format($partition['capacidade']['usado_gb'], 2, ',', '.') ?> GB</div>
            </div>
            <div class="info-card">
              <h3>Percentual de Uso</h3>
              <div class="info-value"><?= number_format($partition['capacidade']['uso_percent'], 1) ?>%</div>
            </div>
            <div class="info-card">
              <h3>Crescimento Médio Mensal</h3>
              <div class="info-value"><?= number_format($partition['crescimento']['mensal_gb'], 2, ',', '.') ?> GB</div>
            </div>
          </div>
        </div>

        <!-- 2. Consumo Detalhado -->
        <div class="section">
          <h2>2. Distribuição do Espaço Utilizado</h2>
          <div class="info-grid">
            <div class="info-card">
              <h3>Datafiles</h3>
              <div class="info-value"><?= number_format($partition['consumo_detalhado']['datafiles_gb'], 2, ',', '.') ?> GB</div>
            </div>
            <div class="info-card">
              <h3>Archives (diário médio)</h3>
              <div class="info-value"><?= number_format($partition['consumo_detalhado']['archives_diario_gb'], 2, ',', '.') ?> GB</div>
            </div>
            <div class="info-card">
              <h3>Backups (diário)</h3>
              <div class="info-value"><?= number_format($partition['consumo_detalhado']['backups_diario_gb'], 2, ',', '.') ?> GB</div>
            </div>
            <div class="info-card">
              <h3>Outros (logs, traces, etc.)</h3>
              <div class="info-value" style="color:<?= $partition['consumo_detalhado']['outros_gb'] > 500 ? '#dc2626' : '#059669' ?>;">
                <?= number_format($partition['consumo_detalhado']['outros_gb'], 2, ',', '.') ?> GB
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Gráfico Histórico + Crescimento Mensal (Últimos 12 meses reais) -->
        <div class="section">
          <h2>3. Evolução e Crescimento Mensal (Últimos 12 meses)</h2>
          <div class="chart-container">
            <canvas id="combinadoChart_<?= md5($mountPoint) ?>" height="180"></canvas>
          </div>
        </div>

        <!-- 4. Projeção de Crescimento (36 meses) -->
        <div class="section">
          <h2>4. Projeção de Crescimento (36 meses)</h2>
          <div class="chart-container">
            <canvas id="projecaoChart_<?= md5($mountPoint) ?>" height="140"></canvas>
          </div>
        </div>

        <!-- 5. Recomendações de Expansão -->
        <div class="section">
          <h2>5. Recomendações de Expansão</h2>
          <p>Com base no crescimento médio da coleta (<?= htmlspecialchars($result['banco']['media_crescimento'] ?? 'N/D') ?>):</p>
          <table>
            <thead>
              <tr>
                <th>Período</th>
                <th>Crescimento Previsto</th>
                <th>Expansão Necessária</th>
                <th>Margem Livre Final</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($partition['expansao_recomendada'] as $meses => $exp): ?>
                <tr>
                  <td><?= $meses ?> meses</td>
                  <td><?= number_format($exp['crescimento_previsto_gb'], 2, ',', '.') ?> GB</td>
                  <td class="highlight">+ <?= number_format($exp['expansao_necessaria_gb'], 2, ',', '.') ?> GB</td>
                  <td><?= number_format($exp['margem_livre_gb'], 2, ',', '.') ?> GB (<?= $exp['percentual_livre_final'] ?>%)</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>

    <div style="text-align: center; margin: 40px 0;">
      <a href="index.php?action=executivo_pdf" class="btn btn-pdf">📥 Gerar PDF</a>
      <a href="index.php?action=volumetria" class="btn">← Voltar à Volumetria</a>
    </div>

    <div class="footer">
      Relatório confidencial – Gerado por CQLE Softwares | www.cqle.com.br<br>
      Data de geração: <?= date('d/m/Y H:i') ?>
    </div>
  </div>

  <script>
    // Dados reais dos últimos 12 meses (extraídos da sua tabela - Set/2024 a Ago/2025)
    const historicoData = {
      labels: [
        'Set/24', 'Out/24', 'Nov/24', 'Dez/24',
        'Jan/25', 'Fev/25', 'Mar/25', 'Abr/25',
        'Mai/25', 'Jun/25', 'Jul/25', 'Ago/25'
      ],
      totalUsage: [
        4059.4, 4311.4, 4604.7, 5007.5,
        5265.8, 5338.4, 5561.6, 5836.1,
        6133.0, 6474.2, 6782.3, 7073.0
      ],
      monthlyGrowth: [
        221.6, 252.0, 293.3, 402.8,
        258.3, 72.6, 223.2, 274.5,
        296.9, 341.2, 308.1, 290.7
      ]
    };

    <?php foreach ($analysis as $mountPoint => $partition): ?>
      // Gráfico Combinado: Total Usage (linha) + Crescimento Mensal (barras) - Últimos 12 meses reais
      const ctxCombinado<?= md5($mountPoint) ?> = document.getElementById('combinadoChart_<?= md5($mountPoint) ?>').getContext('2d');
      new Chart(ctxCombinado<?= md5($mountPoint) ?>, {
        type: 'bar',
        data: {
          labels: historicoData.labels,
          datasets: [{
              type: 'bar',
              label: 'Crescimento Mensal (GB)',
              data: historicoData.monthlyGrowth,
              backgroundColor: 'rgba(59, 130, 246, 0.7)',
              borderColor: '#3b82f6',
              borderWidth: 1,
              yAxisID: 'y'
            },
            {
              type: 'line',
              label: 'Uso Total (GB)',
              data: historicoData.totalUsage,
              borderColor: '#059669',
              backgroundColor: 'rgba(5, 150, 105, 0.2)',
              fill: true,
              tension: 0.3,
              pointRadius: 6,
              pointBackgroundColor: '#059669',
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top'
            },
            tooltip: {
              mode: 'index',
              intersect: false
            }
          },
          scales: {
            y: {
              type: 'linear',
              position: 'left',
              title: {
                display: true,
                text: 'Crescimento Mensal (GB)'
              },
              grid: {
                drawOnChartArea: false
              }
            },
            y1: {
              type: 'linear',
              position: 'right',
              title: {
                display: true,
                text: 'Uso Total (GB)'
              },
              grid: {
                drawOnChartArea: false
              }
            },
            x: {
              title: {
                display: true,
                text: 'Mês/Ano'
              }
            }
          }
        }
      });

      // Projeção 36 meses (dados reais da volumetria)
      const ctxProjecao<?= md5($mountPoint) ?> = document.getElementById('projecaoChart_<?= md5($mountPoint) ?>').getContext('2d');
      new Chart(ctxProjecao<?= md5($mountPoint) ?>, {
        type: 'line',
        data: {
          labels: <?= json_encode(array_column($partition['evolucao_crescimento'], 'mes')) ?>,
          datasets: [{
            label: 'Usado Projetado (GB)',
            data: <?= json_encode(array_column($partition['evolucao_crescimento'], 'usado_gb')) ?>,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            fill: true,
            tension: 0.4
          }, {
            label: 'Capacidade Necessária (~20% livre)',
            data: <?= json_encode(array_column($partition['evolucao_crescimento'], 'necessario_gb')) ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: false
            }
          }
        }
      });
    <?php endforeach; ?>
  </script>
</body>

</html>