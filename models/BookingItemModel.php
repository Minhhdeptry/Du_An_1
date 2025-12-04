<?php
class BookingItemModel
{
    private $pdo;

    public function __construct()
    {
        require_once __DIR__ . "/../commons/function.php";
        $this->pdo = connectDB();
    }

    public function addItem($booking_id, $description, $qty, $unit_price, $type='PERSON')
    {
        $total_price = $qty * $unit_price;
        $sql = "INSERT INTO booking_item (booking_id, description, qty, unit_price, total_price, type)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$booking_id, $description, $qty, $unit_price, $total_price, $type]);
    }

    public function getItemsByBooking($booking_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM booking_item WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByBooking($booking_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM booking_item WHERE booking_id = ?");
        return $stmt->execute([$booking_id]);
    }
}
