<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeKardexExcel {

  private $data = array();
  private $generalDirection = '';
  
  /**
   * @param  array $data
   * @param  string $generalDirection
   * @return void
   */
  public function __construct($data, $generalDirection) {
    $this->data = $data;
    $this->generalDirection = $generalDirection;
  }
    
  /**
   * @return string|false return the file content as buffer
   */
  public function create() {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $this->makeHeader($sheet);
    
    // moths
    $row = 6;
    foreach ($this->data['months'] as $month) {
      $this->makeMonths($sheet, $row, $month );
    }


    // * build the file
    $writer = new Xlsx($spreadsheet);


    // * save the file in a temporaly buffer
    ob_start();
    $writer->save('php://output');
    $content = ob_get_contents();
    ob_end_clean();
    
    return $content;
  }

  private function makeHeader(Worksheet &$sheet){
    $sheet->mergeCells('A1:Q1');
    $sheet->mergeCells('A2:Q2');
    $sheet->mergeCells('A4:Q4');

    $sheet->setCellValue('A1', 'Fiscalía General de Justicia del Estado de Tamaulipas');
    $sheet->setCellValue('A2', $this->generalDirection);
    $sheet->setCellValue('A4', $this->data['name']);

    $sheet->getStyle("A1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
  }

  private function makeMonths(Worksheet &$sheet, int &$row, $month){
    $column = 'A';
    $sheet->mergeCells("A$row:AF$row");
    $sheet->setCellValue("A$row", $month['name']);

    $sheet->getStyle("A".$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    // Background blue name month
    $sheet->getStyle('A'.$row)
        ->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('39537a');
    
    // Color text white
    $sheet->getStyle("A$row")->getFont()
        ->getColor()
        ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

    $sheet->mergeCells("A".($row + 1).":A".($row + 2));
    $sheet->setCellValue("A".($row + 1), 'Día');
    $sheet->getStyle("A".($row + 1))
        ->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('dde3eb');

    $sheet->getRowDimension($row + 3)->setRowHeight(6);

    $sheet->setCellValue("A".($row + 4), 'Entrada');
    
    if ($this->data['checaComida']) {
        $sheet->setCellValue("A".($row + 5), 'Comida S');
        $sheet->setCellValue("A".($row + 6), 'Comida E');
        $sheet->setCellValue("A".($row + 7), 'Salida');
    } else {
        $sheet->setCellValue("A".($row + 5), 'Salida');
    }

    foreach ($month['checadas'] as $checada) {
        $column++;
        $sheet->setCellValue($column.($row + 1), $checada['dayName']);
        $sheet->setCellValue($column.($row + 2), $checada['day']);
        
        // Background blue days
        $sheet->getStyle($column.($row + 1))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('dde3eb');

        $sheet->getStyle($column.($row + 2))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('dde3eb');
        
        // Align center days
        $sheet->getStyle($column.($row + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($column.($row + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        if ($checada['bgCheckin']) {
            $sheet->getStyle($column.($row + 4))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB($checada['bgCheckin']);
        }
        $sheet->setCellValue($column.($row + 4), $checada['checkin']);

        if ($this->data['checaComida']) {
            $sheet->setCellValue($column.($row + 5), $checada['eat']);
            $sheet->setCellValue($column.($row + 6), $checada['arrive']);
            $rowCheckout = $row + 7;
            if ($checada['bgEat']) {
                $sheet->getStyle($column.($row + 5))
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($checada['bgEat']);
            }
            if ($checada['bgArrive']) {
                $sheet->getStyle($column.($row + 6))
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($checada['bgArrive']);
            }
            
        }
        else {
            $rowCheckout = $row + 5;
        }

        $sheet->setCellValue($column.$rowCheckout, $checada['checkout']);
        if ($checada['bgCheckout']) {
            $sheet->getStyle($column.$rowCheckout)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($checada['bgCheckout']);
        }
    }

    if ($this->data['checaComida']) {
        $row += 9;
    } else {
        $row += 7;
    }
  }

}