<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?> - Análise Profissional</title>
  <link rel="icon" type="image/x-icon" href="assets/images/cqle.ico">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 30px 20px;
      color: #1a202c;
    }

    .container {
      max-width: 1600px;
      margin: 0 auto;
    }

    /* HEADER */
    .header {
      background: white;
      border-radius: 24px;
      padding: 40px 50px;
      margin-bottom: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
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
      background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
    }

    .header h1 {
      color: #1a202c;
      font-size: 32px;
      font-weight: 800;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .header-badge {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      padding: 8px 20px;
      border-radius: 30px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .meta {
      display: flex;
      gap: 35px;
      flex-wrap: wrap;
      color: #64748b;
      font-size: 14px;
      font-weight: 500;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .meta-item strong {
      color: #1a202c;
      font-weight: 600;
    }

    .actions {
      margin-top: 25px;
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
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-family: 'Inter', sans-serif;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
    }

    .btn-secondary {
      background: #f8fafc;
      color: #475569;
      border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
      background: #e2e8f0;
      transform: translateY(-2px);
    }

    /* CARDS */
    .card {
      background: white;
      border-radius: 20px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .card-header {
      background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
      color: white;
      padding: 25px 35px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 4px solid #667eea;
    }

    .card-header h2 {
      font-size: 24px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 0;
    }

    .card-body {
      padding: 35px;
    }

    .badge {
      padding: 8px 20px;
      border-radius: 30px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .badge-success {
      background: #d4edda;
      color: #155724;
    }

    .badge-warning {
      background: #fff3cd;
      color: #856404;
    }

    .badge-danger {
      background: #f8d7da;
      color: #721c24;
    }

    .badge-info {
      background: #d1ecf1;
      color: #0c5460;
    }

    /* INFO GRID */
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .info-box {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border: 2px solid #e2e8f0;
      border-radius: 16px;
      padding: 25px;
      text-align: center;
      transition: all 0.3s ease;
    }

    .info-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border-color: #667eea;
    }

    .info-label {
      color: #64748b;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
    }

    .info-value {
      color: #1a202c;
      font-size: 28px;
      font-weight: 800;
      line-height: 1.2;
    }

    .info-value-small {
      font-size: 20px;
    }

    /* FILESYSTEM TABLE */
    .table-container {
      overflow-x: auto;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    .table thead {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .table th {
      padding: 18px 15px;
      text-align: left;
      font-weight: 700;
      color: #475569;
      text-transform: uppercase;
      font-size: 12px;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e2e8f0;
    }

    .table td {
      padding: 16px 15px;
      border-bottom: 1px solid #f1f5f9;
    }

    .table tbody tr {
      transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
      background-color: #f8fafc;
    }

    .table tr.bg-danger {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #991b1b;
      font-weight: 700;
    }

    .table tr.bg-warning {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #92400e;
      font-weight: 600;
    }

    .table tr.bg-success {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      color: #065f46;
    }

    /* INSTANCE SEPARATOR */
    .instance-separator {
      border-top: 3px dashed #e2e8f0;
      margin: 40px 0;
      padding-top: 40px;
    }

    .instance-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 30px;
      border-radius: 16px;
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .instance-header h3 {
      font-size: 22px;
      font-weight: 700;
      margin: 0;
    }

    /* BACKUP CARDS */
    .backup-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px;
    }

    .backup-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border: 2px solid #e2e8f0;
      border-radius: 16px;
      padding: 25px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .backup-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #667eea, #764ba2);
    }

    .backup-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      border-color: #667eea;
    }

    .backup-type {
      font-size: 18px;
      font-weight: 700;
      color: #1a202c;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .backup-info {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #f1f5f9;
      font-size: 14px;
    }

    .backup-info:last-child {
      border-bottom: none;
    }

    .backup-info-label {
      color: #64748b;
      font-weight: 600;
    }

    .backup-info-value {
      color: #1a202c;
      font-weight: 700;
    }

    .recommendation {
      margin-top: 25px;
      padding: 20px;
      background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
      border-left: 4px solid #3b82f6;
      border-radius: 12px;
      color: #1e40af;
      font-size: 14px;
      font-weight: 600;
      line-height: 1.6;
    }

    /* EMPTY STATE */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #94a3b8;
    }

    .empty-state-icon {
      font-size: 64px;
      margin-bottom: 20px;
      opacity: 0.5;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .header {
        padding: 30px 25px;
      }

      .header h1 {
        font-size: 24px;
      }

      .card-body {
        padding: 25px;
      }

      .info-grid {
        grid-template-columns: 1fr;
      }

      .backup-grid {
        grid-template-columns: 1fr;
      }
    }

    /* ANIMATIONS */
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

    .card {
      animation: fadeInUp 0.6s ease-out;
    }

    .card:nth-child(1) {
      animation-delay: 0.1s;
    }

    .card:nth-child(2) {
      animation-delay: 0.2s;
    }

    .card:nth-child(3) {
      animation-delay: 0.3s;
    }

    .card:nth-child(4) {
      animation-delay: 0.4s;
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- HEADER -->
    <div class="header">
      <h1>
        <span>🎯</span>
        Análise de Volumetria Oracle
        <span class="header-badge">v2.0 PRO</span>
      </h1>
      <div class="meta">
        <span class="meta-item">
          <span>📄</span>
          <strong>Arquivo:</strong> <?= htmlspecialchars($result['arquivo_original'] ?? 'N/D') ?>
        </span>
        <span class="meta-item">
          <span>📦</span>
          <strong>Formato:</strong> TXT
        </span>
        <span class="meta-item">
          <span>⏰</span>
          <strong>Processado:</strong> <?= htmlspecialchars($result['data_processamento'] ?? 'N/D') ?>
        </span>
      </div>
      <div class="actions">
        <a href="index.php" class="btn btn-secondary">
          ← Nova Análise
        </a>
        <a href="index.php?action=export&format=json" class="btn btn-primary">
          📥 Exportar JSON
        </a>
        <a href="index.php?action=export&format=txt" class="btn btn-primary">
          📄 Exportar TXT
        </a>
      </div>
    </div>

    <!-- SERVIDOR -->
    <div class="card">
      <div class="card-header">
        <h2><span>🖥️</span> Servidor</h2>
      </div>
      <div class="card-body">
        <?php
        $servidor = $result['servidor'] ?? [];
        $camposServidor = [
          'Hostname' => $servidor['Hostname'] ?? 'N/D',
          'IP' => $servidor['IP'] ?? 'N/D',
          'S.O' => $servidor['S.O'] ?? 'N/D',
          'Versão Banco' => $servidor['Versão Banco'] ?? 'N/D',
          'Fabricante' => $servidor['Fabricante'] ?? 'N/D',
          'Modelo' => $servidor['Modelo'] ?? 'N/D',
          'Service Tag' => $servidor['Service Tag'] ?? 'N/D',
          'Qtd. Proc' => $servidor['Qtd. Proc'] ?? 'N/D',
          'Modelo. Proc' => $servidor['Modelo. Proc'] ?? 'N/D',
          'Multi-Processamento' => $servidor['Multi-Processamento'] ?? 'N/D',
          'Memória' => $servidor['Memória'] ?? 'N/D'
        ];
        ?>
        <div class="info-grid">
          <?php foreach ($camposServidor as $label => $valor): ?>
            <div class="info-box">
              <div class="info-label"><?= htmlspecialchars($label) ?></div>
              <div class="info-value info-value-small"><?= htmlspecialchars($valor) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- FILESYSTEM -->
    <?php if (isset($result['servidor']['filesystem_analysis'])): ?>
      <div class="card">
        <div class="card-header">
          <h2><span>💾</span> Filesystem</h2>
          <?php
          $fsStatus = strtolower($result['servidor']['filesystem_analysis']['status_geral']);
          $badgeClass = $fsStatus === 'crítico' ? 'badge-danger' : ($fsStatus === 'atenção' ? 'badge-warning' : 'badge-success');
          ?>
          <span class="badge <?= $badgeClass ?>">
            <?= strtoupper($result['servidor']['filesystem_analysis']['status_geral']) ?>
          </span>
        </div>
        <div class="card-body" style="padding: 0;">
          <?php
          $fsAnalysis = $result['servidor']['filesystem_analysis'];
          $allFS = array_merge(
            $fsAnalysis['critical'] ?? [],
            $fsAnalysis['warnings'] ?? [],
            $fsAnalysis['healthy'] ?? []
          );

          if (empty($allFS)): ?>
            <div class="empty-state">
              <div class="empty-state-icon">📂</div>
              <p>Nenhum filesystem detectado</p>
            </div>
          <?php else: ?>
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>MOUNT POINT</th>
                    <th>TAMANHO</th>
                    <th>UTILIZADO</th>
                    <th>LIVRE</th>
                    <th style="text-align: center;">USO %</th>
                    <th style="text-align: center;">STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($allFS as $fs):
                    $usage = $fs['use_percent'] ?? 0;
                    $rowClass = $usage >= 90 ? 'bg-danger' : ($usage >= 75 ? 'bg-warning' : ($usage >= 50 ? '' : 'bg-success'));
                    $statusBadge = $usage >= 90 ? 'badge-danger' : ($usage >= 75 ? 'badge-warning' : 'badge-success');
                    $statusText = $usage >= 90 ? 'CRÍTICO' : ($usage >= 75 ? 'ATENÇÃO' : 'OK');
                  ?>
                    <tr class="<?= $rowClass ?>">
                      <td style="font-weight: 700;">
                        <?= htmlspecialchars($fs['mounted_on'] ?? $fs['mount_point'] ?? $fs['device'] ?? 'N/D') ?>
                      </td>
                      <td><?= htmlspecialchars($fs['size'] ?? 'N/D') ?></td>
                      <td><?= htmlspecialchars($fs['used'] ?? 'N/D') ?></td>
                      <td><?= htmlspecialchars($fs['avail'] ?? 'N/D') ?></td>
                      <td style="text-align: center; font-weight: 800; font-size: 16px;">
                        <?= $usage ?>%
                      </td>
                      <td style="text-align: center;">
                        <span class="badge <?= $statusBadge ?>">
                          <?= $statusText ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- BANCO DE DADOS -->
    <div class="card">
      <div class="card-header">
        <h2><span>🗄️</span> Banco de Dados</h2>
        <?php if (isset($result['banco']['status'])): ?>
          <span class="badge <?= $result['banco']['status'] === 'analisado' ? 'badge-success' : 'badge-warning' ?>">
            <?= strtoupper($result['banco']['status'] ?? 'PENDENTE') ?>
          </span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php
        $banco = $result['banco'] ?? [];
        $instancias = $banco['instancias'] ?? [];
        if (empty($instancias) && !empty($banco)) {
          $instancias = [$banco];
        }

        if (empty($instancias)): ?>
          <div class="empty-state">
            <div class="empty-state-icon">🗄️</div>
            <p>Nenhuma informação de banco detectada</p>
          </div>
        <?php else: ?>
          <?php foreach ($instancias as $index => $inst): ?>
            <?php if ($index > 0): ?>
              <div class="instance-separator"></div>
            <?php endif; ?>

            <div class="instance-header">
              <div>
                <h3>Instância: <?= htmlspecialchars($inst['instancia'] ?? 'N/D') ?></h3>
                <div style="margin-top: 5px; font-size: 14px; opacity: 0.9;">
                  Tipo: <strong><?= htmlspecialchars($inst['tipo'] ?? 'N/D') ?></strong>
                </div>
              </div>
              <span class="badge badge-info">
                #<?= $index + 1 ?>
              </span>
            </div>

            <div class="info-grid">
              <div class="info-box">
                <div class="info-label">Tamanho Total</div>
                <div class="info-value"><?= htmlspecialchars($inst['tamanho_total_gb'] ?? 0) ?> GB</div>
              </div>

              <div class="info-box">
                <div class="info-label">Crescimento Mensal</div>
                <div class="info-value"><?= htmlspecialchars($inst['crescimento'] ?? 0) ?> GB</div>
              </div>

              <div class="info-box">
                <div class="info-label">Geração de Archives</div>
                <div class="info-value info-value-small"><?= htmlspecialchars($inst['geracao_archives_formatted'] ?? '0 MB') ?></div>
              </div>

              <div class="info-box">
                <div class="info-label">Tamanho de Datafiles</div>
                <div class="info-value"><?= htmlspecialchars($inst['tamanho_datafiles_gb'] ?? 0) ?> GB</div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- BACKUP -->
    <div class="card">
      <div class="card-header">
        <h2><span>💾</span> Backup</h2>
        <?php
        $backupStatus = $result['backup']['status_class'] ?? 'critico';
        $badgeClass = $backupStatus === 'excelente' ? 'badge-success' : ($backupStatus === 'atencao' ? 'badge-warning' : 'badge-danger');
        ?>
        <span class="badge <?= $badgeClass ?>">
          <?= strtoupper($result['backup']['status'] ?? 'NÃO CONFIGURADO') ?>
        </span>
      </div>
      <div class="card-body">
        <?php if (isset($result['backup']['backups']) && count($result['backup']['backups']) > 0): ?>
          <div style="margin-bottom: 30px;">
            <div class="info-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
              <div class="info-box">
                <div class="info-label">Total de Backups</div>
                <div class="info-value"><?= $result['backup']['total_backups'] ?></div>
              </div>
              <div class="info-box">
                <div class="info-label">Tipos Detectados</div>
                <div class="info-value info-value-small"><?= htmlspecialchars($result['backup']['tipos_detectados']) ?></div>
              </div>
              <div class="info-box">
                <div class="info-label">Tamanho Total</div>
                <div class="info-value info-value-small"><?= htmlspecialchars($result['backup']['tamanho_total_formatado']) ?></div>
              </div>
            </div>
          </div>

          <div class="backup-grid">
            <?php foreach ($result['backup']['backups'] as $bkp): ?>
              <div class="backup-card">
                <div class="backup-type">
                  <span>🔒</span>
                  <?= htmlspecialchars($bkp['tipo']) ?>
                </div>
                <div class="backup-info">
                  <span class="backup-info-label">Diretório</span>
                  <span class="backup-info-value"><?= htmlspecialchars($bkp['diretorio']) ?></span>
                </div>
                <div class="backup-info">
                  <span class="backup-info-label">Tamanho</span>
                  <span class="backup-info-value"><?= htmlspecialchars($bkp['tamanho_formatado']) ?></span>
                </div>
                <div class="backup-info">
                  <span class="backup-info-label">Horário</span>
                  <span class="backup-info-value"><?= htmlspecialchars($bkp['horario_inicio']) ?></span>
                </div>
                <div class="backup-info">
                  <span class="backup-info-label">Duração</span>
                  <span class="backup-info-value"><?= htmlspecialchars($bkp['duracao_media']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (isset($result['backup']['recommendation'])): ?>
            <div class="recommendation">
              💡 <strong>Recomendação:</strong> <?= htmlspecialchars($result['backup']['recommendation']) ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="empty-state">
            <div class="empty-state-icon">⚠️</div>
            <p><strong>CRÍTICO:</strong> Nenhum backup configurado detectado!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</body>

</html>