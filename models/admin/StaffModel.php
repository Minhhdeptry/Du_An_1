<?php
class StaffModel
{
    private $pdo;
    private $lastError = null;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    // ============ Lấy tất cả staff ============
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

    // ============ Tìm kiếm & Lọc nâng cao ============
    public function search($keyword = '', $staff_type = '', $status = '')
    {
        $sql = "SELECT s.*, u.full_name, u.email, u.role
                FROM staffs s
                LEFT JOIN users u ON u.id = s.user_id
                WHERE u.role = 'HDV'";

        $params = [];

        // Tìm kiếm keyword
        if ($keyword !== '') {
            // ✅ FIX: Bỏ COLLATE utf8mb4_unicode_ci
            $sql .= " AND (
                COALESCE(u.full_name, '') LIKE :kw
                OR COALESCE(u.email, '') LIKE :kw
                OR COALESCE(s.phone, '') LIKE :kw
                OR COALESCE(s.id_number, '') LIKE :kw
            )";
            $params[':kw'] = "%$keyword%";
        }

        // Lọc theo phân loại
        if ($staff_type !== '') {
            $sql .= " AND s.staff_type = :staff_type";
            $params[':staff_type'] = $staff_type;
        }

        // Lọc theo trạng thái
        if ($status !== '') {
            $sql .= " AND s.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY s.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============ Thêm mới staff ============
    public function store($data)
    {
        error_log("=== StaffModel::store() START ===");
        error_log("Received data: " . print_r($data, true));

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

        error_log("Params: " . print_r($params, true));

        try {
            $result = $stmt->execute($params);
            $lastId = $this->pdo->lastInsertId();

            error_log("Execute result: " . ($result ? 'TRUE' : 'FALSE'));
            error_log("Last insert ID: " . $lastId);

            if ($result && $lastId > 0) {
                error_log("✅ Insert success!");
                return true;
            }

            error_log("❌ Insert failed - no ID returned");
            return false;

        } catch (PDOException $e) {
            error_log("❌ StaffModel::store() PDOException:");
            error_log("Message: " . $e->getMessage());
            error_log("Code: " . $e->getCode());
            error_log("SQL State: " . ($e->errorInfo[0] ?? 'N/A'));

            $this->lastError = $e->errorInfo;
            return false;
        }
    }

    // ============ Tìm staff theo ID ============
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

    // ============ Cập nhật staff ============
    public function update($data)
    {
        error_log("=== StaffModel::update() START ===");
        error_log("Received data: " . print_r($data, true));

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

        error_log("Params: " . print_r($params, true));

        try {
            $result = $stmt->execute($params);
            $rowCount = $stmt->rowCount();

            error_log("Execute result: " . ($result ? 'TRUE' : 'FALSE'));
            error_log("Rows affected: " . $rowCount);

            // Return true ngay cả khi không có thay đổi (dữ liệu giống cũ)
            if ($result) {
                if ($rowCount === 0) {
                    error_log("⚠️ No rows updated - data might be identical");
                } else {
                    error_log("✅ Update success!");
                }
                return true;
            }

            return false;

        } catch (PDOException $e) {
            error_log("❌ StaffModel::update() PDOException:");
            error_log("Message: " . $e->getMessage());
            error_log("Code: " . $e->getCode());
            error_log("SQL State: " . ($e->errorInfo[0] ?? 'N/A'));

            $this->lastError = $e->errorInfo;
            return false;
        }
    }

    // ============ Xóa staff ============
    public function delete($id)
    {
        try {
            // ✅ Bảng tour_schedule không có cột guide/staff
            // → Bỏ qua kiểm tra, hoặc thêm sau khi bảng được cập nhật

            // TODO: Nếu sau này thêm cột guide_id vào tour_schedule, uncomment đoạn này:
            /*
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM tour_schedule WHERE guide_id = ?");
            $checkStmt->execute([$id]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                error_log("Cannot delete - staff has tours");
                return false;
            }
            */

            // Lấy thông tin ảnh để xóa file
            $staff = $this->find($id);
            if ($staff && !empty($staff['profile_image'])) {
                deleteFile($staff['profile_image']);
            }

            // Xóa staff
            $stmt = $this->pdo->prepare("DELETE FROM staffs WHERE id=?");
            $result = $stmt->execute([$id]);

            error_log("Delete result: " . ($result ? 'TRUE' : 'FALSE'));
            return $result;

        } catch (PDOException $e) {
            error_log("StaffModel::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    // ============ Lấy thống kê staff ============
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

    // ============ Lấy top staff theo rating ============
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

    // ============ Kiểm tra user_id đã là staff chưa ============
    public function isUserAlreadyStaff($user_id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM staffs WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // ============ Kiểm tra trùng số điện thoại ============
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

    // ============ Lấy lỗi cuối cùng ============
    public function getLastError()
    {
        return $this->lastError;
    }
}
?>