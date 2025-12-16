<?php
require_once 'db.php';

// Logic ค้นหา
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Logic เรียงลำดับ
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';

$allow_sort = ['date' => 'a.activity_date', 'code' => 's.student_code', 'hours' => 'a.hours'];
$orderBy = array_key_exists($sort, $allow_sort) ? $allow_sort[$sort] : 'a.activity_date';
$orderDir = ($order === 'asc') ? 'ASC' : 'DESC';

function sortLink($col, $currentSort, $currentOrder, $search)
{
    $newOrder = ($currentSort == $col && $currentOrder == 'desc') ? 'asc' : 'desc';
    $icon = '';
    if ($currentSort == $col) {
        $icon = ($currentOrder == 'desc') ? '<i class="bi bi-caret-down-fill small"></i>' : '<i class="bi bi-caret-up-fill small"></i>';
    }
    return "<a href='index.php?sort=$col&order=$newOrder&search=$search' class='text-dark text-decoration-none fw-bold'>$icon";
}

// Query
$sql = "SELECT a.*, s.student_code, s.fullname 
        FROM activities a 
        JOIN students s ON a.student_id = s.id 
        WHERE s.fullname LIKE :s OR s.student_code LIKE :s OR a.activity_name LIKE :s
        ORDER BY $orderBy $orderDir";
$stmt = $conn->prepare($sql);
$stmt->execute(['s' => "%$search%"]);

