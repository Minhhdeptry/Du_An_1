<?php
// ============================================
// FILE 1: models/admin/StaffModel.php
// ============================================

class StaffModel
{
    private $pdo;
    private $lastError = null;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Lấy PDO connection để dùng chung transaction
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Lấy tất cả staff
     */
    public function getAll()
    {
        $sql = "SELECT s.*, u.full_name, u.email, u.role
                FROM staffs s
                LEFT JOIN users u ON u.id = s.user_id
                WHERE u.role = 'HDV'
                ORDER BY s.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm kiếm & Lọc staff
     */
    public function search($keyword = '', $staff_type = '', $status = '')
    {
        $sql = "SELECT s.*, u.full_name, u.email, u.role
            FROM staffs s
            LEFT JOIN users u ON u.id = s.user_id
            WHERE u.role = 'HDV'";

        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (
            u.full_name LIKE :kw1
            OR u.email LIKE :kw2
            OR s.phone LIKE :kw3
            OR s.id_number LIKE :kw4
        )";

            $kw = "%$keyword%";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
        }

        if ($staff_type !== '') {
            $sql .= " AND s.staff_type = :staff_type";
            $params[':staff_type'] = $staff_type;
        }

        if ($status !== '') {
            $sql .= " AND s.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY s.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStaffByUserId($user_id)
    {
        $sql = "SELECT s.*, u.full_name, u.email 
                FROM staffs s
                JOIN users u ON u.id = s.user_id
                WHERE s.user_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Thêm mới staff (KHÔNG tự quản lý transaction)
     * @return int|false Staff ID nếu thành công, false nếu thất bại
     * @throws PDOException
     */
    public function store($data)
    {
        error_log("=== StaffModel::store() START ===");

        $sql = "INSERT INTO staffs(
            user_id, phone, id_number, qualification, status,
            date_of_birth, profile_image, staff_type, certifications,
            languages, experience_years, rating, health_status,
            tour_history, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            $data["user_id"],
            $data["phone"],
            $data["id_number"] ?? null,
            $data["qualification"] ?? null,
            $data["status"] ?? 'ACTIVE',
            $data["date_of_birth"] ?? null,
            $data["profile_image"] ?? null,
            $data["staff_type"] ?? 'DOMESTIC',
            $data["certifications"] ?? null,
            $data["languages"] ?? null,
            $data["experience_years"] ?? 0,
            $data["rating"] ?? null,
            $data["health_status"] ?? 'good',
            $data["tour_history"] ?? null,
            $data["notes"] ?? null
        ];

        error_log("Executing INSERT with params: " . print_r($params, true));

        $result = $stmt->execute($params);
        $lastId = $this->pdo->lastInsertId();

        if ($result && $lastId > 0) {
            error_log("✅ Staff inserted with ID: $lastId");
            return (int) $lastId;
        }

        error_log("❌ Insert failed");
        return false;
    }

    /**
     * Tìm staff theo ID
     */
    public function find($id)
    {
        $sql = "SELECT s.*, u.full_name, u.email, u.role
                FROM staffs s
                LEFT JOIN users u ON u.id = s.user_id
                WHERE s.id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật staff
     */
    public function update($data)
    {
        error_log("=== StaffModel::update() START ===");

        $sql = "UPDATE staffs SET 
            user_id=?, phone=?, id_number=?, qualification=?, status=?,
            date_of_birth=?, profile_image=?, staff_type=?, certifications=?,
            languages=?, experience_years=?, rating=?, health_status=?,
            tour_history=?, notes=?
            WHERE id=?";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            $data["user_id"],
            $data["phone"],
            $data["id_number"] ?? null,
            $data["qualification"] ?? null,
            $data["status"] ?? 'ACTIVE',
            $data["date_of_birth"] ?? null,
            $data["profile_image"] ?? null,
            $data["staff_type"] ?? 'DOMESTIC',
            $data["certifications"] ?? null,
            $data["languages"] ?? null,
            $data["experience_years"] ?? 0,
            $data["rating"] ?? null,
            $data["health_status"] ?? 'good',
            $data["tour_history"] ?? null,
            $data["notes"] ?? null,
            $data["id"]
        ];

        $result = $stmt->execute($params);
        $rowCount = $stmt->rowCount();

        error_log("Update result: " . ($result ? 'TRUE' : 'FALSE'));
        error_log("Rows affected: " . $rowCount);

        return $result;
    }

    /**
     * Xóa staff
     */
    public function delete($id)
    {
        try {
            $staff = $this->find($id);
            if ($staff && !empty($staff['profile_image'])) {
                deleteFile($staff['profile_image']);
            }

            $stmt = $this->pdo->prepare("DELETE FROM staffs WHERE id=?");
            $result = $stmt->execute([$id]);

            error_log("Delete staff ID $id: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;

        } catch (PDOException $e) {
            error_log("StaffModel::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thống kê staff
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'INACTIVE' THEN 1 ELSE 0 END) as inactive,
                    AVG(CASE WHEN rating IS NOT NULL THEN rating ELSE 0 END) as avg_rating,
                    SUM(experience_years) as total_experience
                FROM staffs s
                LEFT JOIN users u ON u.id = s.user_id
                WHERE u.role = 'HDV'";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Top staff theo rating
     */
    public function getTopRated($limit = 5)
    {
        $sql = "SELECT s.*, u.full_name, u.email
                FROM staffs s
                LEFT JOIN users u ON u.id = s.user_id
                WHERE u.role = 'HDV' 
                  AND s.rating IS NOT NULL
                  AND s.status = 'ACTIVE'
                ORDER BY s.rating DESC, s.experience_years DESC
                LIMIT ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra user đã là staff chưa
     */
    public function isUserAlreadyStaff($user_id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM staffs WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Kiểm tra phone trùng
     */
    public function findByPhone($phone, $excludeId = null)
    {
        $sql = "SELECT id FROM staffs WHERE phone = ?";
        $params = [$phone];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy lỗi cuối cùng
     */
    public function getLastError()
    {
        return $this->lastError;
    }
}
