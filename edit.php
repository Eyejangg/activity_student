<?php
require_once 'db.php';

// 1. ถ้าไม่มี ID ส่งมา ให้เด้งกลับหน้าแรก
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// 2. ถ้ามีการกดปุ่ม Save (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $student_id = $_POST['student_id'];
        $student_code = $_POST['student_code'];
        $fullname = $_POST['fullname'];
        $activity_name = $_POST['activity_name'];
        $activity_date = $_POST['activity_date'];
        $hours = $_POST['hours'];

        // เริ่ม Transaction
        $conn->beginTransaction();

        // A. อัปเดตข้อมูลนักศึกษา
        $sql_student = "UPDATE students SET student_code = ?, fullname = ? WHERE id = ?";
        $stmt_s = $conn->prepare($sql_student);
        $stmt_s->execute([$student_code, $fullname, $student_id]);

        // B. อัปเดตข้อมูลกิจกรรม
        $sql_activity = "UPDATE activities SET activity_name = ?, activity_date = ?, hours = ? WHERE id = ?";
        $stmt_a = $conn->prepare($sql_activity);
        $stmt_a->execute([$activity_name, $activity_date, $hours, $id]);

        $conn->commit();
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// 3. ดึงข้อมูลเดิมมาแสดง
$stmt = $conn->prepare("
    SELECT a.*, s.student_code, s.fullname 
    FROM activities a 
    JOIN students s ON a.student_id = s.id 
    WHERE a.id = ?
");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูล - NPRU</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --npru-pink: #d63384;
            --npru-dark: #880e4f;
            --glass-bg: rgba(255, 255, 255, 0.9);
        }

        body {
            font-family: 'Sarabun', sans-serif;
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
        }

        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }

        .form-control:focus {
            border-color: var(--npru-pink);
            box-shadow: 0 0 0 0.2rem rgba(214, 51, 132, 0.15);
        }

        .section-title {
            color: var(--npru-dark);
            font-weight: 600;
            border-bottom: 2px solid #fce7f3;
            padding-bottom: 10px;
            margin-bottom: 20px;
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

        .btn-cancel {
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-cancel:hover {
            background: #f8f9fa;
            color: #343a40;
        }
    </style>
</head>

<body class="p-3">

    <div class="glass-card">
        <div class="header-section">
            <img src="https://lh4.googleusercontent.com/proxy/33J-Wga102OcxTTvYCTTINlJL-Fx2huXICHs2aRSGXVl0puylfFQ7xVnKdYTKxBzussHPisvDvH7y-7oxxU" alt="Logo" class="logo-img">
            <h3 class="mb-0 fw-bold">แก้ไขข้อมูลกิจกรรม</h3>
            <p class="mb-0 opacity-75 small">อัปเดตข้อมูลในระบบฐานข้อมูล</p>
        </div>

        <div class="p-4 px-md-5 py-4">
            <form method="post">
                <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">

                <div class="mb-4">
                    <h6 class="section-title"><i class="bi bi-person-lines-fill"></i> ข้อมูลนักศึกษา</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">รหัสนักศึกษา</label>
                            <input type="text" name="student_code" class="form-control bg-light" value="<?php echo $row['student_code']; ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small text-muted">ชื่อ-นามสกุล</label>
                            <input type="text" name="fullname" class="form-control bg-light" value="<?php echo $row['fullname']; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="section-title"><i class="bi bi-journal-check"></i> รายละเอียดกิจกรรม</h6>

                    <div class="mb-3">
                        <label class="form-label small text-muted">ชื่อกิจกรรม</label>
                        <input type="text" name="activity_name" class="form-control" value="<?php echo $row['activity_name']; ?>" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">วันที่ทำกิจกรรม</label>
                            <input type="date" name="activity_date" class="form-control" value="<?php echo $row['activity_date']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">จำนวนชั่วโมง</label>
                            <div class="input-group">
                                <input type="number" name="hours" class="form-control" value="<?php echo $row['hours']; ?>" required>
                                <span class="input-group-text bg-white text-muted">ชม.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-2">
                    <a href="index.php" class="btn btn-cancel flex-fill">
                        <i class="bi bi-x-lg"></i> ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary btn-submit text-white flex-fill shadow-sm">
                        <i class="bi bi-save2"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>