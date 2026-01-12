@echo off
REM ===============================
REM Estrutura CQLE Volumetria
REM Executar dentro da pasta cqle_volumetria
REM ===============================

echo Criando arquivos raiz...
type nul > index.php
type nul > config.php

echo Criando pastas Controllers...
mkdir Controllers
type nul > Controllers\VolumetriaController.php

echo Criando pastas Services...
mkdir Services
type nul > Services\FileParserService.php
type nul > Services\ServerAnalyzer.php
type nul > Services\DatabaseAnalyzer.php
type nul > Services\BackupAnalyzer.php

echo Criando pastas Utils...
mkdir Utils
type nul > Utils\Calculator.php
type nul > Utils\FileValidator.php

echo Criando pastas views...
mkdir views
type nul > views\upload.php
type nul > views\results.php

echo Criando pastas assets...
mkdir assets
mkdir assets\css
mkdir assets\js
mkdir assets\images
type nul > assets\images\cqle.ico

echo Criando pasta uploads...
mkdir uploads

echo.
echo Estrutura criada com sucesso!
pause
