<?php

namespace App\Models;

use CodeIgniter\Model;

class MetricsModel extends Model
{
    protected $table            = 'metrics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'domain',
        'path',
        'user_uuid',
        'username',
        'is_admin',
        'device_type',
        'anonymized_ip',
        'useragent',
        'load_time_ms',
        'window_width',
        'window_height',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get hit counts for various time periods in a single optimised query.
     */
    public function getHitCounts(): array
    {
        $row = $this->db->query("
            SELECT
                SUM(DATE(created_at) = CURDATE()) AS today,
                SUM(DATE(created_at) = CURDATE() - INTERVAL 1 DAY) AS yesterday,
                SUM(YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)) AS this_week,
                SUM(YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())) AS this_month,
                SUM(YEAR(created_at) = YEAR(NOW())) AS this_year,
                COUNT(*) AS total
            FROM metrics
        ")->getRowArray();

        return [
            'today'      => (int) ($row['today'] ?? 0),
            'yesterday'  => (int) ($row['yesterday'] ?? 0),
            'this_week'  => (int) ($row['this_week'] ?? 0),
            'this_month' => (int) ($row['this_month'] ?? 0),
            'this_year'  => (int) ($row['this_year'] ?? 0),
            'total'      => (int) ($row['total'] ?? 0),
        ];
    }

    /**
     * Get unique visitor (anonymized IP) counts across various time periods.
     */
    public function getUniqueVisitorCounts(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(DISTINCT CASE WHEN DATE(created_at) = CURDATE() THEN anonymized_ip END) AS today,
                COUNT(DISTINCT CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1) THEN anonymized_ip END) AS this_week,
                COUNT(DISTINCT CASE WHEN YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW()) THEN anonymized_ip END) AS this_month,
                COUNT(DISTINCT anonymized_ip) AS total
            FROM metrics
        ")->getRowArray();

        return [
            'today'      => (int) ($row['today'] ?? 0),
            'this_week'  => (int) ($row['this_week'] ?? 0),
            'this_month' => (int) ($row['this_month'] ?? 0),
            'total'      => (int) ($row['total'] ?? 0),
        ];
    }

    /**
     * Get daily hit counts for the last N days, filling gaps with zero.
     */
    public function getHitsByDay(int $days = 30): array
    {
        $rows = $this->db->query("
            SELECT DATE(created_at) AS day, COUNT(*) AS hits
            FROM metrics
            WHERE created_at >= CURDATE() - INTERVAL ? DAY
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ", [$days])->getResultArray();

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date          = date('Y-m-d', strtotime("-{$i} days"));
            $result[$date] = 0;
        }
        foreach ($rows as $row) {
            $result[$row['day']] = (int) $row['hits'];
        }

        return $result;
    }

    /**
     * Get top domains by hit count.
     */
    public function getTopDomains(int $limit = 10): array
    {
        return $this->db->query("
            SELECT domain, COUNT(*) AS hits
            FROM metrics
            GROUP BY domain
            ORDER BY hits DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    /**
     * Get top paths by hit count, optionally filtered by domain.
     */
    public function getTopPaths(int $limit = 10, ?string $domain = null): array
    {
        if ($domain !== null) {
            return $this->db->query("
                SELECT path, COUNT(*) AS hits
                FROM metrics
                WHERE domain = ?
                GROUP BY path
                ORDER BY hits DESC
                LIMIT ?
            ", [$domain, $limit])->getResultArray();
        }

        return $this->db->query("
            SELECT domain, path, COUNT(*) AS hits
            FROM metrics
            GROUP BY domain, path
            ORDER BY hits DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    /**
     * Get the most recent hits.
     */
    public function getLatestHits(int $limit = 20): array
    {
        return $this->db->query("
            SELECT id, domain, path, username, device_type, anonymized_ip, load_time_ms, created_at
            FROM metrics
            ORDER BY id DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    /**
     * Get device type breakdown with counts and percentages.
     */
    public function getDeviceBreakdown(): array
    {
        return $this->db->query("
            SELECT device_type, COUNT(*) AS hits
            FROM metrics
            GROUP BY device_type
            ORDER BY hits DESC
        ")->getResultArray();
    }

    /**
     * Get average and 95th-percentile page load time in milliseconds.
     */
    public function getLoadTimeStats(): array
    {
        $row = $this->db->query("
            SELECT
                ROUND(AVG(load_time_ms), 0) AS avg_ms,
                ROUND(MIN(load_time_ms), 0) AS min_ms,
                ROUND(MAX(load_time_ms), 0) AS max_ms
            FROM metrics
            WHERE load_time_ms IS NOT NULL
        ")->getRowArray();

        return [
            'avg' => (int) ($row['avg_ms'] ?? 0),
            'min' => (int) ($row['min_ms'] ?? 0),
            'max' => (int) ($row['max_ms'] ?? 0),
        ];
    }

    /**
     * Get hit counts for a specific domain across various time periods.
     */
    public function getDomainHitCounts(string $domain): array
    {
        $row = $this->db->query("
            SELECT
                SUM(DATE(created_at) = CURDATE()) AS today,
                SUM(DATE(created_at) = CURDATE() - INTERVAL 1 DAY) AS yesterday,
                SUM(YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)) AS this_week,
                SUM(YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())) AS this_month,
                SUM(YEAR(created_at) = YEAR(NOW())) AS this_year,
                COUNT(*) AS total
            FROM metrics
            WHERE domain = ?
        ", [$domain])->getRowArray();

        return [
            'today'      => (int) ($row['today'] ?? 0),
            'yesterday'  => (int) ($row['yesterday'] ?? 0),
            'this_week'  => (int) ($row['this_week'] ?? 0),
            'this_month' => (int) ($row['this_month'] ?? 0),
            'this_year'  => (int) ($row['this_year'] ?? 0),
            'total'      => (int) ($row['total'] ?? 0),
        ];
    }

    /**
     * Get daily hit counts for a specific domain over the last N days.
     */
    public function getDomainHitsByDay(string $domain, int $days = 30): array
    {
        $rows = $this->db->query("
            SELECT DATE(created_at) AS day, COUNT(*) AS hits
            FROM metrics
            WHERE domain = ?
            AND created_at >= CURDATE() - INTERVAL ? DAY
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ", [$domain, $days])->getResultArray();

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date          = date('Y-m-d', strtotime("-{$i} days"));
            $result[$date] = 0;
        }
        foreach ($rows as $row) {
            $result[$row['day']] = (int) $row['hits'];
        }

        return $result;
    }

    /**
     * Get device breakdown for a specific domain.
     */
    public function getDomainDeviceBreakdown(string $domain): array
    {
        return $this->db->query("
            SELECT device_type, COUNT(*) AS hits
            FROM metrics
            WHERE domain = ?
            GROUP BY device_type
            ORDER BY hits DESC
        ", [$domain])->getResultArray();
    }

    /**
     * Get all distinct domains.
     */
    public function getAllDomains(): array
    {
        return $this->db->query("
            SELECT DISTINCT domain FROM metrics ORDER BY domain ASC
        ")->getResultArray();
    }

    /**
     * Get all distinct device types.
     */
    public function getAllDeviceTypes(): array
    {
        return $this->db->query("
            SELECT DISTINCT device_type FROM metrics ORDER BY device_type ASC
        ")->getResultArray();
    }
}
