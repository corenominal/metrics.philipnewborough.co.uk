<?php

namespace App\Controllers\Admin;

class Reset extends BaseController
{
    public function index(): string
    {
        $data['js']    = ['admin/reset'];
        $data['css']   = ['admin/reset'];
        $data['title'] = 'Reset Stats';

        return view('admin/reset', $data);
    }

    public function reset()
    {
        $db = \Config\Database::connect();
        $db->table('metrics')->truncate();

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'All stats have been reset.',
        ]);
    }
}
