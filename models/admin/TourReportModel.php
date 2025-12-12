<?php
class TourReportModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Lấy báo cáo theo schedule
     */
    public function getBySchedule($schedule_id)
    {
        $sql = "SELECT tr.*, 
                       u.full_name as created_by_name,
                       ts.depart_date,
                       t.title as tour_name,
                       tg.full_name as guide_name
                FROM tour_reports tr
                LEFT JOIN users u ON tr.created_by = u.id
                JOIN tour_schedule ts ON tr.schedule_id = ts.id
                JOIN tours t ON ts.tour_id = t.id
                LEFT JOIN tour_guides tg ON ts.guide_id = tg.id
                WHERE tr.schedule_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$schedule_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lưu báo cáo
     */
    public function save($data)
    {
        $errors = [];

        // Validate
        if (empty($data['schedule_id'])) {
            $errors[] = "Tour schedule không hợp lệ";
        }
        if (empty($data['actual_guests']) || $data['actual_guests'] < 0) {
            $errors[] = "Số khách thực tế không hợp lệ";
        }

        if (!empty($errors)) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            // Check nếu đã có báo cáo thì update, chưa thì insert
            $existing = $this->getBySchedule($data['schedule_id']);

            if ($existing) {
                // Update
                $sql = "UPDATE tour_reports SET
                        actual_guests = ?,
                        incidents = ?,
                        customer_feedback = ?,
                        guide_notes = ?,
                        expenses_summary = ?,
                        overall_rating = ?,
                        updated_at = NOW()
                        WHERE schedule_id = ?";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $data['actual_guests'],
                    $data['incidents'],
                    $data['customer_feedback'],
                    $data['guide_notes'],
                    $data['expenses_summary'],
                    $data['overall_rating'],
                    $data['schedule_id']
                ]);

                return ['ok' => true, 'id' => $existing['id']];

            } else {
                // Insert
                $sql = "INSERT INTO tour_reports 
                        (schedule_id, actual_guests, incidents, customer_feedback, 
                         guide_notes, expenses_summary, overall_rating, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $data['schedule_id'],
                    $data['actual_guests'],
                    $data['incidents'],
                    $data['customer_feedback'],
                    $data['guide_notes'],
                    $data['expenses_summary'],
                    $data['overall_rating'],
                    $data['created_by']
                ]);

                return ['ok' => true, 'id' => $this->pdo->lastInsertId()];
            }

        } catch (\Exception $e) {
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Lấy chi tiết báo cáo
     */
    public function find($id)
    {
        $sql = "SELECT tr.*, 
                       u.full_name as created_by_name,
                       ts.depart_date,
                       t.title as tour_name,
                       tg.full_name as guide_name
                FROM tour_reports tr
                LEFT JOIN users u ON tr.created_by = u.id
                JOIN tour_schedule ts ON tr.schedule_id = ts.id
                JOIN tours t ON ts.tour_id = t.id
                LEFT JOIN tour_guides tg ON ts.guide_id = tg.id
                WHERE tr.id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả báo cáo (có filter)
     */
    public function getAll($from_date, $to_date, $guide_id = null)
    {
        $sql = "SELECT tr.*, 
                       ts.depart_date,
                       t.title as tour_name,
                       tg.full_name as guide_name
                FROM tour_reports tr
                JOIN tour_schedule ts ON tr.schedule_id = ts.id
                JOIN tours t ON ts.tour_id = t.id
                LEFT JOIN tour_guides tg ON ts.guide_id = tg.id
                WHERE ts.depart_date BETWEEN ? AND ?";

        $params = [$from_date, $to_date];

        if ($guide_id) {
            $sql .= " AND ts.guide_id = ?";
            $params[] = $guide_id;
        }

        $sql .= " ORDER BY ts.depart_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tổng số khách theo từng tour trong 1 năm
     */
    public function getTotalCustomersByTour($year)
    {
        $sql = "
        SELECT 
            t.id,
            t.title AS tour_name,
            SUM(
                CASE 
                    WHEN b.total_people IS NOT NULL THEN b.total_people
                    ELSE (b.adults + b.children)
                END
            ) AS total_customers
        FROM tours t
        JOIN tour_schedule ts ON ts.tour_id = t.id
        JOIN bookings b ON b.tour_schedule_id = ts.id
        WHERE YEAR(ts.depart_date) = ?
          AND b.status NOT IN ('CANCELED')
        GROUP BY t.id, t.title
        ORDER BY total_customers DESC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Doanh thu theo tour trong 1 năm
     */
    public function getRevenueByTour($year)
    {
        $sql = "
        SELECT 
            t.id,
            t.title AS tour_name,
            COALESCE(SUM(p.amount), 0) AS total_revenue
        FROM tours t
        JOIN tour_schedule ts ON ts.tour_id = t.id
        JOIN bookings b ON b.tour_schedule_id = ts.id
        JOIN payments p ON p.booking_id = b.id
        WHERE YEAR(ts.depart_date) = ?
          AND b.status NOT IN ('CANCELED')
        GROUP BY t.id, t.title
        ORDER BY total_revenue DESC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tổng số khách theo từng tháng trong 1 năm
     */
    public function getTotalCustomersByMonth($year)
    {
        $sql = "
        SELECT 
            MONTH(ts.depart_date) AS month,
            SUM(
                CASE 
                    WHEN b.total_people IS NOT NULL THEN b.total_people
                    ELSE (b.adults + b.children)
                END
            ) AS total_customers
        FROM tour_schedule ts
        JOIN bookings b ON b.tour_schedule_id = ts.id
        WHERE YEAR(ts.depart_date) = ?
          AND b.status NOT IN ('CANCELED')
        GROUP BY MONTH(ts.depart_date)
        ORDER BY month ASC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Doanh thu theo từng tháng trong 1 năm
     */
    public function getRevenueByMonth($year)
    {
        $sql = "
            SELECT 
                MONTH(ts.depart_date) AS month,
                COALESCE(SUM(p.amount), 0) AS total_revenue
            FROM tour_schedule ts
            JOIN bookings b ON b.tour_schedule_id = ts.id
            JOIN payments p ON p.booking_id = b.id
            WHERE YEAR(ts.depart_date) = ?
              AND b.status NOT IN ('CANCELED')
            GROUP BY MONTH(ts.depart_date)
            ORDER BY month ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}

?>