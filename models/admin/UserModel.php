<?php
class UserModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    // Lấy tất cả khách hàng
    public function getAllCustomers()
    {
        $sql = "SELECT * FROM users 
                WHERE role = 'CUSTOMER'
                ORDER BY id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy tất cả user theo role (STAFF, CUSTOMER,...)
    public function getUsersByRole($role = 'STAFF')
    {
        $sql = "SELECT * FROM users WHERE role = :role ORDER BY full_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Tìm kiếm theo phone hoặc full_name
    public function searchCustomer($keyword)
    {
        $sql = "
            SELECT *
            FROM users
            WHERE role = 'CUSTOMER'
              AND (full_name LIKE :kw OR phone LIKE :kw)
            ORDER BY id DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':kw' => "%$keyword%"
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function store($data)
    {
        $sql = "INSERT INTO users (username, password_hash, full_name, email, phone, role)
                VALUES (?, ?, ?, ?, ?, 'CUSTOMER')";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
            $data['email'],
            $data['phone']
        ]);
    }

    public function update($data)
    {
        $sql = "UPDATE users SET 
                    full_name=?, email=?, phone=?
                WHERE id=? AND role='CUSTOMER'";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['id']
        ]);
    }


    public function getBookingHistory($user_id)
    {
        $sql = "
        SELECT b.id, b.booking_code, t.title AS tour_name, s.depart_date,
               b.total_people, b.total_amount, b.status
        FROM bookings b
        JOIN tour_schedule s ON b.tour_schedule_id = s.id
        JOIN tours t ON s.tour_id = t.id
        WHERE b.user_id = :uid
        ORDER BY b.id DESC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithBookingCount()
    {
    $sql = "SELECT u.*, 
                   COUNT(b.id) AS total_bookings, 
                   IFNULL(SUM(b.total_amount), 0) AS total_paid
            FROM users u
            LEFT JOIN bookings b ON b.user_id = u.id
            WHERE u.role = 'CUSTOMER'
            GROUP BY u.id
            ORDER BY u.id ASC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=? AND role='CUSTOMER'");
        return $stmt->execute([$id]);
    }
}


