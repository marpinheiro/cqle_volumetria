<?php
/**
 * Utils/FileValidator.php
 */
namespace Utils;

class FileValidator
{
    private const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB
    private const ALLOWED_EXTENSIONS = ['txt', 'log'];
    
    public function validate(array $file): array
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'Nenhum arquivo foi enviado'];
        }
        
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'message' => 'Arquivo muito grande. Máximo: 100MB'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'message' => 'Formato inválido. Use: .txt ou .log'];
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = ['text/plain', 'application/octet-stream'];
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'message' => 'Arquivo não é texto válido'];
        }
        
        return ['valid' => true, 'message' => 'Válido'];
    }
}
