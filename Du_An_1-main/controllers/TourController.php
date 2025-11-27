<?php
require_once "./commons/function.php";

class ToursController
{
  

    private function getAllTours()
    {
        $conn = connectDB();
        $stmt = $conn->query("SELECT * FROM tours ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    private function getTourById($id)
    {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function createTour($name, $price, $description, $category_id, $image)
    {
        $conn = connectDB();
        $sql = "INSERT INTO tours (name, price, description, category_id, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$name, $price, $description, $category_id, $image]);
    }

    private function updateTour($id, $name, $price, $description, $category_id, $image)
    {
        $conn = connectDB();
        $sql = "UPDATE tours SET name=?, price=?, description=?, category_id=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$name, $price, $description, $category_id, $image, $id]);
    }

    private function deleteTour($id)
    {
        $conn = connectDB();
        $stmt = $conn->prepare("DELETE FROM tours WHERE id = ?");
        return $stmt->execute([$id]);
    }

    
    public function index()
    {
        $tours = $this->getAllTours();
        include_once "./views/Tour/index.php";
    }

  
    public function create()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $name = $_POST["name"];
            $price = $_POST["price"];
            $description = $_POST["description"];
            $category_id = $_POST["category_id"];

            // Upload hình
            $image = $_FILES["image"]["name"];
            move_uploaded_file($_FILES["image"]["tmp_name"], "assets/images/" . $image);

            $this->createTour($name, $price, $description, $category_id, $image);

            header("Location: index.php?act=tour");
            exit();
        }

        include_once "./views/Tour/create.php";
    }

    
    public function edit()
    {
        $id = $_GET["id"];
        $tour = $this->getTourById($id);

        include_once "./views/Tour/edit.php";
    }

    
    public function update()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $id = $_POST["id"];
            $tour = $this->getTourById($id);

            $name = $_POST["name"];
            $price = $_POST["price"];
            $description = $_POST["description"];
            $category_id = $_POST["category_id"];

            // Xử lý ảnh
            $image = $tour["image"];
            if (!empty($_FILES["image"]["name"])) {
                $image = $_FILES["image"]["name"];
                move_uploaded_file($_FILES["image"]["tmp_name"], "assets/images/" . $image);
            }

            $this->updateTour($id, $name, $price, $description, $category_id, $image);

            header("Location: index.php?act=tour");
            exit();
        }
    }

   
    public function delete()
    {
        $id = $_GET["id"];

        $this->deleteTour($id);

        header("Location: index.php?act=tour");
        exit();
    }
}
