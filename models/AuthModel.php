<?php
class AuthModel
{
    public $conn;
    public function __construct()
    {
        $this->conn = connectDB();
    }
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function signUp($password, $email, $fullName)
    {
        if ($this->getUserByEmail($email)) {
            return false;
        }
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (password, email, full_name) 
                VALUES (:password, :email,:full_name)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'password' => $hashedPassword,
            'email' => $email,
            'full_name' => $fullName
        ]);
    }


    public function signIn($email, $password)
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return null;
        }
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    public function checkRoleUser($id)
    {
        $sql = "SELECT * FROM roles WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}