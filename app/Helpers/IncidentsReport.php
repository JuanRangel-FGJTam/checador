<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;

class IncidentsReport
{

    private $date;
    private $employees;
    private $generalDirection;
    private $totales;
    private $title;
    
    /**
     * __construct
     *
     * @param  array $employees
     * @param  array{start:string,end:string} $date
     * @param  string $generalDirection
     * @param  array{omissions:int,delays:int,absents:int,acumulations:int,total:int,totales:int} $totales
     * @param  string $title
     */
    public function __construct($employees, $date, $generalDirection, $totales, $title)
    {
        $this->employees = $employees;
        $this->date = $date;
        $this->generalDirection = $generalDirection;
        $this->totales = $totales;
        $this->title = $title;
    }

    /**
     * make the excel report
     *
     * @return string|false return the file content as buffer
     */
    public function create()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // styles
        $styleArray = array(
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '4F81BD'),
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FFFFFF'),
            ),
        );
        $alignCenter = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ),
        );

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $sheet->setCellValue('A1', 'Fiscalía General De Justicia Del Estado De Tamaulipas'); 
        $sheet->setCellValue('A2', $this->generalDirection);
        $sheet->setCellValue('A3', $this->title);

        $sheet->getRowDimension('1')->setRowHeight(22); // Height row title
        $sheet->getRowDimension('2')->setRowHeight(17); // Height row title
        $sheet->getRowDimension('3')->setRowHeight(17); // Height row title
        $sheet->getStyle("A1:A3")->applyFromArray($alignCenter); // Align center

        $sheet->getStyle('A1')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('033270'); // Bg blue FGJ
        $sheet->getStyle('A1')->getFont()
            ->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
        $row = 5;

        if (count($this->employees) > 0)
        {
            $sheet->getStyle("A$row:J$row")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('b9c6d6'); // header table bg blue
            $sheet->setCellValue("A$row", '');
            $sheet->setCellValue("B$row", 'No. Empleado');
            $sheet->setCellValue("C$row", 'Nombre');
            $sheet->setCellValue("D$row", 'Nivel');
            $sheet->setCellValue("E$row", 'Puesto');
            $sheet->setCellValue("F$row", '');
            $sheet->setCellValue("G$row", 'Retardos');
            $sheet->setCellValue("H$row", 'Faltas');
            $sheet->setCellValue("I$row", 'Faltas por acumulación de retardos');
            $sheet->setCellValue("J$row", 'Total Faltas');

            $sheet->getRowDimension('5')->setRowHeight(33); // Height row
            $sheet->getStyle('A5:J5')->getAlignment()->setWrapText(true); // Wrap text 
            // set width numbers
            $sheet->getColumnDimension('F')->setWidth(1);
            $sheet->getColumnDimension('G')->setWidth(13);
            $sheet->getColumnDimension('H')->setWidth(13);
            $sheet->getColumnDimension('I')->setWidth(22);
            $sheet->getColumnDimension('J')->setWidth(10);
            // Align center
            $sheet->getStyle("A5:J5")->applyFromArray($alignCenter);

    
            $row = 6;
            $flag = 1;
            foreach ($this->employees as $employee) 
            {
                $sheet->setCellValue("A$row", $flag);
                $sheet->setCellValue("B$row", $employee['noEmployee']);
                $sheet->setCellValue("C$row", $employee['name']);
                $sheet->setCellValue("D$row", $employee['nivel']);
                $sheet->setCellValue("E$row", $employee['puesto']);
                $sheet->setCellValue("G$row", $employee['delays']);
                $sheet->setCellValue("H$row", $employee['absents']);
                $sheet->setCellValue("I$row", $employee['acumulations']);
                $sheet->setCellValue("J$row", $employee['totalAbsents']);
                // Center numbers
                $sheet->getStyle("G$row:J$row")->applyFromArray($alignCenter);
                // BG blue incidents
                $sheet->getStyle("J$row")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('b9c6d6');
                $row++;
                $flag ++;
            }
            $sheet->mergeCells("A$row:E$row");
            $sheet->setCellValue("A$row", 'TOTAL');
            $sheet->setCellValue("G$row", $this->totales['delays']);
            $sheet->setCellValue("H$row", $this->totales['absents']);
            $sheet->setCellValue("I$row", $this->totales['acumulations']);
            $sheet->setCellValue("J$row", $this->totales['total']);
            // Styles total cells
            $sheet->getStyle("A$row:J$row")->applyFromArray($styleArray);
            $sheet->getStyle("A$row:J$row")->applyFromArray($alignCenter);
        } else {
            $sheet->mergeCells("A$row:J$row");
            $sheet->getStyle("A$row")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A$row:J$row")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('c8d6b9');
            $sheet->setCellValue("A$row", "No hay empleados con incidencias que mostrar");
        }

        foreach(range('A','E') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);

        // * save the file in a temporaly buffer
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;

        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment; filename="'. urlencode('incidencias_' . $this->date['start'] . '.xlsx').'"');
        // return $writer->save('php://output');
    }

}
