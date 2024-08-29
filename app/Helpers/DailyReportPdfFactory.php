<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Codedge\Fpdf\Fpdf\Fpdf;
use Carbon\Carbon;
use App\Models\Employee;
use DateTime;

class DailyReportPdfFactory extends Fpdf {

    /**
     * @var array<Employee> $employees
     */
    protected array $employees = [];
    protected DateTime $dateReport;
    protected string $generalDirectionName;
    
    /**
     *
     * @param  array<Employee>|Collection<Employee> $employees
     * @param  string|DateTime|null $dateReport
     * @param  string $generalDirectionName
     * @return void
     */
    function __construct($employees, $dateReport, $generalDirectionName) {
        // Call the parent constructor
        parent::__construct( orientation:"P", unit:"mm", size:"letter");

        if( $employees instanceof Collection){
            $this->employees = $employees->all();
        }else{
            $this->employees = $employees;
        }

        if( $dateReport != null){
            if( $dateReport instanceof DateTime){
                $this->dateReport = $dateReport;
            }else{
                $this->dateReport = new DateTime($dateReport);
            }
        }else{
            $this->dateReport = new DateTime();
        }

        $this->generalDirectionName = $generalDirectionName;

    }
     
    public function makePdf(){
        $this->AliasNbPages();
        $this->AddPage();
        $this->body();
    }

    public function header() {
        // Logo
        $this->Image('images/logo_fgjtam.png', 8, 8, 45);
        
        // Arial bold 15
        $this->SetFont('Arial','B',12);
        $this->Cell(50); // Move right
        
        // $this->Cell(0, 8, utf8_decode($this->general_direction), 0, 1, 'R');
        $this->MultiCell(0, 5, mb_convert_encoding($this->generalDirectionName, 'ISO-8859-1', 'UTF-8') , 0, 'R', 0);
        $this->Cell(45); // Move right
        $this->SetFont('Arial','',10);
        $this->Cell(0, 5, mb_convert_encoding('Asistencia del día ', 'ISO-8859-1', 'UTF-8') . $this->dateReport->format('Y-m-d'), 0, 1, 'R');
        
        // Salto de línea
        $this->Ln(8);
    }
    
    public function body() {
    
        $this->SetFillColor(174, 179, 190);
        $this->Cell(0, 1, '', 0, 1, 'C', 1); // Line
        $this->SetFillColor(255);
        $this->Ln(2);
        
        // $this->SetFont('Arial','B',12);
        // $this->Cell(0, 5, utf8_decode('Subdirección de Sistemas'), 0, 1);
        $this->SetFont('Arial','',11);
        $this->Ln(3);
    
        if (count($this->employees) == 0) {
          $this->SetFillColor(217, 225, 235); // bg blue
          $this->SetTextColor(22, 47, 82); // blue color
          $this->Cell(0, 10, 'No hay empleados que mostrar', 0, 1, 'C', 1);
          $this->SetTextColor(0); // Black color
        } else {
          $this->SetFillColor(217, 225, 233); // bg header table
          $this->SetTextColor(22, 47, 82); // color white header table
          
          $this->Cell(10, 6, '#', 'B', 0, 'L', 1);
          $this->Cell(105, 6, 'Nombre', 'B', 0, 'L', 1);
          $this->Cell(20, 6, 'Entrada', 'B', 0, 'C', 1);
          $this->Cell(20, 6, 'Comida S', 'B', 0, 'C', 1);
          $this->Cell(20, 6, 'Comida E', 'B', 0, 'C', 1);
          $this->Cell(20, 6, 'Salida', 'B', 1, 'C', 1);
          $this->Cell(0, 1,'', 0, 1, 'C'); // White space
    
          $this->SetFillColor(255);
          $this->SetTextColor(0);
          $loop = 1;
          $bg = false;
          foreach ($this->employees as $employee) {
            if ($bg) {
              $this->SetFillColor(236, 241, 245);
            } else {
              $this->SetFillColor(255);
            }
            $date = new \DateTime();
            $this->Cell(10, 5, $loop, 0, 0, 'L', 1);
            $this->Cell(105, 5, mb_convert_encoding($employee['name'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L', 1);
            // Implement logic
            $this->Cell(20, 5, $employee['checkin'], 0, 0, 'C', 1);
            $this->Cell(20, 5, $employee['toeat'], 0, 0,  'C', 1);
            $this->Cell(20, 5, $employee['toarrive'], 0, 0, 'C', 1);
            $this->Cell(20, 5, $employee['checkout'], 0, 1,  'C', 1);
            $bg = !$bg;
            $loop ++;
          }
        }
    }
    
    public function footer() {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I',8);
        // Número de página
        $this->Cell(0,10, mb_convert_encoding( 'Página', 'ISO-8859-1', 'UTF-8').$this->PageNo().'/{nb}',0,0,'C');
    }

}