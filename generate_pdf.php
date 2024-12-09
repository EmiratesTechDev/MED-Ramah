<!DOCTYPE html>
<html lang="en">
<html dir="ltr">
<head>
<meta charset="UTF-8">
<title> Employee List </title>
<style>

* {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    direction: ltr;
    background: #fff; /*  لون خلفية أبيض */
    margin: 0;
    padding: 20px;
    color: #333;
}

.container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /*  ظل أخف */
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
}

.header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #c00;  /*  لون أحمر */
    padding-bottom: 20px;
}

.company-logo {
    max-width: 150px;  /*  حجم أصغر للشعار */
    height: auto;
    margin-bottom: 10px;
}

.company-name {
    font-size: 24px;  /*  حجم خط أصغر */
    color: #c00;  /*  لون أحمر */
    margin: 10px 0;
}

/*  جدول */
table {
    width: 100%;
    border-collapse: collapse; /*  دمج حدود الجدول */
    margin-top: 20px;
    border: 1px solid #ddd;  /*  حدود خارجية للجدول */
    border-radius: 8px;
    overflow: hidden;
}


th, td {
    border: 1px solid #ddd;  /*  حدود داخلية للخلايا */
    padding: 10px;
    text-align: right;
}

th {
    background-color: #c00;  /*  لون أحمر للخلفية */
    color: white;
    font-weight: 600;
}


tr:nth-child(even) {
    background-color: #f9f9f9; /* لون خلفية أفتح */
}

tr:hover {
    background-color: #f0f0f0;  /*  لون مختلف عند تمرير الماوس */
}


/*  زر الطباعة */
.print-btn {
    background-color: #c00;  /*  لون أحمر */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin-bottom: 20px;
}

.print-btn:hover {
    background-color: #a00; /*  لون أحمر داكن عند تمرير الماوس */
}


/* تذييل */
.footer {
    text-align: center;
    margin-top: 30px;
    color: #666;
}

.footer p {
    margin: 5px 0;
    font-size: 14px;
}


@media print {
            @page {
                size: A4 portrait; /* تحديد حجم الصفحة بشكل صريح */
                margin: 1cm;  /* ضبط الهوامش */
            }

            body {
                margin: 0; /* إزالة الهوامش الخارجية للجسم */
            }

            .container {
                width: 100%; /* جعل الحاوية تأخذ عرض الصفحة بالكامل */
                box-shadow: none;
                padding: 10px; /* تقليل الهوامش الداخلية */
                border-radius: 0; /* إزالة حدود الحاوية */
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed; /* توزيع عرض الأعمدة بالتساوي */
            }

            th, td {
                border: 1px solid #ddd;
                padding: 8px; /* تقليل حشو الخلايا */
                word-break: break-word; /* يسمح بكسر الكلمات الطويلة */
                font-size: 12px; /* تصغير حجم الخط عند الطباعة */

            }
    
    /* Ensure footer stays at bottom without creating extra pages */
    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 10px 20px;
      background: white;
      margin-top: 20px;
    }
    
    /* Add space for footer */
    body::after {
      content: '';
      display: block;
      height: 150px; /* Adjust based on footer height */
    }

    .print-btn {
      display: none;
    }
    
    body {
      background: white;
      padding: 0;
    }
    
    .container {
      box-shadow: none;
    }

  
    th {
      background-color: #c00 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

}



</style>
</head>
<body>

<div class="container">
  <div class="header">
    <img src="images/logo.png" alt="شعار الشركة" class="company-logo"> <br>  </div>


  <button class="print-btn" onclick="window.print()">تصدير الجدول</button>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Refrence</th>
        <th>Last Name</th>
        <th>First Name</th>
        <th>Postion</th>
        <th>Department</th>
        <th>Nationality</th>
        <th>Start Date</th>
        <th>Contact Namber</th>
      </tr>
    </thead>
    <tbody>
      <?php
      require 'db_conn.php';
      $sql = "SELECT * FROM `empform`";
      $result = mysqli_query($conn, $sql);
      while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['ref'] . "</td>";
        echo "<td>" . $row['lastName'] . "</td>";
        echo "<td>" . $row['firstName'] . "</td>";
        echo "<td>" . $row['position'] . "</td>";
        echo "<td>" . $row['department'] . "</td>";
        echo "<td>" . $row['nationality'] . "</td>";
        echo "<td>" . $row['date_start'] . "</td>";
        echo "<td>" . $row['contact_namber'] . "</td>";
        echo "</tr>";
      }
      ?>
    </tbody>
  </table>

  <div class="footer">
    <p>Information Technology Department - قسم تقنية المعلومات </p>
    <p>Email: it@rgcc.com | Website: www.rgcc.com</p>
    <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
</footer>
</div>

<script>

document.querySelector('.print-btn').addEventListener('click', function() {
  // Remove any existing print stylesheet
  const existingPrintStyle = document.querySelector('style[data-print-optimize]');
  if (existingPrintStyle) {
    existingPrintStyle.remove();
  }
  
  // Add temporary print optimization
  const style = document.createElement('style');
  style.setAttribute('data-print-optimize', 'true');
  style.textContent = `
    @page {
      margin: 1cm;
      size: A4;
    }
    @page :first {
      margin-top: 0.5cm;
    }
  `;
  document.head.appendChild(style);
  
  // Trigger print
  window.print();
  
  // Remove temporary style after printing
  setTimeout(() => {
    style.remove();
  }, 1000);
});

</script>

</body>
</html>