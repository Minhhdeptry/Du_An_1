<?php
require_once __DIR__ . '/../../models/admin/AuthModel.php';

class AuthController
{
    private $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    // ---- Đăng nhập ----
    public function SignIn()
    {
        // Nếu đã login, redirect
        if (isset($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];
            if ($role === 'ADMIN') {
                header("Location: index.php?act=dashboard");
            } else {
                header("Location: index.php");
            }
            exit();
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validate
            if (empty($email)) {
                $errors['general'] = "Email không được để trống";
            }
            if (empty($password)) {
                $errors['general'] = "Mật khẩu không được để trống";
            }

            if (empty($errors)) {
                $result = $this->authModel->signIn([
                    'email' => $email, 
                    'password' => $password
                ]);

                if ($result['success']) {
                    // ✅ Lưu user vào session
                    $_SESSION['user'] = $result['user'];

                    // Redirect theo role
                    $role = $result['user']['role'];
                    if ($role === 'ADMIN') {
                        header("Location: index.php?act=dashboard");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $errors['general'] = $result['message'];
                }
            }

            $_SESSION['error'] = $errors['general'] ?? null;
        }

        // Load view
        include __DIR__ . '/../../views/auths/signIn.php';
    }

    // ---- Đăng ký ----
    public function SignUp()
    {
        // Nếu đã login, redirect
        if (isset($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];
            if ($role === 'ADMIN') {
                header("Location: index.php?act=dashboard");
            } elseif ($role === 'HDV') {
                header("Location: index.php?act=assigned-tours");
            } else {
                header("Location: index.php");
            }
            exit();
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm = trim($_POST['confirm_password'] ?? '');

            // Validate
            if (empty($fullname)) {
                $errors['fullname'] = "Họ tên không được để trống";
            }

            if (empty($email)) {
                $errors['email'] = "Email không được để trống";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Email không hợp lệ";
            }

            if (empty($password)) {
                $errors['password'] = "Mật khẩu không được để trống";
            } elseif (strlen($password) < 6) {
                $errors['password'] = "Mật khẩu phải ít nhất 6 ký tự";
            }

            if ($password !== $confirm) {
                $errors['confirmPassword'] = "Mật khẩu xác nhận không khớp";
            }

            if (empty($errors)) {
                $result = $this->authModel->signUp([
                    'fullname' => $fullname,
                    'email' => $email,
                    'password' => $password
                ]);

                if ($result['success']) {
                    $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                    header("Location: index.php?act=sign-in");
                    exit();
                } else {
                    $errors['general'] = $result['message'];
                }
            }

            // Lưu lỗi vào session
            $_SESSION['errorFullname'] = $errors['fullname'] ?? null;
            $_SESSION['errorEmail'] = $errors['email'] ?? null;
            $_SESSION['errorPassword'] = $errors['password'] ?? null;
            $_SESSION['errorConfirmPassword'] = $errors['confirmPassword'] ?? null;
            $_SESSION['error'] = $errors['general'] ?? null;
        }

        // Load view
        include __DIR__ . '/../../views/auths/signUp.php';
    }

    // ---- Đăng xuất ----
    public function logout()
    {
        session_unset();
        session_destroy();
        header("Location: index.php?act=sign-in");
        exit();
    }
}
