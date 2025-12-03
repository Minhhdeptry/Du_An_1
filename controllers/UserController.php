<?php
require_once './models/UserModel.php';

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
            $users = $this->user->searchCustomer($keyword);
        } else {
            $users = $this->user->getAllCustomers();
        }

        require_once './views/admin/User/index.php';
    }


    public function create($act)
    {
        $currentAct = $act;
        require_once './views/admin/User/create.php';
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


        require_once './views/admin/User/edit.php';
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

        require_once './views/admin/User/history.php';
    }


    public function delete()
    {
        $id = $_GET["id"];
        $this->user->delete($id);

        header("Location: ?act=admin-user");
    }
}
