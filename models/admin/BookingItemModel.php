<?php
require_once "./models/admin/BookingModel.php";

class BookingItemModel
{
    private $pdo;
    private $bookingModel;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
        $this->bookingModel = new BookingModel(); // để cập nhật total_amount
    }

    /**
     * Thêm item mới
     */
    public function addItem($booking_id, $description, $qty, $unit_price, $type = 'PERSON')
    {
        try {
            $total_price = $qty * $unit_price;

            $sql = "INSERT INTO booking_item (booking_id, description, qty, unit_price, total_price, type)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $res = $stmt->execute([$booking_id, $description, $qty, $unit_price, $total_price, $type]);

            if ($res) {
                $this->updateBookingTotal($booking_id);
            }

            return $res;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách item theo booking (có thể theo loại)
     */
    public function getItemsByBooking($booking_id, $type = null)
    {
        try {
            $sql = "SELECT * FROM booking_item WHERE booking_id = ?";
            $params = [$booking_id];

            if ($type) {
                $sql .= " AND type = ?";
                $params[] = $type;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật item
     */
    public function updateItem($id, $description, $qty, $unit_price)
    {
        try {
            $total_price = $qty * $unit_price;
            $sql = "UPDATE booking_item 
                    SET description = ?, qty = ?, unit_price = ?, total_price = ?
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $res = $stmt->execute([$description, $qty, $unit_price, $total_price, $id]);

            if ($res) {
                // Lấy booking_id của item
                $booking_id = $this->getBookingIdByItem($id);
                if ($booking_id) {
                    $this->updateBookingTotal($booking_id);
                }
            }

            return $res;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Xóa item theo booking
     */
    public function deleteByBooking($booking_id)
    {
        try {
            $sql = "DELETE FROM booking_item WHERE booking_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $res = $stmt->execute([$booking_id]);

            if ($res) {
                $this->updateBookingTotal($booking_id);
            }

            return $res;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Lấy booking_id từ item_id
     */
    private function getBookingIdByItem($item_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT booking_id FROM booking_item WHERE id = ?");
            $stmt->execute([$item_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['booking_id'] ?? null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Tính tổng tiền của tất cả item trong booking
     */
    public function getTotalAmount($booking_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT SUM(total_price) as total FROM booking_item WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            return (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    /**
     * Cập nhật tổng tiền vào booking
     */
    private function updateBookingTotal($booking_id)
    {
        $items_total = $this->getTotalAmount($booking_id);

        // Lấy thông tin booking
        $booking = $this->bookingModel->find($booking_id);
        if (!$booking) return false;

        $person_total = ($booking['adults'] ?? 0) * ($booking['price_adult'] ?? 0)
                      + ($booking['children'] ?? 0) * ($booking['price_children'] ?? 0);

        $total_amount = $person_total + $items_total;

        // Cập nhật vào bookings
        $stmt = $this->pdo->prepare("UPDATE bookings SET total_amount = ? WHERE id = ?");
        return $stmt->execute([$total_amount, $booking_id]);
    }
}