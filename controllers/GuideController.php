<?php

class GuideController
{
    public $GuideModel;

    public function __construct()
    {

    }
    public function index()
    {

        // logic Dashboard
        include "./views/guideViews/index.php";
    }
}