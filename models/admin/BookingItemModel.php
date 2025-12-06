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
        $this->bookingModel = new BookingModel();
    }

    /** ------------------------
     *  Thêm item mới
     */
    public function addItem($booking_id, $description, $qty, $unit_price, $type = 'SERVICE')
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
            error_log("AddItem Error: " . $e->getMessage());
            return false;
        }
    }

    /** ------------------------
     *  Lấy danh sách item theo booking
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

            $sql .= " ORDER BY id ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("GetItemsByBooking Error: " . $e->getMessage());
            return [];
        }
    }

    /** ------------------------
     *  Cập nhật item
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
                $booking_id = $this->getBookingIdByItem($id);
                if ($booking_id) {
                    $this->updateBookingTotal($booking_id);
                }
            }

            return $res;
        } catch (PDOException $e) {
            error_log("UpdateItem Error: " . $e->getMessage());
            return false;
        }
    }

    /** ------------------------
     *  ✅ Xóa 1 item (FIX: deleteItem thay vì deleteByBooking)
     */
    public function deleteItem($item_id)
    {
        try {
            // Lấy booking_id trước khi xóa
            $booking_id = $this->getBookingIdByItem($item_id);
            if (!$booking_id) {
                return false;
            }

            $sql = "DELETE FROM booking_item WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $res = $stmt->execute([$item_id]);

            if ($res) {
                $this->updateBookingTotal($booking_id);
            }

            return $res;
        } catch (PDOException $e) {
            error_log("DeleteItem Error: " . $e->getMessage());
            return false;
        }
    }

    /** ------------------------
     *  Xóa tất cả items của 1 booking
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
            error_log("DeleteByBooking Error: " . $e->getMessage());
            return false;
        }
    }

    /** ------------------------
     *  Lấy booking_id từ item_id
     */
    public function getBookingIdByItem($item_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT booking_id FROM booking_item WHERE id = ?");
            $stmt->execute([$item_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['booking_id'] ?? null;
        } catch (PDOException $e) {
            error_log("GetBookingIdByItem Error: " . $e->getMessage());
            return null;
        }
    }

    /** ------------------------
     *  Tính tổng tiền items
     */
    public function getTotalAmount($booking_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT SUM(total_price) as total FROM booking_item WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            return (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("GetTotalAmount Error: " . $e->getMessage());
            return 0;
        }
    }

    /** ------------------------
     *  ✅ Cập nhật tổng tiền vào booking (PUBLIC để Controller gọi được)
     */
    public function updateBookingTotal($booking_id)
    {
        try {
            // Tổng tiền items
            $items_total = $this->getTotalAmount($booking_id);

            // Lấy thông tin booking
            $booking = $this->bookingModel->find($booking_id);
            if (!$booking) {
                return false;
            }

            // Tính tiền từ người (adults + children)
            $adults = (int) ($booking['adults'] ?? 0);
            $children = (int) ($booking['children'] ?? 0);
            $price_adult = (float) ($booking['price_adult'] ?? 0);
            $price_children = (float) ($booking['price_children'] ?? 0);

            $person_total = ($adults * $price_adult) + ($children * $price_children);

            // Tổng tiền = tiền người + tiền items
            $total_amount = $person_total + $items_total;

            // Cập nhật vào bảng bookings
            $stmt = $this->pdo->prepare("UPDATE bookings SET total_amount = ? WHERE id = ?");
            return $stmt->execute([$total_amount, $booking_id]);

        } catch (PDOException $e) {
            error_log("UpdateBookingTotal Error: " . $e->getMessage());
            return false;
        }
    }

    /** ------------------------
     *  ✅ Lấy thống kê items theo loại
     */
    public function getItemsSummaryByType($booking_id)
    {
        try {
            $sql = "SELECT type, COUNT(*) as count, SUM(total_price) as total
                    FROM booking_item
                    WHERE booking_id = ?
                    GROUP BY type";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$booking_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("GetItemsSummaryByType Error: " . $e->getMessage());
            return [];
        }
    }

    /** ------------------------
     *  ✅ Validate dữ liệu item
     */
    public function validateItemData(array $data): array
    {
        $errors = [];

        if (empty(trim($data['description'] ?? ''))) {
            $errors[] = "Mô tả dịch vụ không được để trống.";
        }

        $qty = (int) ($data['qty'] ?? 0);
        if ($qty <= 0) {
            $errors[] = "Số lượng phải lớn hơn 0.";
        }

        $unit_price = (float) ($data['unit_price'] ?? 0);
        if ($unit_price < 0) {
            $errors[] = "Đơn giá không được âm.";
        }

        $valid_types = ['SERVICE', 'MEAL', 'ROOM', 'INSURANCE', 'TRANSPORT', 'OTHER'];
        if (!empty($data['type']) && !in_array($data['type'], $valid_types)) {
            $errors[] = "Loại dịch vụ không hợp lệ.";
        }

        return $errors;
    }
}