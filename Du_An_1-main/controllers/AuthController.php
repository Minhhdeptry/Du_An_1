<?php
require './models/AuthModel.php';
class AuthController
{
    protected $model;
    public function __construct()
    {
        $this->model = new AuthModel();
        // require_once './views/layout/headerAdminLayout.php';
    }
    public function SignIn()
    {
        
        include_once './views/auths/signIn.php';
    }
    public function SignUp()
    {
        include_once './views/auths/signUp.php';

    }
}

