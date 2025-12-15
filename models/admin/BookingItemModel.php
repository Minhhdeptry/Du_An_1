<?php
require_once "./models/admin/BookingModel.php";

class BookingItemModel
{
    private $pdo;
    private $bookingModel;

    public function __construct(PDO $pdo)
    {
        // require_once "./commons/function.php";
        // $this->pdo = connectDB();
        // $this->bookingModel = new BookingModel($pdo);
        $this->pdo = $pdo;
    }

    public function addItem(int $booking_id, string $description, int $qty = 1, float $unit_price = 0.0, string $type = 'SERVICE'): bool
    {
        try {
            $description = trim($description);
            if ($description === '' || $qty <= 0 || $unit_price < 0) {
                error_log("Invalid item data: desc='$description', qty=$qty, price=$unit_price");
                return false;
            }

            $sql = "INSERT INTO booking_item 
                    (booking_id, description, qty, unit_price, type, is_deleted)
                    VALUES (?, ?, ?, ?, ?, 0)";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$booking_id, $description, $qty, $unit_price, $type]);

            if ($result) {
                error_log("✅ Added item to booking #$booking_id: $description x $qty");
                $this->recalculateBookingTotal($booking_id);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("BookingItemModel::addItem Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateItem(int $item_id, string $description, int $qty = 1, float $unit_price = 0.0): bool
    {
        try {
            $description = trim($description);
            if ($description === '' || $qty <= 0 || $unit_price < 0) {
                error_log("Invalid update data: desc='$description', qty=$qty, price=$unit_price");
                return false;
            }

            // Lấy booking_id trước để cập nhật tổng sau
            $booking_id = $this->getBookingIdFromItem($item_id);
            if (!$booking_id) {
                error_log("Item #$item_id not found");
                return false;
            }

            $sql = "UPDATE booking_item 
                    SET description = ?, qty = ?, unit_price = ?
                    WHERE id = ? AND is_deleted = 0";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$description, $qty, $unit_price, $item_id]);

            if ($result) {
                error_log("Updated item #$item_id in booking #$booking_id");
                $this->recalculateBookingTotal($booking_id);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("BookingItemModel::updateItem Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteItem(int $item_id): bool
    {
        try {
            $booking_id = $this->getBookingIdFromItem($item_id);
            if (!$booking_id) {
                error_log("Item #$item_id not found");
                return false;
            }

            // Soft delete
            $sql = "UPDATE booking_item SET is_deleted = 1 WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$item_id]);

            if ($result) {
                error_log("✅ Deleted item #$item_id from booking #$booking_id");
                $this->recalculateBookingTotal($booking_id);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("BookingItemModel::deleteItem Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAllByBooking(int $booking_id): bool
    {
        try {
            $sql = "UPDATE booking_item SET is_deleted = 1 WHERE booking_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$booking_id]);

            if ($result) {
                $this->recalculateBookingTotal($booking_id);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("BookingItemModel::deleteAllByBooking Error: " . $e->getMessage());
            return false;
        }
    }

    public function getItemsByBooking(int $booking_id, ?string $type = null): array
    {
        try {
            $sql = "SELECT * FROM booking_item WHERE booking_id = ? AND is_deleted = 0";
            $params = [$booking_id];

            if ($type !== null) {
                $sql .= " AND type = ?";
                $params[] = $type;
            }

            $sql .= " ORDER BY id ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("BookingItemModel::getItemsByBooking Error: " . $e->getMessage());
            return [];
        }
    }

    public function getItemsTotal(int $booking_id): float
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(total_price), 0) AS total 
                FROM booking_item 
                WHERE booking_id = ? AND is_deleted = 0
            ");
            $stmt->execute([$booking_id]);
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("BookingItemModel::getItemsTotal Error: " . $e->getMessage());
            return 0.0;
        }
    }

    private function getBookingIdFromItem(int $item_id): ?int
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT booking_id 
                FROM booking_item 
                WHERE id = ? AND is_deleted = 0
            ");
            $stmt->execute([$item_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['booking_id'] ?? null;
        } catch (PDOException $e) {
            error_log("BookingItemModel::getBookingIdFromItem Error: " . $e->getMessage());
            return null;
        }
    }

    public function recalculateBookingTotal(int $booking_id): bool
    {
        try {
            // Lấy thông tin booking
            $stmt = $this->pdo->prepare("
                SELECT adults, children, price_adult, price_children
                FROM bookings
                WHERE id = ?
            ");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                error_log("Booking #$booking_id not found");
                return false;
            }

            // Tính tiền tour
            $adults = (int) ($booking['adults'] ?? 0);
            $children = (int) ($booking['children'] ?? 0);
            $price_adult = (float) ($booking['price_adult'] ?? 0);
            $price_children = (float) ($booking['price_children'] ?? 0);

            $tour_amount = ($adults * $price_adult) + ($children * $price_children);

            // Tính tiền items
            $items_amount = $this->getItemsTotal($booking_id);

            // Tổng cuối cùng
            $new_total = $tour_amount + $items_amount;

            // Cập nhật vào DB
            $stmt = $this->pdo->prepare("
                UPDATE bookings 
                SET total_amount = ? 
                WHERE id = ?
            ");
            $result = $stmt->execute([$new_total, $booking_id]);

            if ($result) {
                error_log("✅ Recalculated booking #$booking_id: tour=$tour_amount + items=$items_amount = total=$new_total");
            }

            return $result;
        } catch (PDOException $e) {
            error_log("BookingItemModel::recalculateBookingTotal Error: " . $e->getMessage());
            return false;
        }
    }

    public function getSummaryByType(int $booking_id): array
    {
        try {
            $sql = "SELECT 
                        type,
                        COUNT(*) AS item_count,
                        SUM(qty) AS total_qty,
                        SUM(total_price) AS total_amount
                    FROM booking_item
                    WHERE booking_id = ? AND is_deleted = 0
                    GROUP BY type
                    ORDER BY total_amount DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$booking_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("BookingItemModel::getSummaryByType Error: " . $e->getMessage());
            return [];
        }
    }

    public function validateItem(array $data): array
    {
        $errors = [];

        if (empty(trim($data['description'] ?? ''))) {
            $errors[] = "Vui lòng nhập mô tả dịch vụ.";
        }

        $qty = (int) ($data['qty'] ?? 0);
        if ($qty <= 0) {
            $errors[] = "Số lượng phải lớn hơn 0.";
        }

        $unit_price = (float) ($data['unit_price'] ?? 0);
        if ($unit_price < 0) {
            $errors[] = "Đơn giá không được âm.";
        }

        $validTypes = ['SERVICE', 'MEAL', 'ROOM', 'INSURANCE', 'TRANSPORT', 'VISA', 'TICKET', 'GUIDE', 'OTHER'];
        $type = $data['type'] ?? 'SERVICE';
        if (!in_array($type, $validTypes)) {
            $errors[] = "Loại dịch vụ không hợp lệ.";
        }

        return $errors;
    }
}