<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>

<?php
$totalHits = $hit_counts['total'];

// Build chart data arrays for Chart.js (passed as JSON)
$chartLabels = json_encode(array_map(fn($d) => date('d M', strtotime($d)), array_keys($hits_by_day)));
$chartValues = json_encode(array_values($hits_by_day));

// Device breakdown for doughnut chart
$deviceLabels = json_encode(array_column($device_breakdown, 'device_type'));
$deviceValues = json_encode(array_map('intval', array_column($device_breakdown, 'hits')));
?>

<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom border-1 mb-4 pb-3 d-flex align-items-center justify-content-between gap-3">
        <h2 class="mb-0"><i class="bi bi-speedometer2 me-2 text-secondary"></i>Dashboard</h2>
        <span class="text-secondary small">Updated: <?= date('d M Y, H:i') ?></span>
    </div>

    <!-- ── Hit count stat cards ───────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <?php
        $statCards = [
            ['label' => 'Today',      'value' => $hit_counts['today'],      'icon' => 'bi-calendar-day',    'colour' => 'primary'],
            ['label' => 'Yesterday',  'value' => $hit_counts['yesterday'],  'icon' => 'bi-calendar2',       'colour' => 'secondary'],
            ['label' => 'This Week',  'value' => $hit_counts['this_week'],  'icon' => 'bi-calendar-week',   'colour' => 'info'],
            ['label' => 'This Month', 'value' => $hit_counts['this_month'], 'icon' => 'bi-calendar-month',  'colour' => 'success'],
            ['label' => 'This Year',  'value' => $hit_counts['this_year'],  'icon' => 'bi-calendar-check',  'colour' => 'warning'],
            ['label' => 'All Time',   'value' => $hit_counts['total'],      'icon' => 'bi-bar-chart-fill',  'colour' => 'danger'],
        ];
        ?>
        <?php foreach ($statCards as $card): ?>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 border-0 bg-dark-subtle">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card__icon text-<?= $card['colour'] ?> fs-2">
                        <i class="bi <?= $card['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="stat-card__value fw-bold fs-4 lh-1"><?= number_format($card['value']) ?></div>
                        <div class="stat-card__label text-secondary small"><?= $card['label'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Unique visitors + load time + device types ────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 border-0 bg-dark-subtle">
                <div class="card-header border-bottom-0 text-secondary small text-uppercase fw-semibold py-2">
                    <i class="bi bi-people-fill me-1"></i> Unique Visitors
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between border-bottom border-secondary-subtle py-2">
                        <span class="text-secondary">Today</span>
                        <span class="fw-semibold"><?= number_format($unique_counts['today']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-secondary-subtle py-2">
                        <span class="text-secondary">This Week</span>
                        <span class="fw-semibold"><?= number_format($unique_counts['this_week']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-secondary-subtle py-2">
                        <span class="text-secondary">This Month</span>
                        <span class="fw-semibold"><?= number_format($unique_counts['this_month']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-secondary">All Time</span>
                        <span class="fw-semibold"><?= number_format($unique_counts['total']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 border-0 bg-dark-subtle">
                <div class="card-header border-bottom-0 text-secondary small text-uppercase fw-semibold py-2">
                    <i class="bi bi-stopwatch-fill me-1"></i> Page Load Time
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between border-bottom border-secondary-subtle py-2">
                        <span class="text-secondary">Average</span>
                        <span class="fw-semibold"><?= $load_time_stats['avg'] > 0 ? number_format($load_time_stats['avg']) . ' ms' : '—' ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-secondary-subtle py-2">
                        <span class="text-secondary">Fastest</span>
                        <span class="fw-semibold text-success"><?= $load_time_stats['min'] > 0 ? number_format($load_time_stats['min']) . ' ms' : '—' ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-secondary">Slowest</span>
                        <span class="fw-semibold text-danger"><?= $load_time_stats['max'] > 0 ? number_format($load_time_stats['max']) . ' ms' : '—' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100 border-0 bg-dark-subtle">
                <div class="card-header border-bottom-0 text-secondary small text-uppercase fw-semibold py-2">
                    <i class="bi bi-phone-fill me-1"></i> Device Types
                </div>
                <div class="card-body d-flex align-items-center justify-content-center gap-4 pt-0">
                    <?php if (!empty($device_breakdown)): ?>
                    <div class="dashboard__device-chart-wrap">
                        <canvas id="device-chart" width="120" height="120"></canvas>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <?php foreach ($device_breakdown as $d):
                            $pct = $totalHits > 0 ? round(($d['hits'] / $totalHits) * 100, 1) : 0;
                        ?>
                        <div class="d-flex align-items-center gap-2">
                            <span class="device-legend-dot rounded-circle"
                                  data-device="<?= esc($d['device_type']) ?>"></span>
                            <span class="text-capitalize"><?= esc($d['device_type']) ?></span>
                            <span class="text-secondary ms-auto ps-3"><?= number_format((int)$d['hits']) ?></span>
                            <span class="badge text-bg-secondary ms-1"><?= $pct ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-secondary mb-0">No data yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Hits over last 30 days chart ──────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 bg-dark-subtle">
                <div class="card-header border-bottom-0 d-flex align-items-center justify-content-between py-2">
                    <span class="text-secondary small text-uppercase fw-semibold">
                        <i class="bi bi-graph-up me-1"></i> Hits — Last 30 Days
                    </span>
                    <span class="text-secondary small"><?= number_format(array_sum(array_values($hits_by_day))) ?> total in period</span>
                </div>
                <div class="card-body pt-0">
                    <canvas id="hits-chart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Top domains + Top paths ───────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 bg-dark-subtle">
                <div class="card-header border-bottom-0 d-flex align-items-center justify-content-between py-2">
                    <span class="text-secondary small text-uppercase fw-semibold">
                        <i class="bi bi-globe2 me-1"></i> Top Domains
                    </span>
                    <a href="/admin/metrics/domains" class="btn btn-sm btn-outline-secondary py-0">
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($top_domains)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Domain</th>
                                    <th class="text-end">Hits</th>
                                    <th class="text-end pe-3">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_domains as $row):
                                    $pct = $totalHits > 0 ? round(($row['hits'] / $totalHits) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="/admin/metrics/domain/<?= urlencode($row['domain']) ?>"
                                           class="text-decoration-none">
                                            <?= esc($row['domain']) ?>
                                        </a>
                                    </td>
                                    <td class="text-end"><?= number_format((int)$row['hits']) ?></td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;min-width:50px">
                                                <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                                            </div>
                                            <span class="text-secondary small" style="min-width:3rem;text-align:right"><?= $pct ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-secondary p-3 mb-0">No data yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 bg-dark-subtle">
                <div class="card-header border-bottom-0 d-flex align-items-center justify-content-between py-2">
                    <span class="text-secondary small text-uppercase fw-semibold">
                        <i class="bi bi-signpost-2 me-1"></i> Top Paths
                    </span>
                    <a href="/admin/metrics" class="btn btn-sm btn-outline-secondary py-0">
                        Full log <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($top_paths)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Path</th>
                                    <th>Domain</th>
                                    <th class="text-end pe-3">Hits</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_paths as $row): ?>
                                <tr>
                                    <td class="ps-3 text-truncate" style="max-width:200px" title="<?= esc($row['path']) ?>">
                                        <span class="font-monospace small"><?= esc($row['path']) ?></span>
                                    </td>
                                    <td class="text-secondary small"><?= esc($row['domain'] ?? '') ?></td>
                                    <td class="text-end pe-3"><?= number_format((int)$row['hits']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-secondary p-3 mb-0">No data yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Latest hits ───────────────────────────────────────────────────── -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 bg-dark-subtle">
                <div class="card-header border-bottom-0 d-flex align-items-center justify-content-between py-2">
                    <span class="text-secondary small text-uppercase fw-semibold">
                        <i class="bi bi-clock-history me-1"></i> Latest Hits
                    </span>
                    <a href="/admin/metrics" class="btn btn-sm btn-outline-secondary py-0">
                        Full log <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($latest_hits)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Time</th>
                                    <th>Domain</th>
                                    <th>Path</th>
                                    <th>User</th>
                                    <th>Device</th>
                                    <th class="text-end pe-3">Load (ms)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latest_hits as $hit): ?>
                                <tr>
                                    <td class="ps-3 text-secondary small text-nowrap">
                                        <?= esc(date('d M H:i', strtotime($hit['created_at']))) ?>
                                    </td>
                                    <td>
                                        <a href="/admin/metrics/domain/<?= urlencode($hit['domain']) ?>"
                                           class="text-decoration-none small">
                                            <?= esc($hit['domain']) ?>
                                        </a>
                                    </td>
                                    <td class="text-truncate font-monospace small" style="max-width:200px"
                                        title="<?= esc($hit['path']) ?>">
                                        <?= esc($hit['path']) ?>
                                    </td>
                                    <td class="small text-secondary">
                                        <?= !empty($hit['username']) ? esc($hit['username']) : '<span class="opacity-50">—</span>' ?>
                                    </td>
                                    <td>
                                        <?php
                                        $deviceIcons = ['desktop' => 'bi-display', 'mobile' => 'bi-phone', 'tablet' => 'bi-tablet'];
                                        $icon = $deviceIcons[strtolower($hit['device_type'] ?? '')] ?? 'bi-question-circle';
                                        ?>
                                        <i class="bi <?= $icon ?> text-secondary" title="<?= esc($hit['device_type']) ?>"></i>
                                    </td>
                                    <td class="text-end pe-3 small">
                                        <?php if ($hit['load_time_ms'] !== null): ?>
                                        <span class="<?= $hit['load_time_ms'] > 2000 ? 'text-danger' : ($hit['load_time_ms'] > 1000 ? 'text-warning' : 'text-success') ?>">
                                            <?= number_format((int)$hit['load_time_ms']) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="opacity-50">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-secondary p-3 mb-0">No hits recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Embed chart data for JavaScript -->
<script id="dashboard-data" type="application/json">{
    "hitsLabels": <?= $chartLabels ?>,
    "hitsValues": <?= $chartValues ?>,
    "deviceLabels": <?= $deviceLabels ?>,
    "deviceValues": <?= $deviceValues ?>
}</script>

<?= $this->endSection() ?>