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
                ORDER BY b.id DESC";

        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByKeyword($keyword)
    {
        $sql = "SELECT 
                b.*, 
                t.title AS tour_name,
                s.depart_date
            FROM bookings b
            JOIN tour_schedule s ON b.tour_schedule_id = s.id
            JOIN tours t ON s.tour_id = t.id
            WHERE b.booking_code LIKE :kw
               OR b.contact_name LIKE :kw
            ORDER BY b.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':kw' => "%$keyword%"
        ]);

        return $stmt->fetchAll();
    }




    public function find($id)
    {
        $sql = "
            SELECT b.*,
                   ts.depart_date AS start_date,
                   t.title AS tour_name,
                   ts.id AS schedule_id
            FROM bookings b
            LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
            LEFT JOIN tours t ON t.id = ts.tour_id
            WHERE b.id = ?
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r && !isset($r['total_people'])) {
            $ad = isset($r['adults']) ? (int) $r['adults'] : 0;
            $ch = isset($r['children']) ? (int) $r['children'] : 0;
            $r['total_people'] = $ad + $ch;
        }
        return $r;
    }

    public function insert($data)
    {
        $sql = "INSERT INTO bookings
            (booking_code, user_id, tour_schedule_id, contact_name, contact_phone, contact_email, total_amount, status, created_at)
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['booking_code'] ?? '',
            $data['tour_schedule_id'] ?? null,
            $data['contact_name'] ?? null,
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['total_amount'] ?? 0,
            $data['status'] ?? 'PENDING'
        ]);
    }

    public function update($id, $data)
    {
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

        return $stmt->execute([
            $data['booking_code'] ?? '',
            $data['tour_schedule_id'] ?? null,
            $data['contact_name'] ?? null,
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['adults'] ?? 0,
            $data['children'] ?? 0,
            $data['total_people'] ?? 0,
            $data['total_amount'] ?? 0,
            $data['status'] ?? 'PENDING',
            $id
        ]);
    }


    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM bookings WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Lấy danh sách schedules để fill select (kèm tên tour)
    public function getSchedules()
    {
        $sql = "
            SELECT ts.id, ts.depart_date, ts.seats_available, t.title as tour_title
            FROM tour_schedule ts
            LEFT JOIN tours t ON t.id = ts.tour_id
            ORDER BY ts.depart_date ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
