<?php
require_once "./models/admin/StaffModel.php";
require_once "./models/admin/UserModel.php";

class StaffController
{
    private $staffModel;
    private $userModel;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->staffModel = new StaffModel();
        $this->userModel = new UserModel();
    }

    /**
     * Danh sách staff
     */
    public function index($act = null)
    {
        $pageTitle = "Quản lý Hướng dẫn viên";
        $currentAct = $act;

        $keyword = trim($_GET['keyword'] ?? '');
        $staff_type = trim($_GET['staff_type'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $staffs = $this->staffModel->search($keyword, $staff_type, $status);

        $view = "./views/admin/Staff/index.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Form thêm mới
     */
    public function create($act = null)
    {
        $pageTitle = "Thêm Hướng dẫn viên";
        $currentAct = $act;

        $view = "./views/admin/Staff/create.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Lưu mới (với transaction đồng bộ)
     */
    public function store()
    {
        error_log("=== StaffController::store() START ===");

        $data = $_POST;

        // Validate
        $validationError = $this->validateStaffData($data);
        if ($validationError) {
            $_SESSION['error'] = $validationError;
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        // Check phone trùng
        if ($this->staffModel->findByPhone($data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại đã được sử dụng!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        // Dùng connection chung cho transaction
        $pdo = $this->staffModel->getConnection();

        try {
            $pdo->beginTransaction();

            // 1. Tạo username từ email
            $username = $this->generateUniqueUsername($pdo, $data['email']);

            // 2. Check email trùng
            $this->checkEmailExists($pdo, $data['email']);

            // 3. Tạo user
            $defaultPassword = '123456';
            $user_id = $this->createUser($pdo, [
                'username' => $username,
                'password' => $defaultPassword,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone']
            ]);

            // 4. Upload ảnh
            $data['user_id'] = $user_id;
            $data['profile_image'] = $this->handleImageUpload();

            // 5. Tạo staff
            $staff_id = $this->staffModel->store($data);

            if (!$staff_id) {
                throw new Exception("Không thể tạo hồ sơ nhân viên!");
            }

            $pdo->commit();

            error_log("✅ Created: User #$user_id, Staff #$staff_id");

            $_SESSION['success'] = $this->formatSuccessMessage($data['email'], $username, $defaultPassword);
            header("Location: index.php?act=admin-staff");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("❌ Store failed: " . $e->getMessage());

            // Xóa ảnh nếu đã upload
            if (!empty($data['profile_image'])) {
                deleteFile($data['profile_image']);
            }

            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }
    }

    /**
     * Form sửa
     */
    public function edit($act = null)
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID nhân viên!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($id);

        if (!$staff) {
            $_SESSION['error'] = "❌ Nhân viên không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $pageTitle = "Sửa Hướng dẫn viên: " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/edit.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Cập nhật
     */
    public function update()
    {
        error_log("=== StaffController::update() START ===");

        $data = $_POST;
        $id = $data['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID nhân viên!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $oldStaff = $this->staffModel->find($id);
        if (!$oldStaff) {
            $_SESSION['error'] = "❌ Nhân viên không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // Giữ nguyên user_id
        $data['user_id'] = $oldStaff['user_id'];

        // Validate phone
        if (empty($data['phone']) || !preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại không hợp lệ!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-edit&id={$id}");
            exit;
        }

        // Check phone trùng với staff khác
        $existingPhone = $this->staffModel->findByPhone($data['phone'], $id);
        if ($existingPhone) {
            $_SESSION['error'] = "❌ Số điện thoại đã được sử dụng bởi nhân viên khác!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-edit&id={$id}");
            exit;
        }

        // Upload ảnh mới
        $newImage = $this->handleImageUpload();
        if ($newImage) {
            $data['profile_image'] = $newImage;
            // Xóa ảnh cũ
            if (!empty($oldStaff['profile_image']) && $oldStaff['profile_image'] !== $newImage) {
                deleteFile($oldStaff['profile_image']);
            }
        } else {
            $data['profile_image'] = $oldStaff['profile_image'];
        }

        try {
            $result = $this->staffModel->update($data);

            if ($result) {
                error_log("✅ Update success!");
                $_SESSION['success'] = "✅ Cập nhật hướng dẫn viên thành công!";
                header("Location: index.php?act=admin-staff");
            } else {
                throw new Exception("Cập nhật thất bại!");
            }
        } catch (Exception $e) {
            error_log("Update Exception: " . $e->getMessage());
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-edit&id={$id}");
        }

        exit;
    }

    /**
     * Xóa
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID nhân viên!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($id);

        if (!$staff) {
            $_SESSION['error'] = "❌ Nhân viên không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        if ($this->staffModel->delete($id)) {
            $_SESSION['success'] = "✅ Đã xóa hướng dẫn viên: " . $staff['full_name'];
        } else {
            $_SESSION['error'] = "❌ Không thể xóa! HDV này đang có tour hoặc có ràng buộc dữ liệu.";
        }

        header("Location: index.php?act=admin-staff");
        exit;
    }

    /**
     * Xem chi tiết
     */
    public function detail($act = null)
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID nhân viên!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($id);

        if (!$staff) {
            $_SESSION['error'] = "❌ Nhân viên không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $pageTitle = "Chi tiết HDV: " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/detail.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ PRIVATE HELPERS ============

    /**
     * Validate dữ liệu staff
     */
    private function validateStaffData($data)
    {
        if (empty($data['full_name'])) {
            return "❌ Họ tên không được để trống!";
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "❌ Email không hợp lệ!";
        }

        if (empty($data['phone']) || !preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            return "❌ Số điện thoại không hợp lệ! Phải có 10-11 chữ số.";
        }

        return null;
    }

    /**
     * Tạo username unique
     */
    private function generateUniqueUsername($pdo, $email)
    {
        $username = explode('@', $email)[0];

        $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $checkUser->execute([$username]);

        if ($checkUser->fetch()) {
            $username = $username . rand(100, 999);
        }

        return $username;
    }

    /**
     * Check email đã tồn tại
     */
    private function checkEmailExists($pdo, $email)
    {
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);

        if ($checkEmail->fetch()) {
            throw new Exception("Email đã được sử dụng!");
        }
    }

    /**
     * Tạo user mới
     */
    private function createUser($pdo, $userData)
    {
        $passwordHash = password_hash($userData['password'], PASSWORD_BCRYPT);

        $insertUser = $pdo->prepare("
            INSERT INTO users (username, password_hash, full_name, email, phone, role, is_active)
            VALUES (?, ?, ?, ?, ?, 'HDV', 1)
        ");

        $insertUser->execute([
            $userData['username'],
            $passwordHash,
            $userData['full_name'],
            $userData['email'],
            $userData['phone']
        ]);

        $user_id = $pdo->lastInsertId();

        if (!$user_id) {
            throw new Exception("Không thể tạo tài khoản user!");
        }

        return (int)$user_id;
    }

    /**
     * Xử lý upload ảnh
     */
    private function handleImageUpload()
    {
        if (empty($_FILES['profile_image']['name'])) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            throw new Exception("Chỉ chấp nhận file ảnh JPG, PNG, WEBP!");
        }

        if ($_FILES['profile_image']['size'] > $maxSize) {
            throw new Exception("Kích thước ảnh tối đa 2MB!");
        }

        $uploadedPath = uploadFile($_FILES['profile_image'], 'assets/images/staff/');

        if (!$uploadedPath) {
            throw new Exception("Upload ảnh thất bại!");
        }

        return $uploadedPath;
    }

    /**
     * Format success message
     */
    private function formatSuccessMessage($email, $username, $password)
    {
        return "✅ Thêm hướng dẫn viên thành công!<br>
                📧 Email: {$email}<br>
                👤 Username: <strong>{$username}</strong><br>
                🔑 Password: <strong>{$password}</strong><br>
                <small class='text-warning'>(⚠️ Vui lòng đổi mật khẩu sau lần đăng nhập đầu tiên)</small>";
    }
}
?>