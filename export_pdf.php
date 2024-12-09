<?php

require_once('TCPDF-main/tcpdf.php');
require_once('config.php');
// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Extend TCPDF to customize header and footer
class MYPDF extends TCPDF {
    // Add table headers property
    protected $tableHeaders = array(
        array('width' => 12, 'text' => 'ID', 'align' => 'C'),
        array('width' => 15, 'text' => 'Ref', 'align' => 'C'),
        array('width' => 25, 'text' => 'First Name', 'align' => 'C'),
        array('width' => 25, 'text' => 'Last Name', 'align' => 'C'),
        array('width' => 25, 'text' => 'Position', 'align' => 'C'),
        array('width' => 30, 'text' => 'Department', 'align' => 'C'),
        array('width' => 18, 'text' => 'Nationality', 'align' => 'C'),
        array('width' => 20, 'text' => 'Start Date', 'align' => 'C')
    );
    
    public function Header() {
        // Add a nice gradient background to header
        $this->Rect(0, 0, $this->getPageWidth(), 60, 'F', array(), array(41, 128, 185));
        
        // Add logo - simplified approach
        if (file_exists('images/logo.jpg')) {
            $this->Image('images/logo.jpg', 15, 10, 30);
        }
        
        // Company name with enhanced styling
        $this->SetY(15);
        // First line of company name
        $this->SetFont('helvetica', 'B', 28);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 15, 'RAMAH', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);
        
        // Second line of company name
        $this->SetFont('helvetica', 'B', 22);
        $this->Cell(0, 15, 'GENERAL CONTRACTING', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);
        
        // Third line of company name
        $this->SetFont('helvetica', '', 18);
        $this->Cell(0, 15, 'AND TRANSPORT LLC', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(25);
        
        // Title with enhanced styling
        $this->SetFillColor(245, 247, 250);
        $this->Rect(0, 60, $this->getPageWidth(), 25, 'F', array());
        
        $this->SetY(65);
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(0, 15, 'Employee List Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Add date
        $this->SetY(95);
        $this->SetFont('helvetica', '', 6);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Created on: ' . date('F d, Y - h:i A'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Ln(15);
        
    }

    public function Footer() {
        
        // Add subtle background to footer
        $this->SetFillColor(248, 249, 250);
        $this->Rect(0, $this->getPageHeight() - 25, $this->getPageWidth(), 25, 'F', array());
        
        // Position at 15 mm from bottom
        $this->SetY(-25);
        
        // Contact information
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'IT Department | Email: it@rgcc.ae | Phone: +971-50-141-2284', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        // Page number
        $this->SetY(-25);
        $this->SetX($this->getPageWidth() - 45);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(41, 128, 185);
        $pageNum = 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages();
        $this->Cell(30, 6, $pageNum, 0, false, 'C', 1, '', 0, false, 'T', 'M');
    }
    
    // Function to truncate text if it's too long
    function truncateText($text, $maxWidth) {
        if ($this->GetStringWidth($text) > $maxWidth) {
            while ($this->GetStringWidth($text . '...') > $maxWidth) {
                $text = substr($text, 0, -1);
            }
            return $text . '...';
        }
        return $text;
    }
}

// Create new PDF document
$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('RAMAH IT Department');
$pdf->SetAuthor('IT Department');
$pdf->SetTitle('Employee List Report');

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins - adjusted for portrait mode
$pdf->SetMargins(20, 110, 10);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 40);

// Add a page
$pdf->AddPage();

// Function to add table headers
function addTableHeaders($pdf) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(41, 128, 185);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetLineWidth(0.2);
    $pdf->Cell(12, 8, 'ID', 1, 0, 'C', 1);
    $pdf->Cell(16, 8, 'Ref', 1, 0, 'C', 1);
    $pdf->Cell(25, 8, 'First Name', 1, 0, 'C', 1);
    $pdf->Cell(20, 8, 'Last Name', 1, 0, 'C', 1);
    $pdf->Cell(30, 8, 'Position', 1, 0, 'C', 1);
    $pdf->Cell(35, 8, 'Department', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'Nationality', 1, 0, 'C', 1);
    $pdf->Cell(20, 8, 'Start Date', 1, 1, 'C', 1);
}

// Add initial table headers
addTableHeaders($pdf);

// Get data from database
$sql = "SELECT e.*, d.NameDepartment 
        FROM empform e 
        LEFT JOIN departmenttable d ON e.department = d.IdDepartment
        ORDER BY d.NameDepartment, e.lastName";
$result = $conn->query($sql);

// Group employees by department
$current_department = '';
$first = true;

while($row = $result->fetch_assoc()) {
    // Check if we need a new page and add headers
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        addTableHeaders($pdf);
    }

    if ($current_department != $row['NameDepartment']) {
        // Add department header with enhanced styling
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(52, 73, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, $row['NameDepartment'] ? $row['NameDepartment'] : 'No Department', 1, 1, 'L', 1);
        
        $current_department = $row['NameDepartment'];
    }
    
    // Add employee data with enhanced styling
    $pdf->SetFont('helvetica', '', 6.8);
    $pdf->SetTextColor(44, 62, 80);
    
    // Alternate row colors for better readability
    $fill = !isset($fill) || !$fill;
    $fillColor = $fill ? array(245, 247, 250) : array(255, 255, 255);
    $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
    
    $pdf->SetLineWidth(0.1);
    $pdf->Cell(12, 7, $row['id'], 1, 0, 'C', $fill);
    $pdf->Cell(16, 7, $pdf->truncateText($row['ref'], 12), 1, 0, 'C', $fill);
    $pdf->Cell(25, 7, $pdf->truncateText($row['firstName'], 25), 1, 0, 'C', $fill);
    $pdf->Cell(20, 7, $pdf->truncateText($row['lastName'], 20), 1, 0, 'C', $fill);
    $pdf->Cell(30, 7, $pdf->truncateText($row['position'], 29), 1, 0, 'C', $fill);
    $pdf->Cell(35, 7, $pdf->truncateText($row['department'], 35), 1, 0, 'C', $fill);
    $pdf->Cell(18, 7, $pdf->truncateText($row['nationality'], 18), 1, 0, 'C', $fill);
    $pdf->Cell(20, 7, date('d/m/Y', strtotime($row['date_start'])), 1, 1, 'C', $fill);
}

// Close and output PDF document
$pdf->Output('RAMAH_Employees_List.pdf', 'D');

// Close database connection
$conn->close();
?>
