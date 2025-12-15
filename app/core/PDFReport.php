<?php

$tcpdf_paths = [
    __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php',
    __DIR__ . '/../../tcpdf/tcpdf.php',
    __DIR__ . '/../../libs/tcpdf/tcpdf.php'
];

$tcpdf_loaded = false;
foreach ($tcpdf_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $tcpdf_loaded = true;
        break;
    }
}

if (!$tcpdf_loaded) {
    die('TCPDF библиотеката не е намерена. Моля, следвайте инструкциите в INSTALL_TCPDF.md');
}

class PDFReport extends TCPDF {
    
    public function Header() {
        $this->SetFont('dejavusans', 'B', 16);
        $this->Cell(0, 10, 'Справки - Projify', 0, 1, 'C');
        $this->SetFont('dejavusans', '', 10);
        $this->Cell(0, 5, 'Генерирано на: ' . date('d.m.Y H:i'), 0, 1, 'C');
        $this->Ln(5);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'Страница ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
    
    public function addTable($headers, $data, $colWidths = null) {
        $this->SetFont('dejavusans', 'B', 10);
        
        if ($colWidths === null) {
            $colWidths = array_fill(0, count($headers), 190 / count($headers));
        }
        
        foreach ($headers as $i => $header) {
            $this->Cell($colWidths[$i], 7, $header, 1, 0, 'C', true);
        }
        $this->Ln();
        
        $this->SetFont('dejavusans', '', 9);
        $fill = false;
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $this->Cell($colWidths[$i], 6, $cell, 1, 0, 'L', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        $this->Ln(5);
    }
    
    public function addSection($title) {
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 10, $title, 0, 1, 'L');
        $this->SetFont('dejavusans', '', 10);
    }
}

