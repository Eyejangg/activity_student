<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจิตอาสา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: #4e73df;
            color: white;
        }

        .btn-add {
            background-color: #28a745;
            color: white;
            border-radius: 50px;
            padding: 10px 25px;
        }

        .btn-add:hover {
            background-color: #218838;
            color: white;
        }
    </style>
</head>

<body class="container py-5">

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold text-primary"><i class="bi bi-journal-bookmark-fill"></i> บันทึกกิจกรรมจิตอาสา</h2>
            <p class="text-muted">ระบบจัดการข้อมูลกิจกรรมนักศึกษา</p>
        </div>
        <div class="col-auto">
            <a href="create.php" class="btn btn-add shadow-sm">
                <i class="bi bi-plus-lg"></i> เพิ่มกิจกรรมใหม่
            </a>
        </div>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>วันที่</th>
                        <th>รหัสนักศึกษา</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>กิจกรรม</th>
                        <th class="text-center">ชั่วโมง</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT a.*, s.student_code, s.fullname 
                        FROM activities a 
                        JOIN students s ON a.student_id = s.id 
                        ORDER BY a.activity_date DESC
                    ");

                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $date = date("d/m/Y", strtotime($row['activity_date']));
                            echo "<tr>";
                            echo "<td><span class='badge bg-light text-dark border'>{$date}</span></td>";
                            echo "<td class='fw-bold text-primary'>{$row['student_code']}</td>";
                            echo "<td>{$row['fullname']}</td>";
                            echo "<td>{$row['activity_name']}</td>";
                            echo "<td class='text-center'><span class='badge bg-info text-dark'>{$row['hours']} ชม.</span></td>";
                            echo "<td class='text-center'>
                                    <a href='delete.php?id={$row['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"ยืนยันการลบ?\")'>
                                        <i class='bi bi-trash'></i> ลบ
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>ยังไม่มีข้อมูลกิจกรรม</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>