// Summary
$sumStmt = $conn->query("SELECT SUM(hours) as total_hours, COUNT(id) as total_activities FROM activities");
$summary = $sumStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจิตอาสา - มหาวิทยาลัยราชภัฏนครปฐม</title>
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
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            color: white;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .bg-gradient-pink {
            background: linear-gradient(135deg, #ec4899, #db2777);
        }

        .bg-gradient-orange {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        /* ปุ่มต่างๆ */
        .btn-custom-add {
            background: linear-gradient(45deg, #10b981, #059669);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .btn-custom-add:hover {
            transform: scale(1.05);
            color: white;
        }

        .btn-custom-check {
            border: 2px solid var(--npru-pink);
            color: var(--npru-pink);
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
        }

        .btn-custom-check:hover {
            background: var(--npru-pink);
            color: white;
        }

        /* ปุ่มใหม่: ลงทะเบียนนักศึกษา */
        .btn-custom-register {
            background: linear-gradient(45deg, #06b6d4, #0891b2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .btn-custom-register:hover {
            transform: scale(1.05);
            color: white;
        }

        .table-custom thead {
            background-color: #ffffff;
            border-bottom: 2px solid var(--npru-pink);
        }

        .table-custom th {
            color: #000;
            padding: 15px;
            cursor: pointer;
        }

        .badge-hours {
            background-color: #fce7f3;
            color: #be185d;
            border: 1px solid #fbcfe8;
        }

        .badge-date {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 500;
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
    <div class="container fade-in">

        <div class="glass-card p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4">
                <img src="https://lh4.googleusercontent.com/proxy/33J-Wga102OcxTTvYCTTINlJL-Fx2huXICHs2aRSGXVl0puylfFQ7xVnKdYTKxBzussHPisvDvH7y-7oxxU" alt="Logo" class="logo-img">
                <div>
                    <h5 class="text-secondary mb-1">ระบบบริหารจัดการกิจกรรมจิตอาสา</h5>
                    <h2 class="fw-bold mb-0" style="color: var(--npru-dark);">มหาวิทยาลัยราชภัฏนครปฐม</h2>
                </div>
            </div>
            <div class="d-none d-md-block text-end border-start ps-4">
                <p class="mb-0 text-muted small">วันที่ปัจจุบัน</p>
                <h4 class="fw-bold text-dark"><?php echo date('d/m/Y'); ?></h4>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-gradient-pink p-4 h-100">
                    <h6 class="text-white-50 text-uppercase small">ชั่วโมงรวมทั้งหมด</h6>
                    <h1 class="display-4 fw-bold mb-0"><?php echo number_format($summary['total_hours'] ?: 0); ?></h1>
                    <span class="fs-6 opacity-75">ชั่วโมง</span>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-gradient-orange p-4 h-100">
                    <h6 class="text-white-50 text-uppercase small">กิจกรรมทั้งหมด</h6>
                    <h1 class="display-4 fw-bold mb-0"><?php echo number_format($summary['total_activities']); ?></h1>
                    <span class="fs-6 opacity-75">รายการ</span>
                </div>
            </div>

            <div class="col-md-12 col-lg-6">
                <div class="glass-card p-4 h-100 d-flex flex-column justify-content-center">
                    <h5 class="fw-bold text-dark mb-3">เมนูจัดการระบบ</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="check_status.php" class="btn btn-custom-check shadow-sm flex-grow-1">
                            <i class="bi bi-search"></i> ตรวจสอบ
                        </a>
                        <a href="add_student.php" class="btn btn-custom-register shadow-sm flex-grow-1">
                            <i class="bi bi-person-plus-fill"></i> ลงทะเบียน นศ.
                        </a>
                        <a href="create.php" class="btn btn-custom-add shadow-sm flex-grow-1">
                            <i class="bi bi-journal-plus"></i> เพิ่มกิจกรรม
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-light rounded-circle p-2 text-danger"><i class="bi bi-table fs-5"></i></div>
                    <h4 class="fw-bold mb-0 text-dark">รายการกิจกรรม</h4>
                </div>
                <form method="get" class="d-flex position-relative" style="width: 300px;">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                    <input type="text" name="search" class="form-control rounded-pill ps-4 pe-5 border-0 bg-light" style="height: 45px;" placeholder="ค้นหาชื่อ, รหัส..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn position-absolute end-0 top-0 h-100 pe-3 text-muted border-0 bg-transparent"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <div class="table-responsive rounded-4 border-0">
                <table class="table table-hover table-custom align-middle mb-0">
                    <thead class="text-center">
                        <tr>
                            <th class="py-3" style="width: 15%;"><?php echo sortLink('date', $sort, $order, $search); ?> วันที่</a></th>
                            <th style="width: 15%;"><?php echo sortLink('code', $sort, $order, $search); ?> รหัสนักศึกษา</a></th>
                            <th class="text-start" style="width: 25%;">ชื่อ-นามสกุล</th>
                            <th class="text-start" style="width: 25%;">กิจกรรม</th>
                            <th style="width: 10%;"><?php echo sortLink('hours', $sort, $order, $search); ?> ชั่วโมง</a></th>
                            <th style="width: 10%;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php if ($stmt->rowCount() > 0): ?>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <?php
                                $dateObj = strtotime($row['activity_date']);
                                $dateThai = date("d/m/", $dateObj) . (date("Y", $dateObj) + 543);
                                ?>
                                <tr>
                                    <td class="text-center"><span class='badge badge-date rounded-pill px-3'><?php echo $dateThai; ?></span></td>
                                    <td class="text-center fw-bold" style="color: var(--npru-dark);"><?php echo $row['student_code']; ?></td>
                                    <td><?php echo $row['fullname']; ?></td>
                                    <td><?php echo $row['activity_name']; ?></td>
                                    <td class="text-center"><span class='badge badge-hours rounded-pill fs-6 px-3'><?php echo $row['hours']; ?></span></td>
                                    <td class="text-center">
                                        <a href='edit.php?id=<?php echo $row['id']; ?>' class='btn btn-sm btn-light text-warning shadow-sm me-1 rounded-circle' style='width:32px; height:32px;'><i class='bi bi-pencil-fill small'></i></a>
                                        <a href='delete.php?id=<?php echo $row['id']; ?>' class='btn btn-sm btn-light text-danger shadow-sm rounded-circle' style='width:32px; height:32px;' onclick='return confirm("ยืนยันการลบ?")'><i class='bi bi-trash-fill small'></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan='6' class='text-center py-5 text-muted'>ไม่พบข้อมูล</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($search): ?>
                <div class="text-center mt-3"><a href="index.php" class="btn btn-link text-decoration-none text-muted">ล้างการค้นหา</a></div>
            <?php endif; ?>
        </div>
        <footer class="text-center text-muted mt-5 mb-3 small">© <?php echo date("Y") + 543; ?> ระบบสารสนเทศเพื่อการจัดการ - มหาวิทยาลัยราชภัฏนครปฐม</footer>
    </div>
</body>

</html>