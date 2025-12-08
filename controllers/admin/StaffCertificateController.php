<?php
// ============================================
// FILE 1: controllers/admin/StaffCertificateController.php
// ============================================

require_once "./models/admin/StaffModel.php";
require_once "./models/admin/StaffCertificateModel.php";

class StaffCertificateController
{
    private $staffModel;
    private $certModel;

    public function __construct()
    {
        $this->staffModel = new StaffModel();
        $this->certModel = new StaffCertificateModel();
    }

    /**
     * Danh sách chứng chỉ của HDV
     */
    public function index($act)
    {
        $staff_id = $_GET['staff_id'] ?? null;

        if (!$staff_id) {
            $_SESSION['error'] = "❌ Không tìm thấy HDV!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($staff_id);
        if (!$staff) {
            $_SESSION['error'] = "❌ HDV không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $certificates = $this->certModel->getStaffCertificates($staff_id);

        $pageTitle = "Chứng chỉ - " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/certificates.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Form thêm chứng chỉ
     */
    public function create($act)
    {
        $staff_id = $_GET['staff_id'] ?? null;

        if (!$staff_id) {
            $_SESSION['error'] = "❌ Không tìm thấy HDV!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($staff_id);
        if (!$staff) {
            $_SESSION['error'] = "❌ HDV không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $pageTitle = "Thêm chứng chỉ - " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/certificate_create.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Lưu chứng chỉ
     */
    public function store()
    {
        $data = $_POST;
        $staff_id = $data['staff_id'] ?? null;

        if (!$staff_id) {
            $_SESSION['error'] = "❌ Thiếu thông tin HDV!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // Validate
        if (empty($data['certificate_name'])) {
            $_SESSION['error'] = "❌ Tên chứng chỉ không được để trống!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-cert-create&staff_id=" . $staff_id);
            exit;
        }

        try {
            // Upload file
            $data['certificate_file'] = $this->handleFileUpload();

            if ($this->certModel->addCertificate($data)) {
                $_SESSION['success'] = "✅ Thêm chứng chỉ thành công!";
            } else {
                throw new Exception("Không thể thêm chứng chỉ!");
            }

            header("Location: index.php?act=admin-staff-cert&staff_id=" . $staff_id);
            exit;

        } catch (Exception $e) {
            // Xóa file nếu đã upload
            if (!empty($data['certificate_file'])) {
                deleteFile($data['certificate_file']);
            }

            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-cert-create&staff_id=" . $staff_id);
            exit;
        }
    }

    /**
     * Form sửa chứng chỉ
     */
    public function edit($act)
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy chứng chỉ!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM staff_certificates WHERE id = ?");
        $stmt->execute([$id]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$certificate) {
            $_SESSION['error'] = "❌ Chứng chỉ không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($certificate['staff_id']);

        $pageTitle = "Sửa chứng chỉ - " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/certificate_edit.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Cập nhật chứng chỉ
     */
    public function update()
    {
        $id = $_POST['id'] ?? null;
        $data = $_POST;

        if (!$id) {
            $_SESSION['error'] = "❌ Thiếu thông tin chứng chỉ!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // Lấy thông tin cũ
        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM staff_certificates WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$old) {
            $_SESSION['error'] = "❌ Chứng chỉ không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        try {
            // Upload file mới
            $newFile = $this->handleFileUpload();
            if ($newFile) {
                $data['certificate_file'] = $newFile;
                // Xóa file cũ
                if (!empty($old['certificate_file'])) {
                    deleteFile($old['certificate_file']);
                }
            } else {
                $data['certificate_file'] = $old['certificate_file'];
            }

            if ($this->certModel->updateCertificate($id, $data)) {
                $_SESSION['success'] = "✅ Cập nhật chứng chỉ thành công!";
            } else {
                throw new Exception("Không thể cập nhật chứng chỉ!");
            }

            header("Location: index.php?act=admin-staff-cert&staff_id=" . $old['staff_id']);
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            header("Location: index.php?act=admin-staff-cert-edit&id=" . $id);
            exit;
        }
    }

    /**
     * Xóa chứng chỉ
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        $staff_id = $_GET['staff_id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy chứng chỉ!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        if ($this->certModel->deleteCertificate($id)) {
            $_SESSION['success'] = "✅ Đã xóa chứng chỉ!";
        } else {
            $_SESSION['error'] = "❌ Không thể xóa chứng chỉ!";
        }

        header("Location: index.php?act=admin-staff-cert&staff_id=" . $staff_id);
        exit;
    }

    /**
     * Danh sách chứng chỉ sắp hết hạn
     */
    public function expiring($act)
    {
        $days = $_GET['days'] ?? 30;
        $certificates = $this->certModel->getExpiringCertificates($days);

        $pageTitle = "Chứng chỉ sắp hết hạn";
        $currentAct = $act;
        $view = "./views/admin/Staff/certificates_expiring.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Upload file chứng chỉ
     */
    private function handleFileUpload()
    {
        if (empty($_FILES['certificate_file']['name'])) {
            return null;
        }

        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['certificate_file']['type'], $allowedTypes)) {
            throw new Exception("Chỉ chấp nhận file PDF, JPG, PNG!");
        }

        if ($_FILES['certificate_file']['size'] > $maxSize) {
            throw new Exception("Kích thước file tối đa 5MB!");
        }

        $uploadedPath = uploadFile($_FILES['certificate_file'], 'assets/files/certificates/');

        if (!$uploadedPath) {
            throw new Exception("Upload file thất bại!");
        }

        return $uploadedPath;
    }
}