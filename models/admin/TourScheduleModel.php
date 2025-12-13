<?php
class TourScheduleModel
// Lấy các tour được phân công cho HDV chính hoặc phụ

{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/env.php";
        require_once "./commons/function.php";

        global $pdo;
        if (function_exists("connectDB")) {
            $pdo = connectDB();
        }

        $this->pdo = $pdo;

        $this->autoUpdateScheduleStatus();
    }

    // Lấy tất cả lịch, kèm tour + danh mục
    public function getAll()
    {
        $sql = "SELECT ts.*, 
                       t.title AS tour_title, t.code AS tour_code, 
                       c.name AS category_name,
                       u1.full_name AS guide_name,
                       u2.full_name AS assistant_name
                FROM tour_schedule ts
                JOIN tours t ON ts.tour_id = t.id
                LEFT JOIN tour_category c ON t.category_id = c.id
                LEFT JOIN staffs s1 ON ts.guide_id = s1.id
                LEFT JOIN users u1 ON s1.user_id = u1.id
                LEFT JOIN staffs s2 ON ts.assistant_guide_id = s2.id
                LEFT JOIN users u2 ON s2.user_id = u2.id
                ORDER BY ts.id DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function autoUpdateScheduleStatus()
    {
        try {
            $today = date('Y-m-d');

            // ✅ CASE 1: Tour quá lịch + KHÔNG có booking → CLOSED
            $sql1 = "UPDATE tour_schedule ts
                     SET ts.status = 'CLOSED'
                     WHERE ts.return_date < ?
                       AND ts.status IN ('OPEN', 'CLOSED')
                       AND NOT EXISTS (
                           SELECT 1 FROM bookings b 
                           WHERE b.tour_schedule_id = ts.id 
                           AND b.status NOT IN ('CANCELED')
                       )";
            $stmt1 = $this->pdo->prepare($sql1);
            $stmt1->execute([$today]);

            // ✅ CASE 2: Tour quá lịch + CÓ booking → FINISHED
            $sql2 = "UPDATE tour_schedule ts
                     SET ts.status = 'FINISHED'
                     WHERE ts.return_date < ?
                       AND ts.status IN ('OPEN', 'CLOSED')
                       AND EXISTS (
                           SELECT 1 FROM bookings b 
                           WHERE b.tour_schedule_id = ts.id 
                           AND b.status NOT IN ('CANCELED')
                       )";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([$today]);

            // ✅ CASE 3: Tour chưa đến lịch → OPEN (nếu đang là CLOSED)
            // Chỉ áp dụng cho tour đã đóng do hết hạn, không ảnh hưởng CANCELED
            $sql3 = "UPDATE tour_schedule
                     SET status = 'OPEN'
                     WHERE depart_date >= ?
                       AND status = 'CLOSED'";
            $stmt3 = $this->pdo->prepare($sql3);
            $stmt3->execute([$today]);
        } catch (PDOException $e) {
            error_log("AutoUpdateScheduleStatus Error: " . $e->getMessage());
        }
    }


    public function searchByKeyword($keyword)
    {
        $sql = "SELECT ts.*, 
                   t.title AS tour_title, t.code AS tour_code, 
                   c.name AS category_name,
                   u1.full_name AS guide_name,
                   u2.full_name AS assistant_name
            FROM tour_schedule ts
            JOIN tours t ON ts.tour_id = t.id
            LEFT JOIN tour_category c ON t.category_id = c.id
            LEFT JOIN staffs s1 ON ts.guide_id = s1.id
            LEFT JOIN users u1 ON s1.user_id = u1.id
            LEFT JOIN staffs s2 ON ts.assistant_guide_id = s2.id
            LEFT JOIN users u2 ON s2.user_id = u2.id
            WHERE t.title LIKE ?
               OR t.code LIKE ?
               OR ts.depart_date LIKE ?
               OR ts.return_date LIKE ?
               OR c.name LIKE ?
            ORDER BY ts.id DESC";

        $stmt = $this->pdo->prepare($sql);

        // ✅ FIX: Cần 5 tham số vì có 5 dấu ? trong WHERE
        $searchTerm = "%$keyword%";
        $stmt->execute([
            $searchTerm,  // t.title LIKE ?
            $searchTerm,  // t.code LIKE ?
            $searchTerm,  // ts.depart_date LIKE ?
            $searchTerm,  // ts.return_date LIKE ?
            $searchTerm   // c.name LIKE ?
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tour_schedule WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo lịch mới
    public function store($data)
    {
        // Xử lý loại tour
        $tourType = $data['tour_type'] ?? 'REGULAR';
        $seatsTotal = ($tourType === 'ON_DEMAND') ? 0 : ($data['seats_total'] ?? 0);
        $seatsAvailable = $seatsTotal;

        $sql = "INSERT INTO tour_schedule 
                (tour_id, tour_type, depart_date, return_date, seats_total, seats_available, price_adult, price_children, status, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['tour_id'],
            $tourType,
            $data['depart_date'],
            $data['return_date'],
            $seatsTotal,
            $seatsAvailable,
            $data['price_adult'],
            $data['price_children'],
            $data['status'],
            $data['note'] ?? null
        ]);
    }

    // Cập nhật lịch
    public function update($id, $data)
    {
        // Xử lý loại tour
        $tourType = $data['tour_type'] ?? 'REGULAR';
        $seatsTotal = ($tourType === 'ON_DEMAND') ? 0 : ($data['seats_total'] ?? 0);

        $sql = "UPDATE tour_schedule SET
                tour_id=?, tour_type=?, depart_date=?, return_date=?, seats_total=?, 
                price_adult=?, price_children=?, status=?, note=?
                WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['tour_id'],
            $tourType,
            $data['depart_date'],
            $data['return_date'],
            $seatsTotal,
            $data['price_adult'],
            $data['price_children'],
            $data['status'],
            $data['note'] ?? null,
            $id
        ]);

        // Cập nhật seats_available dựa trên booking hiện tại
        if ($tourType === 'REGULAR') {
            $this->updateSeats($id);
        } else {
            // Tour ON_DEMAND: set seats_available = 0
            $stmt = $this->pdo->prepare("UPDATE tour_schedule SET seats_available = 0 WHERE id = ?");
            $stmt->execute([$id]);
        }
    }

    // Xóa lịch
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tour_schedule WHERE id=?");
        $stmt->execute([$id]);
    }

    // Cập nhật seats_available dựa trên booking hiện tại (chỉ cho REGULAR)
    public function updateSeats($schedule_id)
    {
        // Kiểm tra loại tour
        $stmt = $this->pdo->prepare("SELECT tour_type FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $tourType = $result['tour_type'] ?? 'REGULAR';

        // Chỉ update nếu là REGULAR
        if ($tourType !== 'REGULAR') {
            return;
        }

        // Tổng số người đã đặt (status còn hiệu lực)
        $stmt = $this->pdo->prepare("
            SELECT SUM(adults + children) AS booked
            FROM bookings
            WHERE tour_schedule_id = ? AND status IN ('PENDING','CONFIRMED','PAID','COMPLETED')
        ");
        $stmt->execute([$schedule_id]);
        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);

        // Lấy tổng số ghế
        $stmt2 = $this->pdo->prepare("SELECT seats_total FROM tour_schedule WHERE id = ?");
        $stmt2->execute([$schedule_id]);
        $seats_total = (int) $stmt2->fetch(PDO::FETCH_ASSOC)['seats_total'];

        // Cập nhật seats_available = seats_total - booked
        $stmt3 = $this->pdo->prepare("UPDATE tour_schedule SET seats_available = ? WHERE id = ?");
        $stmt3->execute([$seats_total - $booked, $schedule_id]);
    }

    public function hasBooking($schedule_id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE tour_schedule_id=?");
        $stmt->execute([$schedule_id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        return $count > 0;
    }

    public function getBookingCount($schedule_id)
    {
        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) as booking_count
        FROM bookings
        WHERE tour_schedule_id = ?
          AND status NOT IN ('CANCELED')
    ");
        $stmt->execute([$schedule_id]);
        return (int) $stmt->fetchColumn();
    }

    public function getToursByStaff($staff_id)
    {
        $sql = "SELECT ts.*, t.title AS tour_title, t.code AS tour_code, c.name AS category_name,
                   u1.full_name AS guide_name, u2.full_name AS assistant_name
            FROM tour_schedule ts
            JOIN tours t ON ts.tour_id = t.id
            LEFT JOIN tour_category c ON t.category_id = c.id
            LEFT JOIN staffs s1 ON ts.guide_id = s1.id
            LEFT JOIN users u1 ON s1.user_id = u1.id
            LEFT JOIN staffs s2 ON ts.assistant_guide_id = s2.id
            LEFT JOIN users u2 ON s2.user_id = u2.id
            WHERE ts.guide_id = ? OR ts.assistant_guide_id = ?
            ORDER BY ts.depart_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id, $staff_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
