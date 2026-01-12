<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?> - Análise de Volumetria Oracle</title>
  <link rel="icon" type="image/x-icon" href="assets/images/cqle.ico">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      background: white;
      border-radius: 24px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
      max-width: 700px;
      width: 100%;
      padding: 50px;
      animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
    }

    .logo {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #7e22ce, #2a5298);
      border-radius: 20px;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      color: white;
      box-shadow: 0 10px 30px rgba(126, 34, 206, 0.3);
    }

    .header h1 {
      color: #1e293b;
      font-size: 32px;
      margin-bottom: 8px;
      font-weight: 700;
    }

    .header p {
      color: #64748b;
      font-size: 16px;
    }

    .author {
      text-align: center;
      color: #7e22ce;
      font-size: 13px;
      margin-top: 10px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .upload-area {
      border: 3px dashed #cbd5e1;
      border-radius: 20px;
      padding: 50px 40px;
      text-align: center;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .upload-area::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(126, 34, 206, 0.05), rgba(42, 82, 152, 0.05));
      opacity: 0;
      transition: opacity 0.3s;
    }

    .upload-area:hover {
      border-color: #7e22ce;
      transform: translateY(-2px);
      box-shadow: 0 15px 40px rgba(126, 34, 206, 0.15);
    }

    .upload-area:hover::before {
      opacity: 1;
    }

    .upload-area.dragover {
      border-color: #7e22ce;
      background: linear-gradient(135deg, rgba(126, 34, 206, 0.1), rgba(42, 82, 152, 0.1));
      transform: scale(1.02);
    }

    .upload-icon {
      font-size: 60px;
      margin-bottom: 20px;
      display: inline-block;
      animation: bounce 2s infinite;
    }

    @keyframes bounce {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-10px);
      }
    }

    .upload-text {
      color: #334155;
      margin-bottom: 12px;
      font-size: 18px;
      font-weight: 600;
    }

    .upload-hint {
      color: #64748b;
      font-size: 14px;
      line-height: 1.6;
    }

    .upload-hint strong {
      color: #7e22ce;
    }

    input[type="file"] {
      display: none;
    }

    .file-info {
      margin-top: 25px;
      padding: 20px;
      background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%);
      border-radius: 15px;
      border-left: 4px solid #10b981;
      display: none;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .file-info.show {
      display: block;
    }

    .file-name {
      color: #065f46;
      font-weight: 700;
      margin-bottom: 8px;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .file-size {
      color: #059669;
      font-size: 14px;
    }

    .btn {
      width: 100%;
      padding: 18px;
      background: linear-gradient(135deg, #7e22ce 0%, #2a5298 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 25px;
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(126, 34, 206, 0.3);
      letter-spacing: 0.5px;
    }

    .btn:hover:not(:disabled) {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(126, 34, 206, 0.4);
    }

    .btn:active:not(:disabled) {
      transform: translateY(-1px);
    }

    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    .error {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #991b1b;
      padding: 18px 20px;
      border-radius: 12px;
      margin-bottom: 25px;
      border-left: 4px solid #dc2626;
      display: <?= isset($error) ? 'block' : 'none' ?>;
      animation: shake 0.5s;
    }

    @keyframes shake {

      0%,
      100% {
        transform: translateX(0);
      }

      25% {
        transform: translateX(-10px);
      }

      75% {
        transform: translateX(10px);
      }
    }

    .error strong {
      font-weight: 700;
    }

    .info-box {
      background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
      border-left: 4px solid #3b82f6;
      padding: 20px;
      margin-top: 35px;
      border-radius: 12px;
    }

    .info-box h3 {
      color: #1e40af;
      font-size: 17px;
      margin-bottom: 15px;
      font-weight: 700;
    }

    .info-box ul {
      color: #1e3a8a;
      font-size: 14px;
      margin-left: 20px;
      line-height: 1.8;
    }

    .info-box li {
      margin-bottom: 8px;
    }

    .info-box strong {
      color: #1e40af;
    }

    .formats {
      display: flex;
      gap: 15px;
      margin-top: 20px;
      justify-content: center;
    }

    .format-badge {
      background: white;
      padding: 10px 20px;
      border-radius: 20px;
      font-size: 13px;
      color: #64748b;
      font-weight: 600;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255, 255, 255, .3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div class="logo">📊</div>
      <h1><?= APP_NAME ?></h1>
      <p>Análise Inteligente de Volumetria Oracle</p>
      <div class="author">🏆 <?= APP_AUTHOR ?></div>
    </div>

    <?php if (isset($error)): ?>
      <div class="error">
        <strong>⚠️ Erro:</strong> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=process" enctype="multipart/form-data" id="uploadForm">
      <div class="upload-area" id="uploadArea">
        <div class="upload-icon">📁</div>
        <div class="upload-text">
          <strong>Clique para selecionar</strong> ou arraste o arquivo aqui
        </div>
        <div class="upload-hint">
          Formatos aceitos: <strong>.txt</strong> ou <strong>.log</strong><br>
          Tamanho máximo: <strong>100 MB</strong>
        </div>
        <input type="file" name="volumetria_file" id="volumetria_file" accept=".txt,.log" required>
      </div>

      <div class="file-info" id="fileInfo">
        <div class="file-name" id="fileName"></div>
        <div class="file-size" id="fileSize"></div>
      </div>

      <button type="submit" class="btn" id="submitBtn" disabled>
        🚀 Processar Arquivo
      </button>
    </form>

    <div class="info-box">
      <h3>📋 Formatos Suportados:</h3>
      <div class="formats">
        <div class="format-badge">CQLE_VOLUMETRIA</div>
        <div class="format-badge">Formato Gricki</div>
      </div>
      <ul>
        <li><strong>Servidor:</strong> Hostname, IP, SO, Memória, CPU, Filesystem</li>
        <li><strong>Banco:</strong> Instância, Tamanho, Crescimento, Archives, Tablespaces</li>
        <li><strong>Backup:</strong> RMAN, DataPump, Tipos e Tamanhos</li>
      </ul>
    </div>
  </div>

  <script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('volumetria_file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');

    uploadArea.addEventListener('click', () => fileInput.click());

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      uploadArea.addEventListener(eventName, () => {
        uploadArea.classList.add('dragover');
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      uploadArea.addEventListener(eventName, () => {
        uploadArea.classList.remove('dragover');
      }, false);
    });

    uploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      fileInput.files = files;
      handleFiles(files);
    }

    fileInput.addEventListener('change', function() {
      handleFiles(this.files);
    });

    function handleFiles(files) {
      if (files.length > 0) {
        const file = files[0];
        fileName.innerHTML = '✅ <strong>' + file.name + '</strong>';
        fileSize.textContent = '💾 Tamanho: ' + formatFileSize(file.size);
        fileInfo.classList.add('show');
        submitBtn.disabled = false;
      }
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    document.getElementById('uploadForm').addEventListener('submit', function() {
      submitBtn.innerHTML = '<span class="loading"></span> Processando...';
      submitBtn.disabled = true;
    });
  </script>
</body>

</html>