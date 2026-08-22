<?php

namespace App\Imports;

class DapodikImport extends SiswaDataImport
{
    /**
     * Build colMap from a raw header row array (used by chunked page import).
     */
    public function buildColMap(array $headerRow): array
    {
        $colMap = [];
        foreach ($headerRow as $idx => $cell) {
            $key = $this->normalizeKey((string) $cell);
            if ($key !== '') {
                $colMap[$key] = $idx;
            }
        }
        return $colMap;
    }
}
