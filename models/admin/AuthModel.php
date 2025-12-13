<?php
require_once __DIR__ . '/../../commons/env.php';
require_once __DIR__ . '/../../commons/function.php';

class AuthModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Đăng ký
    public function signUp(array $data)
    {
        $fullname = trim($data['fullname']);
        $email = trim($data['email']);
        $password = trim($data['password']);

        // Kiểm tra email đã tồn tại
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email đã tồn tại'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Tạo username từ email
        $username = explode('@', $email)[0];
        
        // Kiểm tra username trùng
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        while ($stmt->rowCount() > 0) {
            $username .= rand(10, 99);
            $stmt->execute(['username' => $username]);
        }

        // Insert user
        $stmt = $this->conn->prepare(
            "INSERT INTO users (username, full_name, email, password_hash, role, is_active, created_at)
             VALUES (:username, :full_name, :email, :password_hash, 'CUSTOMER', 1, NOW())"
        );
        
        $result = $stmt->execute([
            'username' => $username,
            'full_name' => $fullname,
            'email' => $email,
            'password_hash' => $hashedPassword
        ]);

        return $result 
            ? ['success' => true, 'message' => 'Đăng ký thành công'] 
            : ['success' => false, 'message' => 'Đăng ký thất bại'];
    }

    // Đăng nhập
    public function signIn(array $data)
    {
        $email = trim($data['email']);
        $password = trim($data['password']);

        // Tìm user theo email
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'Email không tồn tại'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Mật khẩu không chính xác'];
        }

        // Kiểm tra tài khoản active
        if ($user['is_active'] != 1) {
            return ['success' => false, 'message' => 'Tài khoản chưa kích hoạt'];
        }

        return [
            'success' => true, 
            'message' => 'Đăng nhập thành công', 
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }
}