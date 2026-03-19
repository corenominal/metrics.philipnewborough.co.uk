<?php

namespace App\Controllers\Admin;

use App\Models\MetricsModel;

class Metrics extends BaseController
{
    /**
     * Full paginated hit log with optional filters.
     */
    public function index(): string
    {
        $model = new MetricsModel();

        $domain     = $this->request->getGet('domain') ?? '';
        $deviceType = $this->request->getGet('device_type') ?? '';
        $dateFrom   = $this->request->getGet('date_from') ?? '';
        $dateTo     = $this->request->getGet('date_to') ?? '';

        if (!empty($domain)) {
            $model->where('domain', $domain);
        }
        if (!empty($deviceType)) {
            $model->where('device_type', $deviceType);
        }
        if (!empty($dateFrom)) {
            $model->where('created_at >=', $dateFrom . ' 00:00:00');
        }
        if (!empty($dateTo)) {
            $model->where('created_at <=', $dateTo . ' 23:59:59');
        }

        $perPage = 50;
        $hits    = $model->orderBy('id', 'DESC')->paginate($perPage);
        $pager   = $model->pager;

        $rawModel = new MetricsModel();

        $data['hits']         = $hits;
        $data['pager']        = $pager;
        $data['domains']      = $rawModel->getAllDomains();
        $data['device_types'] = $rawModel->getAllDeviceTypes();
        $data['filters']      = compact('domain', 'deviceType', 'dateFrom', 'dateTo');
        $data['js']           = ['admin/metrics'];
        $data['css']          = ['admin/metrics'];
        $data['title']        = 'Hit Log';

        return view('admin/metrics/index', $data);
    }

    /**
     * Domains overview — all domains with summary stats.
     */
    public function domains(): string
    {
        $model = new MetricsModel();

        $data['domains']  = $model->getTopDomains(100);
        $data['js']       = ['admin/metrics'];
        $data['css']      = ['admin/metrics'];
        $data['title']    = 'Domains';

        return view('admin/metrics/domains', $data);
    }

    /**
     * Drill-down view for a single domain.
     */
    public function domain(string $domain): string
    {
        $domain = urldecode($domain);
        $model  = new MetricsModel();

        $data['domain']           = $domain;
        $data['hit_counts']       = $model->getDomainHitCounts($domain);
        $data['hits_by_day']      = $model->getDomainHitsByDay($domain, 30);
        $data['top_paths']        = $model->getTopPaths(20, $domain);
        $data['latest_hits']      = $model->where('domain', $domain)->orderBy('id', 'DESC')->findAll(15);
        $data['device_breakdown'] = $model->getDomainDeviceBreakdown($domain);
        $data['chartjs']         = true;
        $data['js']              = ['admin/metrics'];
        $data['css']             = ['admin/metrics'];
        $data['title']           = 'Domain: ' . $domain;

        return view('admin/metrics/domain', $data);
    }
}
