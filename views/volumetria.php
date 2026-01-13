<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?> - Estudo de Volumetria</title>
  <link rel="icon" type="image/x-icon" href="assets/images/cqle.ico">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
      min-height: 100vh;
      padding: 30px 20px;
      color: #1a202c;
    }

    .container {
      max-width: 1800px;
      margin: 0 auto;
    }

    /* HEADER */
    .header {
      background: white;
      border-radius: 24px;
      padding: 40px 50px;
      margin-bottom: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      position: relative;
      overflow: hidden;
    }

    .header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #10b981, #059669, #047857);
    }

    .header h1 {
      color: #1a202c;
      font-size: 36px;
      font-weight: 900;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .header-badge {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      padding: 8px 20px;
      border-radius: 30px;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .header-subtitle {
      color: #64748b;
      font-size: 16px;
      margin-bottom: 25px;
    }

    .actions {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    .btn {
      padding: 14px 28px;
      border-radius: 12px;
      border: none;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-family: 'Inter', sans-serif;
    }

    .btn-primary {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(16, 185, 129, 0.5);
    }

    .btn-secondary {
      background: #f8fafc;
      color: #475569;
      border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
      background: #e2e8f0;
    }

    .partition-card {
      background: white;
      border-radius: 20px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      animation: fadeInUp 0.6s ease-out;
    }

    .partition-header {
      background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
      color: white;
      padding: 25px 35px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .partition-title {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .partition-title h2 {
      font-size: 24px;
      font-weight: 700;
      margin: 0;
    }

    .partition-type {
      background: rgba(255, 255, 255, 0.2);
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .criticality-badge {
      padding: 10px 24px;
      border-radius: 30px;
      font-size: 14px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .criticality-critico {
      background: #fee2e2;
      color: #991b1b;
      animation: pulse 2s infinite;
    }

    .criticality-atencao {
      background: #fef3c7;
      color: #92400e;
    }

    .criticality-monitorar {
      background: #dbeafe;
      color: #1e40af;
    }

    .criticality-estavel {
      background: #d1fae5;
      color: #065f46;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.7;
      }
    }

    .partition-body {
      padding: 35px;
    }

    .capacity-overview {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .capacity-box {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border: 2px solid #e2e8f0;
      border-radius: 16px;
      padding: 20px;
      text-align: center;
      transition: all 0.3s ease;
    }

    .capacity-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border-color: #10b981;
    }

    .capacity-label {
      color: #64748b;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .capacity-value {
      color: #1a202c;
      font-size: 24px;
      font-weight: 800;
    }

    .consumption-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .consumption-item {
      background: white;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .consumption-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .consumption-icon.datafiles {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .consumption-icon.archives {
      background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .consumption-icon.backups {
      background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .consumption-icon.outros {
      background: linear-gradient(135deg, #6b7280, #4b5563);
    }

    .consumption-info h4 {
      color: #64748b;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      margin-bottom: 5px;
    }

    .consumption-info p {
      color: #1a202c;
      font-size: 20px;
      font-weight: 800;
    }

    .prediction-table {
      background: #f8fafc;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 30px;
    }

    .prediction-table h3 {
      color: #1a202c;
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 8px;
      overflow: hidden;
    }

    .table th {
      background: linear-gradient(135deg, #1a202c, #2d3748);
      color: white;
      padding: 15px;
      text-align: left;
      font-weight: 700;
      font-size: 13px;
      text-transform: uppercase;
    }

    .table td {
      padding: 15px;
      border-bottom: 1px solid #f1f5f9;
    }

    .table tr:hover {
      background: #f8fafc;
    }

    .highlight-value {
      color: #10b981;
      font-weight: 800;
      font-size: 16px;
    }

    .chart-container {
      background: white;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 30px;
    }

    .chart-container h3 {
      color: #1a202c;
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 20px;
    }

    canvas {
      max-height: 400px;
    }

    .alert {
      padding: 20px 25px;
      border-radius: 12px;
      margin-bottom: 20px;
      display: flex;
      align-items: start;
      gap: 15px;
      font-weight: 600;
    }

    .alert-icon {
      font-size: 24px;
    }

    .alert-critical {
      background: linear-gradient(135deg, #fee2e2, #fecaca);
      border-left: 4px solid #dc2626;
      color: #991b1b;
    }

    .alert-warning {
      background: linear-gradient(135deg, #fef3c7, #fde68a);
      border-left: 4px solid #f59e0b;
      color: #92400e;
    }

    .alert-info {
      background: linear-gradient(135deg, #dbeafe, #bfdbfe);
      border-left: 4px solid #3b82f6;
      color: #1e40af;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #94a3b8;
    }

    .empty-state-icon {
      font-size: 72px;
      margin-bottom: 20px;
      opacity: 0.5;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>
        <span>📊</span>
        Estudo de Volumetria
        <span class="header-badge">ANÁLISE PREDITIVA</span>
      </h1>
      <p class="header-subtitle">
        Análise detalhada por partição com projeções de crescimento e recomendações de expansão
      </p>
      <div class="actions">
        <button onclick="window.history.back()" class="btn btn-secondary">
          ← Voltar aos Resultados
        </button>
        <a href="index.php?action=executivo" class="btn btn-primary" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
          📄 Relatório Executivo
        </a>
        <a href="index.php" class="btn btn-secondary">
          🏠 Nova Análise
        </a>
      </div>
    </div>

    <?php if (empty($analysis)): ?>
      <div class="partition-card">
        <div class="partition-body">
          <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3>Nenhum dado disponível para análise</h3>
            <p>Não foram encontradas partições com dados relacionados (datafiles, archives ou backups).</p>
          </div>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($analysis as $mountPoint => $partition): ?>
        <div class="partition-card">
          <div class="partition-header">
            <div class="partition-title">
              <h2><?= htmlspecialchars($mountPoint) ?></h2>
              <span class="partition-type"><?= htmlspecialchars($partition['tipo']) ?></span>
            </div>
            <span class="criticality-badge criticality-<?= strtolower(str_replace(['Í', 'Ã', 'Ç'], ['i', 'a', 'c'], $partition['previsao']['nivel_criticidade'])) ?>">
              <?= htmlspecialchars($partition['previsao']['nivel_criticidade']) ?>
            </span>
          </div>

          <div class="partition-body">
            <?php if ($partition['previsao']['nivel_criticidade'] === 'CRÍTICO'): ?>
              <div class="alert alert-critical">
                <span class="alert-icon">🚨</span>
                <div>
                  <strong>ATENÇÃO URGENTE!</strong><br>
                  <?php if ($partition['previsao']['meses_ate_esgotamento']): ?>
                    Partição estará cheia em aproximadamente <strong><?= $partition['previsao']['meses_ate_esgotamento'] ?> meses</strong>
                    (<?= htmlspecialchars($partition['previsao']['data_esgotamento']) ?>)
                  <?php else: ?>
                    Partição com uso crítico atual: <strong><?= $partition['capacidade']['uso_percent'] ?>%</strong>
                  <?php endif; ?>
                </div>
              </div>
            <?php elseif ($partition['previsao']['nivel_criticidade'] === 'ATENÇÃO'): ?>
              <div class="alert alert-warning">
                <span class="alert-icon">⚠️</span>
                <div>
                  <strong>Monitoramento Necessário</strong><br>
                  <?php if ($partition['previsao']['meses_ate_esgotamento']): ?>
                    Espaço estimado para <?= $partition['previsao']['meses_ate_esgotamento'] ?> meses
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <div class="capacity-overview">
              <div class="capacity-box">
                <div class="capacity-label">Capacidade Total</div>
                <div class="capacity-value"><?= number_format($partition['capacidade']['total_gb'], 2, ',', '.') ?> GB</div>
              </div>
              <div class="capacity-box">
                <div class="capacity-label">Espaço Usado</div>
                <div class="capacity-value"><?= number_format($partition['capacidade']['usado_gb'], 2, ',', '.') ?> GB</div>
              </div>
              <div class="capacity-box">
                <div class="capacity-label">Espaço Livre</div>
                <div class="capacity-value"><?= number_format($partition['capacidade']['livre_gb'], 2, ',', '.') ?> GB</div>
              </div>
              <div class="capacity-box">
                <div class="capacity-label">Uso Atual</div>
                <div class="capacity-value"><?= number_format($partition['capacidade']['uso_percent'], 1) ?>%</div>
              </div>
            </div>

            <h3 style="margin-bottom: 20px; color: #1a202c;">💾 Consumo Detalhado por Tipo</h3>
            <div class="consumption-grid">
              <div class="consumption-item">
                <div class="consumption-icon datafiles">📁</div>
                <div class="consumption-info">
                  <h4>Datafiles</h4>
                  <p><?= number_format($partition['consumo_detalhado']['datafiles_gb'], 2, ',', '.') ?> GB</p>
                </div>
              </div>
              <div class="consumption-item">
                <div class="consumption-icon archives">📦</div>
                <div class="consumption-info">
                  <h4>Archives (diário)</h4>
                  <p><?= number_format($partition['consumo_detalhado']['archives_diario_gb'], 2, ',', '.') ?> GB</p>
                </div>
              </div>
              <div class="consumption-item">
                <div class="consumption-icon backups">💾</div>
                <div class="consumption-info">
                  <h4>Backups (diário)</h4>
                  <p><?= number_format($partition['consumo_detalhado']['backups_diario_gb'], 2, ',', '.') ?> GB</p>
                </div>
              </div>
              <div class="consumption-item">
                <div class="consumption-icon outros">📊</div>
                <div class="consumption-info">
                  <h4>Outros</h4>
                  <p><?= number_format($partition['consumo_detalhado']['outros_gb'], 2, ',', '.') ?> GB</p>
                </div>
              </div>
            </div>

            <div class="alert alert-info">
              <span class="alert-icon">📈</span>
              <div>
                <strong>Crescimento Médio:</strong>
                <?= number_format($partition['crescimento']['mensal_gb'], 2, ',', '.') ?> GB/mês |
                <?= number_format($partition['crescimento']['anual_gb'], 2, ',', '.') ?> GB/ano
              </div>
            </div>

            <div class="prediction-table">
              <h3>🎯 Recomendações de Expansão </h3>
              <table class="table">
                <thead>
                  <tr>
                    <th>Período</th>
                    <th>Crescimento</th>
                    <th>Usado Futuro</th>
                    <th>Total Necessário</th>
                    <th>Expansão</th>
                    <th>% Livre Final</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($partition['expansao_recomendada'] as $meses => $exp): ?>
                    <tr>
                      <td><strong><?= $meses ?> meses</strong></td>
                      <td><?= number_format($exp['crescimento_previsto_gb'], 2, ',', '.') ?> GB</td>
                      <td><?= number_format($exp['usado_futuro_gb'], 2, ',', '.') ?> GB</td>
                      <td class="highlight-value"><?= number_format($exp['total_necessario_gb'], 2, ',', '.') ?> GB</td>
                      <td><strong><?= number_format($exp['expansao_necessaria_gb'], 2, ',', '.') ?> GB</strong></td>
                      <td><?= number_format($exp['percentual_livre_final'], 1, ',', '.') ?>%</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <?php if (!empty($partition['evolucao_crescimento'])): ?>
              <div class="chart-container">
                <h3>📊 Projeção de Crescimento (36 meses)</h3>
                <canvas id="chart-<?= md5($mountPoint) ?>"></canvas>
              </div>

              <script>
                const ctx<?= md5($mountPoint) ?> = document.getElementById('chart-<?= md5($mountPoint) ?>').getContext('2d');
                new Chart(ctx<?= md5($mountPoint) ?>, {
                  type: 'line',
                  data: {
                    labels: <?= json_encode(array_column($partition['evolucao_crescimento'], 'mes')) ?>,
                    datasets: [{
                        label: 'Usado Projetado',
                        data: <?= json_encode(array_column($partition['evolucao_crescimento'], 'usado_gb')) ?>,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#ef4444'
                      },
                      {
                        label: 'Capacidade Atual',
                        data: <?= json_encode(array_column($partition['evolucao_crescimento'], 'capacidade_atual_gb')) ?>,
                        borderColor: '#f59e0b',
                        borderWidth: 3,
                        borderDash: [10, 5],
                        fill: false,
                        pointRadius: 0
                      },
                      {
                        label: 'Capacidade Necessária (~900GB livre)',
                        data: <?= json_encode(array_column($partition['evolucao_crescimento'], 'necessario_gb')) ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981'
                      }
                    ]
                  },
                  options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                      mode: 'index',
                      intersect: false
                    },
                    plugins: {
                      legend: {
                        display: true,
                        position: 'top',
                        labels: {
                          usePointStyle: true,
                          padding: 15,
                          font: {
                            size: 12,
                            weight: 'bold'
                          }
                        }
                      },
                      tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                          size: 14,
                          weight: 'bold'
                        },
                        bodyFont: {
                          size: 13
                        },
                        callbacks: {
                          label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' GB';
                          }
                        }
                      }
                    },
                    scales: {
                      y: {
                        beginAtZero: true,
                        ticks: {
                          callback: function(value) {
                            return value.toFixed(0) + ' GB';
                          },
                          font: {
                            size: 11
                          }
                        },
                        grid: {
                          color: 'rgba(0, 0, 0, 0.05)'
                        }
                      },
                      x: {
                        ticks: {
                          font: {
                            size: 11
                          }
                        },
                        grid: {
                          display: false
                        }
                      }
                    }
                  }
                });
              </script>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>

</html>