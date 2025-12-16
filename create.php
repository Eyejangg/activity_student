<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $student_code = trim($_POST['student_code']);
    $fullname = trim($_POST['fullname']);
    $activity_name = $_POST['activity_name'];
    $activity_date = $_POST['activity_date'];
    $hours = $_POST['hours'];

    try {
        // 1. ตรวจสอบก่อนว่ามีรหัสนักศึกษานี้หรือยัง?
        $checkStmt = $conn->prepare("SELECT id FROM students WHERE student_code = ?");
        $checkStmt->execute([$student_code]);
        $existingStudent = $checkStmt->fetch();

        if ($existingStudent) {
            // ถ้านักศึกษาคนนี้มีอยู่แล้ว -> ใช้ ID เดิม
            $student_id = $existingStudent['id'];
        } else {
            // ถ้ายังไม่มี -> เพิ่มนักศึกษาใหม่ลงตาราง students ก่อน
            $insertStudent = $conn->prepare("INSERT INTO students (student_code, fullname) VALUES (?, ?)");
            $insertStudent->execute([$student_code, $fullname]);
            $student_id = $conn->lastInsertId(); // รับ ID ที่เพิ่งสร้างใหม่
        }

        // 2. บันทึกกิจกรรมลงตาราง activities
        $sql = "INSERT INTO activities (student_id, activity_name, activity_date, hours) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$student_id, $activity_name, $activity_date, $hours]);

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มกิจกรรม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #eef2f7;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card-header {
            background: linear-gradient(45deg, #4e73df, #224abe);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            text-align: center;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            border-color: #4e73df;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">📝 บันทึกกิจกรรมใหม่</h4>
        </div>
        <div class="card-body p-4">
            <form method="post">
                <h6 class="text-primary border-bottom pb-2 mb-3">ข้อมูลนักศึกษา</h6>
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="form-label small text-muted">รหัสนักศึกษา</label>
                        <input type="text" name="student_code" class="form-control" placeholder="เช่น 66001" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small text-muted">ชื่อ-นามสกุล</label>
                        <input type="text" name="fullname" class="form-control" placeholder="ชื่อ นามสกุล" required>
                    </div>
                </div>

                <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">รายละเอียดกิจกรรม</h6>
                <div class="mb-3">
                    <label class="form-label small text-muted">ชื่อกิจกรรม</label>
                    <input type="text" name="activity_name" class="form-control" placeholder="เช่น จิตอาสาพัฒนาวัด" required>
                </div>

                <div class="row mb-4">
                    <div class="col-md-7">
                        <label class="form-label small text-muted">วันที่ทำกิจกรรม</label>
                        <input type="date" name="activity_date" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small text-muted">จำนวนชั่วโมง</label>
                        <input type="number" name="hours" class="form-control" placeholder="0" min="1" required>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">บันทึกข้อมูล</button>
                    <a href="index.php" class="btn btn-light text-muted">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>