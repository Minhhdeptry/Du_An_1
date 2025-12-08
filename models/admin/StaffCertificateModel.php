<?php 
class StaffCertificateModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Lấy chứng chỉ của HDV
     */
    public function getStaffCertificates($staff_id)
    {
        $sql = "SELECT * FROM staff_certificates 
                WHERE staff_id = ?
                ORDER BY expiry_date DESC, issue_date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm chứng chỉ
     */
    public function addCertificate($data)
    {
        $sql = "INSERT INTO staff_certificates 
                (staff_id, certificate_name, certificate_number, issuing_organization,
                 issue_date, expiry_date, certificate_file, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['staff_id'],
            $data['certificate_name'],
            $data['certificate_number'] ?? null,
            $data['issuing_organization'] ?? null,
            $data['issue_date'] ?? null,
            $data['expiry_date'] ?? null,
            $data['certificate_file'] ?? null,
            $this->calculateStatus($data['expiry_date'] ?? null),
            $data['notes'] ?? null
        ]);
    }

    /**
     * Cập nhật chứng chỉ
     */
    public function updateCertificate($id, $data)
    {
        $sql = "UPDATE staff_certificates SET
                certificate_name = ?,
                certificate_number = ?,
                issuing_organization = ?,
                issue_date = ?,
                expiry_date = ?,
                certificate_file = ?,
                status = ?,
                notes = ?
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['certificate_name'],
            $data['certificate_number'] ?? null,
            $data['issuing_organization'] ?? null,
            $data['issue_date'] ?? null,
            $data['expiry_date'] ?? null,
            $data['certificate_file'] ?? null,
            $this->calculateStatus($data['expiry_date'] ?? null),
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Xóa chứng chỉ
     */
    public function deleteCertificate($id)
    {
        // Xóa file nếu có
        $stmt = $this->pdo->prepare("SELECT certificate_file FROM staff_certificates WHERE id = ?");
        $stmt->execute([$id]);
        $cert = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cert && !empty($cert['certificate_file'])) {
            deleteFile($cert['certificate_file']);
        }

        $stmt = $this->pdo->prepare("DELETE FROM staff_certificates WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Tính trạng thái chứng chỉ
     */
    private function calculateStatus($expiry_date)
    {
        if (!$expiry_date) return 'VALID';

        $today = strtotime('today');
        $expiry = strtotime($expiry_date);
        $days_diff = ($expiry - $today) / 86400;

        if ($days_diff < 0) return 'EXPIRED';
        if ($days_diff <= 30) return 'PENDING_RENEWAL';
        return 'VALID';
    }

    /**
     * Lấy chứng chỉ sắp hết hạn
     */
    public function getExpiringCertificates($days = 30)
    {
        $sql = "SELECT sc.*, s.*, u.full_name, u.email
                FROM staff_certificates sc
                JOIN staffs s ON s.id = sc.staff_id
                JOIN users u ON u.id = s.user_id
                WHERE sc.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  AND sc.expiry_date >= CURDATE()
                  AND sc.status IN ('VALID', 'PENDING_RENEWAL')
                  AND s.deleted_at IS NULL
                ORDER BY sc.expiry_date ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái hết hạn (chạy daily)
     */
    public function updateExpiredStatus()
    {
        $sql = "UPDATE staff_certificates 
                SET status = CASE
                    WHEN expiry_date < CURDATE() THEN 'EXPIRED'
                    WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'PENDING_RENEWAL'
                    ELSE 'VALID'
                END
                WHERE expiry_date IS NOT NULL";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}
