<?php
ob_start();
require_once('TCPDF-main/tcpdf.php');
require_once('config.php');

if (!isset($_GET['employee_id'])) {
    die('Employee ID is required');
}

$employee_id = $_GET['employee_id'];

// استعلام لجلب بيانات الموظف
$sql = "SELECT * FROM empform WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    die('Employee not found');
}
// استعلام لجلب الأجهزة المرتبطة بالموظف
$sql_devices = "SELECT * FROM devtable WHERE employee_id = ?";
$stmt_devices = $conn->prepare($sql_devices);
$stmt_devices->bind_param("i", $employee_id);
$stmt_devices->execute();
$devices_result = $stmt_devices->get_result();

// إنشاء فئة مخصصة من TCPDF
class MYPDF extends TCPDF {
    public function Header() {
        // إضافة خلفية متدرجة للهيدر
        $this->Rect(0, 0, $this->getPageWidth(), 60, 'F', array(), array(41, 128, 185));
        
        // إضافة الشعار
        if (file_exists('images/logo.jpg')) {
            $this->Image('images/logo.jpg', 15, 10, 30);
        }
        
        // اسم الشركة مع التنسيق المحسن
        $this->SetY(15);
        // السطر الأول من اسم الشركة
        $this->SetFont('helvetica', 'B', 28);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 15, 'RAMAH', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);
        
        // السطر الثاني من اسم الشركة
        $this->SetFont('helvetica', 'B', 22);
        $this->Cell(0, 15, 'GENERAL CONTRACTING', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);
        
        // السطر الثالث من اسم الشركة
        $this->SetFont('helvetica', '', 18);
        $this->Cell(0, 15, 'AND TRANSPORT LLC', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(25);
        
        // العنوان مع التنسيق المحسن
        $this->SetFillColor(245, 247, 250);
        $this->Rect(0, 60, $this->getPageWidth(), 25, 'F', array());
        
        $this->SetY(65);
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(0, 15, 'Employee Details Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        // إضافة خلفية خفيفة للتذييل
        $this->SetFillColor(248, 249, 250);
        $this->Rect(0, $this->getPageHeight() - 25, $this->getPageWidth(), 25, 'F', array());
        
        // الموضع على بعد 15 مم من الأسفل
        $this->SetY(-25);
        
        // معلومات الاتصال
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'IT Department | Email: it@rgcc.ae | Phone: +971-50-141-2284', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        // رقم الصفحة
        $this->SetY(-25);
        $this->SetX($this->getPageWidth() - 45);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(41, 128, 185);
        $pageNum = 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages();
        $this->Cell(30, 6, $pageNum, 0, false, 'C', 1, '', 0, false, 'T', 'M');
    }
}

// إنشاء مستند PDF جديد
$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

// تعيين معلومات المستند
$pdf->SetCreator('RAMAH IT Department');
$pdf->SetAuthor('IT Department');
$pdf->SetTitle('Employee Details - ' . $employee['firstName'] . ' ' . $employee['lastName']);

// تعيين الخط الافتراضي أحادي المسافة
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// تعيين الهوامش
$pdf->SetMargins(10, 100, 10);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// تعيين الفواصل التلقائية للصفحات
$pdf->SetAutoPageBreak(TRUE, 40);

// إضافة صفحة جديدة
$pdf->AddPage();

// إنشاء جدول لبيانات الموظف
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('helvetica', 'B', 12);

// مصفوفة بيانات الموظف
$employee_data = [
    ['Reference', $employee['ref']],
    ['Name', $employee['firstName'] . ' ' . $employee['lastName']],
    ['Position', $employee['position']],
    ['Department', $employee['department']],
    ['Nationality', $employee['nationality']],
    ['Start Date', $employee['date_start']],
    ['Contact Number', $employee['contact_namber']]
];

 //طباعة بيانات الموظف في جدول
foreach($employee_data as $i => $row) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, $row[0], 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, $row[1], 1, 1, 'L');
}

$pdf->Ln(10);

// إضافة قسم الأجهزة
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Assigned Devices', 0, 1, 'L');
$pdf->Ln(5);

if ($devices_result->num_rows > 0) {
    // عناوين جدول الأجهزة
    $pdf->SetFillColor(41, 128, 185);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(255, 255, 255);
    
    // تحديد عرض الأعمدة
    $col_widths = array(45, 45, 35, 30, 35);
    
    $pdf->Cell($col_widths[0], 10, 'Device Name', 1, 0, 'C', true);
    $pdf->Cell($col_widths[1], 10, 'Model', 1, 0, 'C', true);
    $pdf->Cell($col_widths[2], 10, 'System', 1, 0, 'C', true);
    $pdf->Cell($col_widths[3], 10, 'IP', 1, 0, 'C', true);
    $pdf->Cell($col_widths[4], 10, 'MAC Address', 1, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $fill = false;
    while ($device = $devices_result->fetch_assoc()) {
        // التحقق من الارتفاع المطلوب للنص
       // $height = max(
        //    $pdf->getStringHeight($col_widths[0], $device['name_device']),
         //   $pdf->getStringHeight($col_widths[1], $device['model_device']),
          //  $pdf->getStringHeight($col_widths[2], $device['system_exp']),
          //  $pdf->getStringHeight($col_widths[3], $device['ip']),
           // $pdf->getStringHeight($col_widths[4], $device['mac_adress'])
        //);
        
        $height = max($height, 10); // الحد الأدنى للارتفاع
        
        $pdf->SetFillColor(245, 247, 250);
        $pdf->MultiCell($col_widths[0], $height, $device['name_device'], 1, 'L', $fill, 0);
        $pdf->MultiCell($col_widths[1], $height, $device['model_device'], 1, 'L', $fill, 0);
        $pdf->MultiCell($col_widths[2], $height, $device['system_exp'], 1, 'L', $fill, 0);
        $pdf->MultiCell($col_widths[3], $height, $device['ip'], 1, 'L', $fill, 0);
        $pdf->MultiCell($col_widths[4], $height, $device['mac_adress'], 1, 'L', $fill, 1);
        
        $fill = !$fill; // تبديل لون الخلفية
    }
} else {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'No devices assigned to this employee.', 0, 1);
}

// تنظيف أي إخراج متبقي
ob_end_clean();

// إخراج ملف PDF
$pdf->Output('employee_details_' . $employee_id . '.pdf', 'D');
