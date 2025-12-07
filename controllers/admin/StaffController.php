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

    // ============ DANH SÁCH STAFF ============
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

    // ============ FORM THÊM MỚI ============
    public function create($act = null)
    {
        $pageTitle = "Thêm Hướng dẫn viên";
        $currentAct = $act;

        // ✅ BỎ check users vì giờ tự động tạo
        $view = "./views/admin/Staff/create.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ LƯU MỚI ============
    public function store()
    {
        error_log("=== STORE DEBUG START ===");
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        $data = $_POST;

        // ✅ VALIDATE dữ liệu cơ bản
        if (empty($data['full_name'])) {
            $_SESSION['error'] = "❌ Họ tên không được để trống!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        if (empty($data['email'])) {
            $_SESSION['error'] = "❌ Email không được để trống!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "❌ Email không hợp lệ!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        if (empty($data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại không được để trống!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại không hợp lệ! Phải có 10-11 chữ số.";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        // ✅ Check phone đã tồn tại
        if ($this->staffModel->findByPhone($data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại đã được sử dụng!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        // ✅ TỰ ĐỘNG TẠO USER HDV
        try {
            // ✅ FIX: Dùng biến local thay vì $this->pdo
            $pdo = connectDB();
            $pdo->beginTransaction();

            // 1. Tạo username từ email
            $username = explode('@', $data['email'])[0];

            // Check username trùng
            $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $checkUser->execute([$username]);
            if ($checkUser->fetch()) {
                // Nếu trùng, thêm số random
                $username = $username . rand(100, 999);
            }

            // Check email trùng
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->execute([$data['email']]);
            if ($checkEmail->fetch()) {
                throw new Exception("Email đã được sử dụng!");
            }

            // 2. Tạo password mặc định
            $defaultPassword = '123456';
            $passwordHash = password_hash($defaultPassword, PASSWORD_BCRYPT);

            // 3. Insert user
            $insertUser = $pdo->prepare("
                INSERT INTO users (username, password_hash, full_name, email, phone, role, is_active)
                VALUES (?, ?, ?, ?, ?, 'HDV', 1)
            ");

            $insertUser->execute([
                $username,
                $passwordHash,
                $data['full_name'],
                $data['email'],
                $data['phone']
            ]);

            $user_id = $pdo->lastInsertId();

            if (!$user_id) {
                throw new Exception("Không thể tạo tài khoản user!");
            }

            // 4. Set user_id vào data
            $data['user_id'] = $user_id;

            // ✅ Upload ảnh
            $data['profile_image'] = null;

            if (!empty($_FILES['profile_image']['name'])) {
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

                $data['profile_image'] = $uploadedPath;
            }

            // 5. Lưu staff
            $result = $this->staffModel->store($data);

            if (!$result) {
                throw new Exception("Không thể tạo hồ sơ nhân viên!");
            }

            // ✅ Commit transaction
            $pdo->commit();

            error_log("✅ Store success! User ID: $user_id");
            $_SESSION['success'] = "✅ Thêm hướng dẫn viên thành công!<br>
                                 📧 Email: {$data['email']}<br>
                                 👤 Username: <strong>$username</strong><br>
                                 🔑 Password: <strong>$defaultPassword</strong><br>
                                 <small class='text-warning'>(⚠️ Vui lòng đổi mật khẩu sau lần đăng nhập đầu tiên)</small>";
            header("Location: index.php?act=admin-staff");
            exit;

        } catch (Exception $e) {
            // Rollback nếu lỗi
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Store Exception: " . $e->getMessage());

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

    // ============ FORM SỬA ============
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

        // ✅ BỎ: Không cần lấy users nữa vì chỉ update info, không đổi user
        // $users = $this->userModel->getUsersByRole('HDV');

        $pageTitle = "Sửa Hướng dẫn viên: " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/edit.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ CẬP NHẬT ============
    public function update()
    {
        error_log("=== UPDATE DEBUG START ===");
        error_log("POST: " . print_r($_POST, true));
        error_log("FILES: " . print_r($_FILES, true));

        $data = $_POST;
        $id = $data['id'] ?? null;

        // ✅ Check ID
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID nhân viên!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // ✅ Lấy thông tin staff cũ
        $oldStaff = $this->staffModel->find($id);
        if (!$oldStaff) {
            $_SESSION['error'] = "❌ Nhân viên không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // ✅ Giữ nguyên user_id (không cho đổi)
        $data['user_id'] = $oldStaff['user_id'];

        // ✅ Validate phone
        if (empty($data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại không được để trống!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-edit&id={$id}");
            exit;
        }

        // ✅ Validate phone format
        if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại không hợp lệ! Phải có 10-11 chữ số.";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-edit&id={$id}");
            exit;
        }

        // ✅ Check phone trùng với staff khác
        $existingPhone = $this->staffModel->findByPhone($data['phone'], $id);
        if ($existingPhone) {
            error_log("Error: Phone already used by another staff");
            $_SESSION['error'] = "❌ Số điện thoại đã được sử dụng bởi nhân viên khác!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-edit&id={$id}");
            exit;
        }

        // ✅ Upload ảnh mới
        if (!empty($_FILES['profile_image']['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                $_SESSION['error'] = "❌ Chỉ chấp nhận file ảnh JPG, PNG, WEBP!";
                $_SESSION['old_data'] = $data;
                header("Location: index.php?act=admin-staff-edit&id={$id}");
                exit;
            }

            if ($_FILES['profile_image']['size'] > $maxSize) {
                $_SESSION['error'] = "❌ Kích thước ảnh tối đa 2MB!";
                $_SESSION['old_data'] = $data;
                header("Location: index.php?act=admin-staff-edit&id={$id}");
                exit;
            }

            $newImage = uploadFile($_FILES['profile_image'], 'assets/images/staff/');

            if ($newImage) {
                $data['profile_image'] = $newImage;

                // Xóa ảnh cũ
                if (!empty($oldStaff['profile_image']) && $oldStaff['profile_image'] !== $newImage) {
                    deleteFile($oldStaff['profile_image']);
                }
            } else {
                $_SESSION['error'] = "❌ Upload ảnh thất bại!";
                $_SESSION['old_data'] = $data;
                header("Location: index.php?act=admin-staff-edit&id={$id}");
                exit;
            }
        } else {
            $data['profile_image'] = $oldStaff['profile_image'];
        }

        $data['id'] = $id;

        // ✅ Update database
        try {
            $result = $this->staffModel->update($data);

            if ($result) {
                error_log("✅ Update success!");
                $_SESSION['success'] = "✅ Cập nhật hướng dẫn viên thành công!";
                header("Location: index.php?act=admin-staff");
            } else {
                error_log("❌ Update failed!");
                $_SESSION['error'] = "❌ Cập nhật thất bại! Vui lòng thử lại.";
                $_SESSION['old_data'] = $data;
                header("Location: index.php?act=admin-staff-edit&id={$id}");
            }
        } catch (Exception $e) {
            error_log("Update Exception: " . $e->getMessage());
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-edit&id={$id}");
        }

        exit;
    }

    // ============ XÓA ============
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

    // ============ XEM CHI TIẾT ============
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

    // ============ THỐNG KÊ ============
    public function statistics($act = null)
    {
        $pageTitle = "Thống kê Hướng dẫn viên";
        $currentAct = $act;

        $stats = $this->staffModel->getStats();
        $topStaffs = $this->staffModel->getTopRated(10);

        $view = "./views/admin/Staff/statistics.php";
        include "./views/layout/adminLayout.php";
    }
}
?>