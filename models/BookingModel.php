<?php
class BookingModel
{
    private $pdo;

    public function __construct()
    {
        require_once __DIR__ . "/../commons/function.php";
        $this->pdo = connectDB();
    }

    // Lấy tất cả booking (kèm tên tour + ngày khởi hành)
    public function getAll()
    {
        $sql = "SELECT b.*,
                ts.depart_date,
                t.title AS tour_name
            FROM bookings b
            JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
            JOIN tours t ON t.id = ts.tour_id
            ORDER BY b.booking_code ASC"; // sắp xếp theo booking_code tăng dần

        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByKeyword($keyword)
    {
        $sql = "SELECT b.*,
                   ts.depart_date,
                   t.title AS tour_name
            FROM bookings b
            LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
            LEFT JOIN tours t ON t.id = ts.tour_id
            WHERE b.booking_code LIKE :kw
               OR b.contact_name LIKE :kw
               OR t.title LIKE :kw
            ORDER BY b.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':kw' => "%$keyword%"
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function find($id)
    {
        $sql = "
            SELECT b.*,
                   ts.depart_date AS start_date,
                   t.title AS tour_name,
                   ts.id AS schedule_id,
                   ts.seats_total,
                   ts.seats_available,
                   ts.price_adult,
                   ts.price_children
            FROM bookings b
            LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
            LEFT JOIN tours t ON t.id = ts.tour_id
            WHERE b.id = ?
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $ad = isset($r['adults']) ? (int) $r['adults'] : 0;
            $ch = isset($r['children']) ? (int) $r['children'] : 0;
            $r['total_people'] = $ad + $ch;
            $r['total_amount'] = $this->calculateTotal($r['tour_schedule_id'], $ad, $ch);
        }
        return $r;
    }

    public function insert($data)
    {
        // kiểm tra số chỗ
        if (!$this->checkCapacity($data['tour_schedule_id'], $data['adults'], $data['children'])) {
            return false;
        }

        $total_amount = $this->calculateTotal(
            $data['tour_schedule_id'],
            $data['adults'] ?? 0,
            $data['children'] ?? 0
        );

        $sql = "INSERT INTO bookings
            (booking_code, user_id, tour_schedule_id, contact_name, contact_phone, contact_email, adults, children, total_people, total_amount, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute([
            $data['booking_code'] ?? '',
            $data['user_id'] ?? null,
            $data['tour_schedule_id'] ?? null,
            $data['contact_name'] ?? null,
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['adults'] ?? 0,
            $data['children'] ?? 0,
            ($data['adults'] ?? 0) + ($data['children'] ?? 0),
            $total_amount,
            $data['status'] ?? 'PENDING'
        ]);

        if ($res) {
            $this->updateSeats($data['tour_schedule_id']);
        }

        return $res;
    }

    public function update($id, $data)
    {
        // kiểm tra số chỗ
        if (!$this->checkCapacity($data['tour_schedule_id'], $data['adults'], $data['children'], $id)) {
            return false;
        }

        $total_amount = $this->calculateTotal(
            $data['tour_schedule_id'],
            $data['adults'] ?? 0,
            $data['children'] ?? 0
        );

        $sql = "UPDATE bookings SET
                    booking_code = ?, 
                    tour_schedule_id = ?, 
                    contact_name = ?, 
                    contact_phone = ?, 
                    contact_email = ?,
                    adults = ?, 
                    children = ?, 
                    total_people = ?, 
                    total_amount = ?, 
                    status = ?
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute([
            $data['booking_code'] ?? '',
            $data['tour_schedule_id'] ?? null,
            $data['contact_name'] ?? null,
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['adults'] ?? 0,
            $data['children'] ?? 0,
            ($data['adults'] ?? 0) + ($data['children'] ?? 0),
            $total_amount,
            $data['status'] ?? 'PENDING',
            $id
        ]);

        if ($res) {
            $this->updateSeats($data['tour_schedule_id']);
        }

        return $res;
    }

    public function delete($id)
    {
        $booking = $this->find($id);
        $stmt = $this->pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $res = $stmt->execute([$id]);

        if ($res && $booking) {
            $this->updateSeats($booking['tour_schedule_id']);
        }

        return $res;
    }

    // Tính tổng tiền dựa trên tour_schedule
    public function calculateTotal($schedule_id, $adults, $children)
    {
        $stmt = $this->pdo->prepare("SELECT price_adult, price_children FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sc)
            return 0;

        $total = ($adults * $sc['price_adult']) + ($children * $sc['price_children']);
        return $total;
    }

    // Kiểm tra số chỗ còn
    public function checkCapacity($schedule_id, $adults, $children, $booking_id = null)
    {
        $stmt = $this->pdo->prepare("SELECT seats_total, seats_available FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sc)
            return false;

        $current_booked = $sc['seats_total'] - $sc['seats_available'];

        // nếu update, trừ số người hiện tại của booking
        if ($booking_id) {
            $b = $this->find($booking_id);
            $current_booked -= $b['total_people'] ?? 0;
        }

        $requested = $adults + $children;

        return ($current_booked + $requested) <= $sc['seats_total'];
    }

    // Cập nhật seats_available
    public function updateSeats($schedule_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(adults + children) as booked 
            FROM bookings 
            WHERE tour_schedule_id = ?
        ");
        $stmt->execute([$schedule_id]);
        $sum = $stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0;

        $stmt = $this->pdo->prepare("UPDATE tour_schedule SET seats_available = seats_total - ? WHERE id = ?");
        $stmt->execute([$sum, $schedule_id]);
    }

    // Hủy booking
    public function cancelBooking($id)
    {
        $booking = $this->find($id);
        if (!$booking)
            return false;

        $stmt = $this->pdo->prepare("UPDATE bookings SET status = 'CANCELED' WHERE id = ?");
        $res = $stmt->execute([$id]);
        if ($res) {
            $this->updateSeats($booking['tour_schedule_id']);
        }
        return $res;
    }

    // Lấy danh sách schedules để fill select (kèm tên tour)
    public function getSchedules($booking_id = null)
    {
        $sql = "
        SELECT ts.id, ts.depart_date, ts.seats_available, ts.price_adult, ts.price_children, t.title as tour_title
        FROM tour_schedule ts
        LEFT JOIN tours t ON t.id = ts.tour_id
        WHERE ts.status = 'OPEN'
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
