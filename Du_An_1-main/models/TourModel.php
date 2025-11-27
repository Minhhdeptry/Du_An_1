<?php

class TourModel {

   
    // Lấy tất cả tour
   
    public function getAll() {
        $conn = connectDB();
        $sql = "SELECT * FROM tours";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll();
    }

   
    // Lấy tour theo ID
   
    public function getById($id) {
        $conn = connectDB();
        $sql = "SELECT * FROM tours WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

   
    // Thêm tour
   
    public function create($name, $price, $desc, $cate, $image) {
        $conn = connectDB();
        $sql = "INSERT INTO tours (name, price, description, category_id, image)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        return $stmt->execute([$name, $price, $desc, $cate, $image]);
    }

   
    // Cập nhật tour
   
    public function update($id, $name, $price, $desc, $cate, $image) {
        $conn = connectDB();
        $sql = "UPDATE tours 
                SET name = ?, price = ?, description = ?, category_id = ?, image = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);

        return $stmt->execute([$name, $price, $desc, $cate, $image, $id]);
    }

    
    // Xóa tour theo 
   
    public function delete($id) {
        $conn = connectDB();
        $sql = "DELETE FROM tours WHERE id = ?";
        $stmt = $conn->prepare($sql);

        return $stmt->execute([$id]);
    }
}
