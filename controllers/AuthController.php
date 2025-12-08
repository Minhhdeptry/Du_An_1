<?php
session_start();
require './models/AuthModel.php';
class AuthController
{
    protected $authModel;
    public function __construct()
    {
        $this->authModel = new AuthModel();
        // require_once './views/layout/headerAdminLayout.php';
    }
    public function SignIn()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Vui lòng điền tài khoản và mật khẩu';
                header('Location: sign-in');
                exit;
            }
            $user = $this->authModel->signIn(email: $email, password: $password);
            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'fullName' => $user['full_name'],
                    'email' => $user['email'],
                    'avatar' => $user['avatar']
                ];
                $_SESSION['success'] = 'Đăng nhập thành công!';
                $idUser = $_SESSION['user']['id'];
                $roleUser = $this->authModel->checkRoleUser($idUser);
                if ($roleUser['role'] === "admin") {
                    header('Location: dashboard');
                    exit;
                } else {
                    header('Location: /tech4tourism/');
                    exit;
                }
            
            } else {
                $_SESSION['error'] = '<span style="color: red;">Email hoặc mật khẩu không chính xác</span>';
                header('Location: sign-in');
                exit;
            }
        }
        include_once './views/auths/signIn.php';
    }
    public function SignUp()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirmPassword'];
            $fullName = $_POST['fullName'];


            if ($password !== $confirmPassword) {
                $_SESSION['errorConfirmPassword'] = '<span style="color: red;">Mật khẩu phải giống nhau </span>';
                header('Location: sign-up');
                exit;
            }
            if (!$fullName) {
                $_SESSION['errorFullName'] = '<span style="color: red;">Không bỏ qua trường này</span>';
                header('Location: sign-up');
                exit;
            }
            if (strlen($password) < 6) {
                $_SESSION['errorPassword'] = '<span style="color: red;">Mật khẩu phải có ít nhất 6 ký tự</span>';
                header('Location: sign-up');
                exit;
            }
            if (empty($email) || empty($password) || empty($fullName)) {
                $_SESSION['error'] = '<span style="color: red;">Vui lòng điền đầy đủ thông tin</span>';
                header('Location: sign-up');
                exit;
            }
            $checkUser = $this->authModel->getUserByEmail($email);
            if ($checkUser) {
                $_SESSION['errorEmail'] = '<span style="color: red;">Email đã được sử dụng!</span>';
                header('Location: sign-up');
                exit;
            }

            if ($this->authModel->signUp($password, $email, $fullName)) {
                $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                header('Location: sign-in');
                exit;
            } else {
                $_SESSION['error'] = '<span style="color: red; ">Đã xảy ra lỗi! Vui lòng thử lại.</span>';
                exit;
            }
        }

        include_once './views/auths/signUp.php';

    }
}
