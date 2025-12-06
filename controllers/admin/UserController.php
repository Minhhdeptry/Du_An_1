<?php
require_once './models/admin/UserModel.php';

class UserController
{
    private $user;

    public function __construct()
    {
        $this->user = new UserModel();
    }

    public function index($act)
    {
        $keyword = $_GET['keyword'] ?? '';
        $currentAct = $act;

        if ($keyword) {
            // Lấy tất cả khách kèm số tour đã đặt
            $allUsers = $this->user->getAllWithBookingCount();

            // Lọc theo keyword
            $users = array_filter($allUsers, function ($u) use ($keyword) {
                return str_contains(strtolower($u['full_name']), strtolower($keyword)) ||
                    str_contains($u['phone'], $keyword);
            });
        } else {
            // Lấy tất cả khách kèm số tour đã đặt
            $users = $this->user->getAllWithBookingCount();
        }

        $view = "./views/admin/User/index.php";
        include "./views/layout/adminLayout.php";
    }



    public function create($act)
    {
        $currentAct = $act;
        $view = "./views/admin/User/create.php";
        include "./views/layout/adminLayout.php";
    }

    public function store()
    {
        $data = $_POST;

        $data["is_active"] = isset($_POST["is_active"]) ? 1 : 0;

        $this->user->store($data);

        header("Location: ?act=admin-user");
    }

    public function edit($act)
    {
        $id = $_GET["id"];
        $row = $this->user->find($id);
        $currentAct = $act;

        $view = "./views/admin/User/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update()
    {
        $data = $_POST;
        $data["is_active"] = isset($_POST["is_active"]) ? 1 : 0;

        $this->user->update($data);

        header("Location: ?act=admin-user");
    }
    public function history($act)
    {
        $user_id = $_GET['id'] ?? null;
        $currentAct = $act;

        if (!$user_id) {
            header("Location: ?act=admin-user");
            exit;
        }

        $user = $this->user->find($user_id);
        $bookings = $this->user->getBookingHistory($user_id);

        $view = "./views/admin/User/history.php";
        include "./views/layout/adminLayout.php";
    }


    public function delete()
    {
        $id = $_GET["id"];
        $this->user->delete($id);

        header("Location: ?act=admin-user");
    }
}
