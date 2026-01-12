* Utils/Calculator.php
<?php

namespace Utils;

class Calculator
{
  public static function calculateAverage(array $values, bool $excludeNegative = true): array
  {
    $validValues = [];
    $excludedCount = 0;

    foreach ($values as $value) {
      if (is_numeric($value)) {
        $numValue = floatval($value);

        if ($excludeNegative && $numValue <= 0) {
          $excludedCount++;
        } else {
          $validValues[] = $numValue;
        }
      } else {
        $excludedCount++;
      }
    }

    $count = count($validValues);
    $average = $count > 0 ? array_sum($validValues) / $count : 0;

    return [
      'media' => round($average, 2),
      'valores_validos' => $count,
      'valores_excluidos' => $excludedCount,
      'total_original' => count($values)
    ];
  }

  public static function formatSize(float $bytes): string
  {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;

    while ($bytes >= 1024 && $i < count($units) - 1) {
      $bytes /= 1024;
      $i++;
    }

    return round($bytes, 2) . ' ' . $units[$i];
  }

  public static function convertToGB(float $size, string $unit): float
  {
    $unit = strtoupper($unit);

    switch ($unit) {
      case 'TB':
        return $size * 1024;
      case 'MB':
        return $size / 1024;
      case 'GB':
      default:
        return $size;
    }
  }
}
