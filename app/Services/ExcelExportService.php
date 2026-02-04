<?php

namespace App\Services;

class ExcelExportService
{
    /**
     * Cria um arquivo Excel (.xlsx) a partir de dados usando XML SpreadsheetML
     * Funciona sem ZipArchive, gerando um XML que o Excel pode abrir
     */
    public static function criarExcel($dados, $nomeArquivo, $titulo = '')
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_') . '.xml';
        
        $xml = self::getSpreadsheetML($dados, $titulo);
        
        file_put_contents($tempFile, $xml);

        return $tempFile;
    }

    private static function getSpreadsheetML($dados, $titulo = '')
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Header">
   <Font ss:Bold="1"/>
   <Interior ss:Color="#CCCCCC" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="Title">
   <Font ss:Size="14" ss:Bold="1"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Planilha1">
  <Table>';

        $rowIndex = 0;
        $headerRow = null;
        $primeiraLinhaComDados = null;

        // Dados (já incluem título e cabeçalhos)
        foreach ($dados as $row) {
            // Pular linhas vazias para detectar cabeçalho
            $isLinhaVazia = empty($row) || (isset($row[0]) && empty($row[0]));
            
            if (!$isLinhaVazia && $primeiraLinhaComDados === null) {
                $primeiraLinhaComDados = $rowIndex;
            }
            
            // Detectar cabeçalho: primeira linha não vazia após título (geralmente linha 3 ou 4)
            if ($headerRow === null && !$isLinhaVazia && $rowIndex > 1 && isset($row[0]) && !is_numeric($row[0])) {
                // Verificar se parece ser cabeçalho (contém palavras como "ID", "Categoria", "Valor", etc.)
                $palavrasCabecalho = ['id', 'categoria', 'valor', 'data', 'descrição', 'observação', 'total', 'quantidade', 'percentual'];
                $primeiroValor = strtolower($row[0] ?? '');
                foreach ($palavrasCabecalho as $palavra) {
                    if (strpos($primeiroValor, $palavra) !== false) {
                        $headerRow = $rowIndex;
                        break;
                    }
                }
            }
            
            $xml .= '<Row>';
            
            // Se for array indexado
            if (isset($row[0])) {
                foreach ($row as $value) {
                    $cellValue = $value ?? '';
                    
                    // Verificar se é numérico
                    $isNumeric = false;
                    $numericValue = $cellValue;
                    
                    if (is_numeric($cellValue)) {
                        $isNumeric = true;
                        $numericValue = $cellValue;
                    } elseif (is_string($cellValue) && preg_match('/^[\d,\.]+%?$/', str_replace(['.', ',', '%'], '', $cellValue))) {
                        // Tentar converter valores formatados
                        $cleanValue = str_replace(['.', ',', '%'], ['', '.', ''], $cellValue);
                        if (is_numeric($cleanValue)) {
                            $numericValue = $cleanValue;
                            $isNumeric = true;
                        }
                    }
                    
                    $style = ($rowIndex === $headerRow) ? ' ss:StyleID="Header"' : '';
                    
                    if ($isNumeric && $cellValue !== '') {
                        $xml .= '<Cell' . $style . '><Data ss:Type="Number">' . $numericValue . '</Data></Cell>';
                    } else {
                        $cellValue = htmlspecialchars($cellValue, ENT_XML1, 'UTF-8');
                        $xml .= '<Cell' . $style . '><Data ss:Type="String">' . $cellValue . '</Data></Cell>';
                    }
                }
            }
            
            $xml .= '</Row>';
            $rowIndex++;
        }

        $xml .= '  </Table>
 </Worksheet>
</Workbook>';

        return $xml;
    }
}

