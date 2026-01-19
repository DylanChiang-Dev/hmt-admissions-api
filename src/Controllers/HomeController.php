<?php
namespace HmtAdmissions\Api\Controllers;

class HomeController {
    public function index() {
        return ['status' => 'ok', 'version' => '1.0.0'];
    }
}
