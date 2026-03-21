<?php

namespace App\Controllers\Api;

use App\Models\MetricsModel;

class Metrics extends BaseController
{
    public function receive()
    {
        $json = $this->request->getJSON(true);

        if (empty($json)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'No JSON body provided.']);
        }

        $validation = \Config\Services::validation();

        $validation->setRules([
            'domain'       => 'required|max_length[255]',
            'path'         => 'required',
            'device_type'  => 'required|max_length[20]',
            'anonymized_ip' => 'required|max_length[45]',
            'user_uuid'    => 'permit_empty|max_length[36]',
            'username'     => 'permit_empty|max_length[100]',
            'is_admin'     => 'permit_empty|in_list[0,1]',
            'useragent'    => 'permit_empty',
            'load_time_ms' => 'permit_empty|integer',
            'window_width' => 'permit_empty|integer|greater_than[0]',
            'window_height' => 'permit_empty|integer|greater_than[0]',
        ]);

        if (!$validation->run($json)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => $validation->getErrors()]);
        }

        // If is_admin do not log the visit
        if (isset($json['is_admin']) && $json['is_admin'] == 1) {
            return $this->response
                ->setStatusCode(200)
                ->setJSON(['status' => 'admin visit - not logged']);
        }

        $model = new MetricsModel();

        $data = [
            'domain'        => $json['domain'],
            'path'          => $json['path'],
            'device_type'   => $json['device_type'],
            'anonymized_ip' => $json['anonymized_ip'],
            'user_uuid'     => $json['user_uuid'] ?? null,
            'username'      => $json['username'] ?? null,
            'is_admin'      => $json['is_admin'] ?? 0,
            'useragent'     => $json['useragent'] ?? null,
            'load_time_ms'  => $json['load_time_ms'] ?? null,
            'window_width'  => $json['window_width'] ?? null,
            'window_height' => $json['window_height'] ?? null,
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $model->insert($data);

        return $this->response
            ->setStatusCode(201)
            ->setJSON(['status' => 'success']);
    }
}
