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

    public function receivePwa()
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
            'user_uuid'    => 'permit_empty|max_length[36]',
            'username'     => 'permit_empty|max_length[100]',
            'is_admin'     => 'permit_empty|in_list[0,1]',
            'load_time_ms' => 'permit_empty|integer',
            'window_width' => 'permit_empty|integer|greater_than[0]',
            'window_height' => 'permit_empty|integer|greater_than[0]',
        ]);

        if (!$validation->run($json)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => $validation->getErrors()]);
        }

        $isAdmin = isset($GLOBALS['is_admin']) && $GLOBALS['is_admin'] === true
            ? 1
            : ($json['is_admin'] ?? 0);

        // If is_admin do not log the visit
        if ($isAdmin == 1) {
            return $this->response
                ->setStatusCode(200)
                ->setJSON(['status' => 'admin visit - not logged']);
        }

        $model = new MetricsModel();

        $data = [
            'domain'        => $json['domain'],
            'path'          => $json['path'],
            'device_type'   => $json['device_type'],
            'anonymized_ip' => $this->anonymizeIp($this->request->getIPAddress()),
            'user_uuid'     => $json['user_uuid'] ?? null,
            'username'      => $json['username'] ?? null,
            'is_admin'      => 0,
            'useragent'     => $this->request->getUserAgent()->getAgentString() ?: null,
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

    private function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Zero out the last 64 bits (last 4 groups of IPv6)
            $parts = explode(':', inet_ntop(inet_pton($ip)));
            $full  = array_pad($parts, 8, '0');
            $full  = array_map(fn($g) => str_pad($g, 4, '0', STR_PAD_LEFT), $full);
            for ($i = 4; $i < 8; $i++) {
                $full[$i] = '0000';
            }
            return implode(':', $full);
        }

        // IPv4: zero out the last octet
        $parts    = explode('.', $ip);
        $parts[3] = '0';
        return implode('.', $parts);
    }
}
