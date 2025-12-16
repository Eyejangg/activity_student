<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_code = trim($_POST['student_code']);
    $fullname = trim($_POST['fullname']);

    if (!empty($student_code) && !empty($fullname)) {
        try {
            // 1. เช็คก่อนว่ารหัสนี้มีหรือยัง?
            $checkStmt = $conn->prepare("SELECT id FROM students WHERE student_code = ?");
            $checkStmt->execute([$student_code]);

            if ($checkStmt->rowCount() > 0) {
                $error = "⚠️ รหัสนักศึกษานี้ ($student_code) มีในระบบแล้ว!";
            } else {
                // 2. ถ้ายังไม่มี ให้เพิ่มลง Database
                $sql = "INSERT INTO students (student_code, fullname) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$student_code, $fullname]);

                // บันทึกเสร็จแล้ว กลับไปหน้าเพิ่มกิจกรรมทันที (หรือจะโชว์ Success ก็ได้)
                header("Location: create.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนนักศึกษาใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #fff0f5 0%, #ffe4e6 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-custom {
            width: 100%;
            max-width: 450px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .header-bg {
            background: linear-gradient(45deg, #d63384, #be185d);
            /* สีชมพูธีมมหาลัย */
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 25px;
            text-align: center;
        }

        .btn-save {
            background: linear-gradient(45deg, #10b981, #059669);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
        }

        .btn-save:hover {
            background: #047857;
            color: white;
            transform: scale(1.02);
            transition: 0.2s;
        }
    </style>
</head>

<body>

    <div class="card card-custom">
        <div class="header-bg">
            <h4 class="mb-0 fw-bold">🎓 ลงทะเบียนนักศึกษาใหม่</h4>
            <small>เพิ่มรายชื่อเข้าสู่ระบบฐานข้อมูล</small>
        </div>
        <div class="card-body p-4">

            <?php if ($error): ?>
                <div class="alert alert-danger text-center rounded-3 mb-3 small">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">รหัสนักศึกษา</label>
                    <input type="text" name="student_code" class="form-control form-control-lg bg-light border-0" placeholder="เช่น 66401..." required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">ชื่อ - นามสกุล</label>
                    <input type="text" name="fullname" class="form-control form-control-lg bg-light border-0" placeholder="ระบุคำนำหน้าชื่อด้วย" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-save rounded-pill shadow-sm">
                        บันทึกข้อมูล
                    </button>
                    <a href="create.php" class="btn btn-light text-muted rounded-pill mt-2">
                        ย้อนกลับ
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>