<?php
require_once 'db.php';

// ดึงรายชื่อนักศึกษา
try {
    $stmt_students = $conn->query("SELECT * FROM students ORDER BY student_code ASC");
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $activity_name = $_POST['activity_name'];

    // ถ้าเลือก "อื่นๆ" ให้ใช้ค่าที่พิมพ์เอง
    if ($activity_name == 'other') {
        $activity_name = trim($_POST['other_activity_name']);
    }

    $activity_date = $_POST['activity_date'];
    $hours = $_POST['hours'];

    try {
        $sql = "INSERT INTO activities (student_id, activity_name, activity_date, hours) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$student_id, $activity_name, $activity_date, $hours]);

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกกิจกรรม - NPRU</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --npru-pink: #d63384;
            --npru-dark: #880e4f;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.6);
        }

        body {
            font-family: 'Sarabun', sans-serif;
            /* พื้นหลัง Gradient สีชมพู */
            background: linear-gradient(135deg, #fff0f5 0%, #ffe4e6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Prompt', sans-serif;
        }

        .glass-card {
            width: 100%;
            max-width: 600px;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .header-section {
            background: linear-gradient(135deg, var(--npru-pink), var(--npru-dark));
            padding: 30px;
            text-align: center;
            color: white;
            position: relative;
        }

        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 10px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--npru-pink);
            box-shadow: 0 0 0 0.2rem rgba(214, 51, 132, 0.15);
        }

        .btn-submit {
            background: linear-gradient(45deg, #10b981, #059669);
            border: none;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-add-student {
            font-size: 0.9rem;
            color: var(--npru-pink);
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-add-student:hover {
            color: var(--npru-dark);
            text-decoration: underline;
        }
    </style>

    <script>
        function checkActivity(val) {
            var element = document.getElementById('other_input');
            if (val === 'other') element.style.display = 'block';
            else element.style.display = 'none';
        }
    </script>
</head>

<body class="p-3">

    <div class="glass-card">
        <div class="header-section">
            <img src="https://lh4.googleusercontent.com/proxy/33J-Wga102OcxTTvYCTTINlJL-Fx2huXICHs2aRSGXVl0puylfFQ7xVnKdYTKxBzussHPisvDvH7y-7oxxU" alt="Logo" class="logo-img">
            <h3 class="mb-0 fw-bold">บันทึกกิจกรรมจิตอาสา</h3>
            <p class="mb-0 opacity-75 small">เพิ่มข้อมูลกิจกรรมใหม่เข้าระบบ</p>
        </div>

        <div class="p-4 px-md-5 py-4">
            <form method="post">

                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary">1. เลือกนักศึกษา</label>
                    <select name="student_id" class="form-select bg-light" required>
                        <option value="" selected disabled>-- ค้นหาและเลือกรายชื่อ --</option>
                        <?php foreach ($students as $std): ?>
                            <option value="<?php echo $std['id']; ?>">
                                <?php echo $std['student_code'] . " - " . $std['fullname']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="text-end mt-2">
                        <a href="add_student.php" class="btn-add-student">
                            <i class="bi bi-person-plus-fill"></i> ยังไม่มีรายชื่อ? ลงทะเบียนใหม่ คลิก
                        </a>
                    </div>
                </div>

                <hr class="text-muted opacity-25">

                <div class="mb-3">
                    <label class="form-label fw-bold text-secondary">2. รายละเอียดกิจกรรม</label>
                    <select name="activity_name" class="form-select mb-2" onchange="checkActivity(this.value)" required>
                        <option value="" selected disabled>-- เลือกกิจกรรม --</option>
                        <option value="จิตอาสาพัฒนาวัด">จิตอาสาพัฒนาวัด</option>
                        <option value="ปลูกป่าชายเลน">ปลูกป่าชายเลน</option>
                        <option value="ทาสีโรงเรียน">ทาสีโรงเรียน</option>
                        <option value="บริจาคโลหิต">บริจาคโลหิต</option>
                        <option value="ช่วยงานห้องสมุด">ช่วยงานห้องสมุด</option>
                        <option value="other" class="fw-bold">--- ระบุเอง (อื่นๆ) ---</option>
                    </select>

                    <div id="other_input" style="display:none;">
                        <input type="text" name="other_activity_name" class="form-control bg-light" placeholder="ระบุชื่อกิจกรรม...">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label small text-muted">วันที่ทำกิจกรรม</label>
                        <input type="date" name="activity_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">จำนวนชั่วโมง</label>
                        <div class="input-group">
                            <input type="number" name="hours" class="form-control" placeholder="0" min="1" required>
                            <span class="input-group-text bg-white text-muted">ชม.</span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-submit text-white shadow-sm">
                        <i class="bi bi-save2"></i> บันทึกข้อมูล
                    </button>
                    <a href="index.php" class="btn btn-light text-muted rounded-3 mt-2">
                        ย้อนกลับหน้าหลัก
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>