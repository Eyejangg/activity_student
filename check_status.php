<?php
require_once 'db.php';

$student_data = null;
$activities_log = [];
$error_message = "";

// ตรวจสอบการค้นหา
if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);

    if (!empty($keyword)) {
        // SQL ค้นหา
        $sql = "SELECT s.id, s.student_code, s.fullname, COALESCE(SUM(a.hours), 0) as total_hours
                FROM students s
                LEFT JOIN activities a ON s.id = a.student_id
                WHERE s.student_code = :keyword OR s.fullname LIKE :keyword_like
                GROUP BY s.id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'keyword' => $keyword,
            'keyword_like' => "%$keyword%"
        ]);

        if ($stmt->rowCount() > 0) {
            $student_data = $stmt->fetch(PDO::FETCH_ASSOC);

            // SQL ประวัติกิจกรรม
            $stmt_log = $conn->prepare("SELECT * FROM activities WHERE student_id = ? ORDER BY activity_date DESC");
            $stmt_log->execute([$student_data['id']]);
            $activities_log = $stmt_log->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_message = "❌ ไม่พบข้อมูลนักศึกษา: " . htmlspecialchars($keyword);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบสถานะ - NPRU</title>

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
            background-attachment: fixed;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Prompt', sans-serif;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
        }

        /* ปุ่มค้นหาธีม NPRU */
        .btn-search-npru {
            background: linear-gradient(45deg, var(--npru-pink), var(--npru-dark));
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            height: 50px;
            transition: 0.3s;
        }

        .btn-search-npru:hover {
            background: linear-gradient(45deg, #be185d, #831843);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(214, 51, 132, 0.3);
        }

        .form-control-custom {
            height: 50px;
            border-radius: 10px;
            font-size: 1.1rem;
            border: 1px solid #e0e0e0;
        }

        .form-control-custom:focus {
            border-color: var(--npru-pink);
            box-shadow: 0 0 0 0.2rem rgba(214, 51, 132, 0.15);
        }

        .result-card-header {
            background: linear-gradient(135deg, var(--npru-pink), var(--npru-dark));
            color: white;
            border-radius: 20px;
        }

        .table-custom thead {
            background-color: #ffffff;
            border-bottom: 2px solid var(--npru-pink);
        }

        .table-custom th {
            color: #000;
            font-weight: 600;
        }

        .badge-hours {
            background-color: #fce7f3;
            color: #be185d;
            border: 1px solid #fbcfe8;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="py-4">

    <div class="container fade-in" style="max-width: 800px;">

        <div class="mb-3">
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 border-0 bg-white shadow-sm text-decoration-none">
                <i class="bi bi-arrow-left"></i> กลับหน้าผู้ดูแลระบบ
            </a>
        </div>

        <div class="glass-card p-4 mb-4 text-center">
            <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                <img src="https://lh4.googleusercontent.com/proxy/33J-Wga102OcxTTvYCTTINlJL-Fx2huXICHs2aRSGXVl0puylfFQ7xVnKdYTKxBzussHPisvDvH7y-7oxxU" alt="Logo" class="logo-img">
                <div>
                    <h5 class="text-secondary mb-1">ระบบบริหารจัดการกิจกรรมจิตอาสา</h5>
                    <h2 class="fw-bold mb-0" style="color: var(--npru-dark);">มหาวิทยาลัยราชภัฏนครปฐม</h2>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 mb-4">
            <div class="text-center mb-4">
                <h4 class="fw-bold text-dark">ตรวจสอบชั่วโมงกิจกรรม</h4>
                <p class="text-muted small">กรอกรหัสนักศึกษา หรือ ชื่อ-นามสกุล เพื่อค้นหา</p>
            </div>

            <form method="get" action="check_status.php">
                <div class="row g-2">
                    <div class="col-md-9">
                        <input type="text" name="keyword" class="form-control form-control-custom bg-light"
                            placeholder="ระบุรหัส หรือ ชื่อนักศึกษา..."
                            required
                            value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-search-npru w-100 shadow-sm">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                    </div>
                </div>
            </form>

            <?php if ($error_message): ?>
                <div class="alert alert-danger mt-4 mb-0 text-center rounded-3 border-0 shadow-sm">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($student_data): ?>
            <div class="card result-card-header shadow-lg mb-4 border-0">
                <div class="card-body text-center p-5">
                    <h5 class="opacity-75 text-uppercase small ls-1">ผลการค้นหาข้อมูลของ</h5>
                    <h2 class="fw-bold mt-2 display-6"><?php echo $student_data['fullname']; ?></h2>
                    <p class="fs-5 mb-4 opacity-75">รหัสนักศึกษา: <?php echo $student_data['student_code']; ?></p>

                    <div class="bg-white text-dark rounded-4 p-4 d-inline-block shadow" style="min-width: 250px;">
                        <span class="text-muted d-block small text-uppercase fw-bold">ชั่วโมงจิตอาสารวม</span>
                        <span class="display-3 fw-bold" style="color: var(--npru-pink);"><?php echo $student_data['total_hours']; ?></span>
                        <span class="text-muted small">ชั่วโมง</span>
                    </div>
                </div>
            </div>

            <?php if (count($activities_log) > 0): ?>
                <div class="glass-card p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="bg-light rounded-circle p-2 text-danger"><i class="bi bi-clock-history fs-5"></i></div>
                        <h5 class="fw-bold mb-0 text-dark">ประวัติการเข้าร่วมกิจกรรม</h5>
                    </div>

                    <div class="table-responsive rounded-4">
                        <table class="table table-hover table-custom align-middle mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th class="py-3">วันที่</th>
                                    <th class="text-start">กิจกรรม</th>
                                    <th>ชั่วโมง</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php foreach ($activities_log as $log): ?>
                                    <?php
                                    $dateObj = strtotime($log['activity_date']);
                                    $dateThai = date("d/m/", $dateObj) . (date("Y", $dateObj) + 543);
                                    ?>
                                    <tr>
                                        <td class="text-center text-muted"><?php echo $dateThai; ?></td>
                                        <td><?php echo $log['activity_name']; ?></td>
                                        <td class="text-center"><span class="badge badge-hours rounded-pill px-3"><?php echo $log['hours']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4 mb-5">
                <a href="check_status.php" class="btn btn-link text-muted text-decoration-none">ล้างการค้นหา</a>
            </div>

        <?php endif; ?>

    </div>
</body>

</html>