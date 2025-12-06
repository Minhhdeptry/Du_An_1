<?php
class BookingModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /** Lấy tất cả booking (trừ đã hủy) */
    public function getAll()
    {
        $sql = "SELECT b.*, ts.depart_date, t.title AS tour_name
                FROM bookings b
                JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                JOIN tours t ON t.id = ts.tour_id
                WHERE b.status != 'CANCELED'
                ORDER BY b.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Tìm kiếm booking */
    public function searchByKeyword($keyword)
    {
        $sql = "SELECT b.*, ts.depart_date, t.title AS tour_name
                FROM bookings b
                LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                LEFT JOIN tours t ON t.id = ts.tour_id
                WHERE (b.booking_code LIKE :kw OR b.contact_name LIKE :kw OR t.title LIKE :kw)
                  AND b.status != 'CANCELED'
                ORDER BY b.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':kw' => "%$keyword%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy chi tiết booking theo ID */
    public function find($id)
    {
        $sql = "SELECT b.*, ts.depart_date, t.title AS tour_name,
                       ts.seats_total, ts.seats_available, ts.price_adult, ts.price_children
                FROM bookings b
                LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                LEFT JOIN tours t ON t.id = ts.tour_id
                WHERE b.id = ? LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {
            $ad = (int) $r['adults'];
            $ch = (int) $r['children'];
            $r['total_people'] = $ad + $ch;
            $r['total_amount'] = $this->calculateTotal($r['tour_schedule_id'], $ad, $ch);
        }

        return $r;
    }

    /** Cập nhật booking */
    public function update($id, $data)
    {
        $old = $this->find($id);
        if (!$old) {
            return ['ok' => false, 'error' => 'Booking không tồn tại!'];
        }

        $adults = (int) ($data['adults'] ?? $old['adults']);
        $children = (int) ($data['children'] ?? $old['children']);
        $newSchedule = (int) ($data['tour_schedule_id'] ?? $old['tour_schedule_id']);

        if (!$this->checkCapacity($newSchedule, $adults, $children, $id)) {
            return ['ok' => false, 'error' => 'Không đủ chỗ để cập nhật!'];
        }

        $total_amount = $this->calculateTotal($newSchedule, $adults, $children);

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE bookings SET
                        tour_schedule_id = ?, contact_name = ?, contact_phone = ?, contact_email = ?,
                        adults = ?, children = ?, total_people = ?, total_amount = ?, status = ?
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $ok = $stmt->execute([
                $newSchedule,
                $data['contact_name'] ?? '',
                $data['contact_phone'] ?? '',
                $data['contact_email'] ?? '',
                $adults,
                $children,
                $adults + $children,
                $total_amount,
                $data['status'] ?? $old['status'],
                $id
            ]);

            if (!$ok) {
                throw new \Exception("Cập nhật thất bại!");
            }

            $this->pdo->commit();

            $this->updateSeats((int) $old['tour_schedule_id']);
            if ((int) $old['tour_schedule_id'] !== $newSchedule) {
                $this->updateSeats($newSchedule);
            }

            return ['ok' => true];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Tính tổng tiền */
    public function calculateTotal($schedule_id, $adults, $children)
    {
        $stmt = $this->pdo->prepare("SELECT price_adult, price_children FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sc) return 0;

        return ($adults * $sc['price_adult']) + ($children * $sc['price_children']);
    }

    /** Kiểm tra số chỗ */
    public function checkCapacity($schedule_id, $adults, $children, $booking_id = null)
    {
        $stmt = $this->pdo->prepare("SELECT seats_total FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sc) return false;

        $sql = "SELECT SUM(adults + children) AS booked
                FROM bookings
                WHERE tour_schedule_id = ? AND status IN ('PENDING','CONFIRMED','PAID','COMPLETED')";
        $params = [$schedule_id];

        if ($booking_id) {
            $sql .= " AND id != ?";
            $params[] = $booking_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);
        return ($booked + $adults + $children) <= (int) $sc['seats_total'];
    }

    /** Cập nhật số ghế */
    public function updateSeats($schedule_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(adults + children) AS booked
            FROM bookings
            WHERE tour_schedule_id = ? AND status IN ('PENDING','CONFIRMED','PAID','COMPLETED')
        ");
        $stmt->execute([$schedule_id]);
        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);

        $stmt = $this->pdo->prepare("
            UPDATE tour_schedule
            SET seats_available = seats_total - ?
            WHERE id = ?
        ");
        $stmt->execute([$booked, $schedule_id]);
    }

    /** Hủy booking */
    public function cancelBooking($id)
    {
        $b = $this->find($id);
        if (!$b) return false;

        $stmt = $this->pdo->prepare("UPDATE bookings SET status = 'CANCELED' WHERE id = ?");
        $res = $stmt->execute([$id]);

        if ($res) {
            $this->updateSeats((int) $b['tour_schedule_id']);
        }

        return $res;
    }

    /** Lấy danh sách schedules */
    public function getSchedules()
    {
        $sql = "SELECT ts.id, ts.depart_date, ts.seats_available, ts.price_adult, ts.price_children,
                       t.title AS tour_title
                FROM tour_schedule ts
                JOIN tours t ON t.id = ts.tour_id
                WHERE ts.status = 'OPEN'
                ORDER BY ts.depart_date ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
