<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse
    {
        // Redirect to admin dashboard if user is logged in
        return redirect()->to('/admin');
    }
}
