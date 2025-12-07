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

        // Gọi Model với filter
        $staffs = $this->staffModel->search($keyword, $staff_type, $status);

        $view = "./views/admin/Staff/index.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ FORM THÊM MỚI ============
    public function create($act = null)
    {
        $pageTitle = "Thêm Hướng dẫn viên";
        $currentAct = $act;

        // Lấy danh sách user có role='HDV'
        $users = $this->userModel->getUsersByRole('HDV');

        // Kiểm tra xem có user HDV nào không
        if (empty($users)) {
            $_SESSION['error'] = "⚠️ Chưa có user HDV nào! Vui lòng tạo user với role='HDV' trước.";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $view = "./views/admin/Staff/create.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ LƯU MỚI ============
    public function store()
    {
        $data = $_POST;

        // ✅ Validate dữ liệu
        if (empty($data['user_id'])) {
            $_SESSION['error'] = "❌ Vui lòng chọn tài khoản user!";
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        if (empty($data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại không được để trống!";
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        // ✅ Kiểm tra user đã là staff chưa
        if ($this->staffModel->isUserAlreadyStaff($data['user_id'])) {
            $_SESSION['error'] = "❌ User này đã là nhân viên rồi! Vui lòng chọn user khác.";
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        // ✅ Xử lý upload ảnh
        if (!empty($_FILES['profile_image']['name'])) {
            // Kiểm tra file upload
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                $_SESSION['error'] = "❌ Chỉ chấp nhận file ảnh JPG, PNG, WEBP!";
                header("Location: index.php?act=admin-staff-create");
                exit;
            }

            if ($_FILES['profile_image']['size'] > $maxSize) {
                $_SESSION['error'] = "❌ Kích thước ảnh tối đa 2MB!";
                header("Location: index.php?act=admin-staff-create");
                exit;
            }

            // Upload ảnh
            $data['profile_image'] = uploadFile($_FILES['profile_image'], 'assets/images/staff/');
            
            if (!$data['profile_image']) {
                $_SESSION['error'] = "❌ Upload ảnh thất bại! Vui lòng thử lại.";
                header("Location: index.php?act=admin-staff-create");
                exit;
            }
        } else {
            $data['profile_image'] = null;
        }

        // ✅ Lưu vào database
        if ($this->staffModel->store($data)) {
            $_SESSION['success'] = "✅ Thêm hướng dẫn viên thành công!";
            header("Location: index.php?act=admin-staff");
        } else {
            $_SESSION['error'] = "❌ Thêm thất bại! Vui lòng kiểm tra lại thông tin.";
            header("Location: index.php?act=admin-staff-create");
        }
        exit;
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

        // Lấy thông tin staff
        $staff = $this->staffModel->find($id);
        
        if (!$staff) {
            $_SESSION['error'] = "❌ Nhân viên không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // Lấy danh sách HDV
        $users = $this->userModel->getUsersByRole('HDV');

        $pageTitle = "Sửa Hướng dẫn viên: " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/edit.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ CẬP NHẬT ============
    public function update()
    {
        $data = $_POST;
        $id = $data['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID nhân viên!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // ✅ Validate dữ liệu
        if (empty($data['user_id'])) {
            $_SESSION['error'] = "❌ Vui lòng chọn tài khoản user!";
            header("Location: index.php?act=admin-staff-edit&id={$id}");
            exit;
        }

        if (empty($data['phone'])) {
            $_SESSION['error'] = "❌ Số điện thoại không được để trống!";
            header("Location: index.php?act=admin-staff-edit&id={$id}");
            exit;
        }

        // ✅ Xử lý upload ảnh mới
        if (!empty($_FILES['profile_image']['name'])) {
            // Kiểm tra file upload
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                $_SESSION['error'] = "❌ Chỉ chấp nhận file ảnh JPG, PNG, WEBP!";
                header("Location: index.php?act=admin-staff-edit&id={$id}");
                exit;
            }

            if ($_FILES['profile_image']['size'] > $maxSize) {
                $_SESSION['error'] = "❌ Kích thước ảnh tối đa 2MB!";
                header("Location: index.php?act=admin-staff-edit&id={$id}");
                exit;
            }

            // Upload ảnh mới
            $newImage = uploadFile($_FILES['profile_image'], 'assets/images/staff/');
            
            if ($newImage) {
                $data['profile_image'] = $newImage;
                
                // Xóa ảnh cũ nếu có
                if (!empty($data['old_profile_image']) && $data['old_profile_image'] !== $newImage) {
                    deleteFile($data['old_profile_image']);
                }
            } else {
                $_SESSION['error'] = "❌ Upload ảnh thất bại!";
                header("Location: index.php?act=admin-staff-edit&id={$id}");
                exit;
            }
        } else {
            // Giữ nguyên ảnh cũ
            $data['profile_image'] = $data['old_profile_image'] ?? null;
        }

        // ✅ Cập nhật vào database
        if ($this->staffModel->update($data)) {
            $_SESSION['success'] = "✅ Cập nhật hướng dẫn viên thành công!";
            header("Location: index.php?act=admin-staff");
        } else {
            $_SESSION['error'] = "❌ Cập nhật thất bại! Vui lòng thử lại.";
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

        // Lấy thông tin staff để hiển thị tên khi xóa
        $staff = $this->staffModel->find($id);
        
        if (!$staff) {
            $_SESSION['error'] = "❌ Nhân viên không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // ✅ Thực hiện xóa
        if ($this->staffModel->delete($id)) {
            $_SESSION['success'] = "✅ Đã xóa hướng dẫn viên: " . $staff['full_name'];
        } else {
            $_SESSION['error'] = "❌ Không thể xóa! HDV này đang có tour hoặc có ràng buộc dữ liệu.";
        }

        header("Location: index.php?act=admin-staff");
        exit;
    }

    // ============ XEM CHI TIẾT (Optional) ============
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

        // Lấy thống kê tour đã dẫn (nếu có bảng liên kết)
        // $tourStats = $this->staffModel->getTourStatsByStaff($id);

        $pageTitle = "Chi tiết HDV: " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/detail.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ THỐNG KÊ (Optional) ============
    public function statistics($act = null)
    {
        $pageTitle = "Thống kê Hướng dẫn viên";
        $currentAct = $act;

        // Lấy thống kê
        $stats = $this->staffModel->getStats();
        $topStaffs = $this->staffModel->getTopRated(10);

        $view = "./views/admin/Staff/statistics.php";
        include "./views/layout/adminLayout.php";
    }
}