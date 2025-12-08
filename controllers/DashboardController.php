<?php

class DashboardController
{
    public $ProductModel;

    public function __construct()
    {

    }
    public function Dashboard()
    {

        // logic Dashboard
        include "./views/admin/Dashboard/index.php";
    }
}