<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom border-1 mb-4 pb-3 d-flex align-items-center gap-3">
        <a href="/admin" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0"><i class="bi bi-list-ul me-2 text-secondary"></i>Hit Log</h2>
    </div>

    <!-- Filters -->
    <div class="card border-0 bg-dark-subtle mb-4">
        <div class="card-body py-3">
            <form method="get" action="/admin/metrics" class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="filter-domain" class="form-label small text-secondary mb-1">Domain</label>
                    <select id="filter-domain" name="domain" class="form-select form-select-sm">
                        <option value="">All Domains</option>
                        <?php foreach ($domains as $d): ?>
                        <option value="<?= esc($d['domain']) ?>"
                            <?= ($filters['domain'] === $d['domain']) ? 'selected' : '' ?>>
                            <?= esc($d['domain']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="filter-device" class="form-label small text-secondary mb-1">Device</label>
                    <select id="filter-device" name="device_type" class="form-select form-select-sm">
                        <option value="">All Devices</option>
                        <?php foreach ($device_types as $dt): ?>
                        <option value="<?= esc($dt['device_type']) ?>"
                            <?= ($filters['deviceType'] === $dt['device_type']) ? 'selected' : '' ?>>
                            <?= esc(ucfirst($dt['device_type'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="filter-date-from" class="form-label small text-secondary mb-1">From</label>
                    <input type="date" id="filter-date-from" name="date_from"
                           class="form-control form-control-sm"
                           value="<?= esc($filters['dateFrom']) ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="filter-date-to" class="form-label small text-secondary mb-1">To</label>
                    <input type="date" id="filter-date-to" name="date_to"
                           class="form-control form-control-sm"
                           value="<?= esc($filters['dateTo']) ?>">
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <?php if (!empty($filters['domain']) || !empty($filters['deviceType']) || !empty($filters['dateFrom']) || !empty($filters['dateTo'])): ?>
                    <a href="/admin/metrics" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Results table -->
    <div class="card border-0 bg-dark-subtle">
        <div class="card-body p-0">
            <?php if (!empty($hits)): ?>
            <div class="d-flex justify-content-end p-3">
                <button type="button" class="btn btn-sm btn-outline-danger" id="delete-selected-btn" data-url="/admin/metrics/delete" disabled>
                    <i class="bi bi-trash me-1"></i> Delete Selected
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:1px" class="ps-3 text-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all-hits">
                                </div>
                            </th>
                            <th class="ps-3">#</th>
                            <th>Time</th>
                            <th>Domain</th>
                            <th>Path</th>
                            <th>User</th>
                            <th>Device</th>
                            <th>IP</th>
                            <th class="text-end pe-3">Load (ms)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hits as $hit): ?>
                        <tr data-hit-id="<?= $hit['id'] ?>">
                            <td class="ps-3 text-center small">
                                <div class="form-check">
                                    <input class="form-check-input hit-select" type="checkbox" value="<?= $hit['id'] ?>" id="hit-<?= $hit['id'] ?>">
                                </div>
                            </td>
                            <td class="ps-3 text-secondary small"><?= $hit['id'] ?></td>
                            <td class="text-secondary small text-nowrap">
                                <?= esc(date('d M Y H:i', strtotime($hit['created_at']))) ?>
                            </td>
                            <td>
                                <a href="/admin/metrics/domain/<?= urlencode($hit['domain']) ?>"
                                   class="text-decoration-none small">
                                    <?= esc($hit['domain']) ?>
                                </a>
                            </td>
                            <td class="text-truncate font-monospace small" style="max-width:240px"
                                title="<?= esc($hit['path']) ?>">
                                <?= esc($hit['path']) ?>
                            </td>
                            <td class="small text-secondary">
                                <?= !empty($hit['username']) ? esc($hit['username']) : '<span class="opacity-50">—</span>' ?>
                            </td>
                            <td class="small">
                                <?php
                                $deviceIcons = ['desktop' => 'bi-display', 'mobile' => 'bi-phone', 'tablet' => 'bi-tablet'];
                                $icon = $deviceIcons[strtolower($hit['device_type'] ?? '')] ?? 'bi-question-circle';
                                ?>
                                <i class="bi <?= $icon ?> text-secondary me-1"
                                   title="<?= esc($hit['device_type']) ?>"></i>
                                <span class="text-secondary text-capitalize"><?= esc($hit['device_type']) ?></span>
                            </td>
                            <td class="small text-secondary font-monospace"><?= esc($hit['anonymized_ip']) ?></td>
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

            <!-- Pagination -->
            <?php if ($pager): ?>
            <div class="d-flex justify-content-center py-3">
                <?= $pager->links('default', 'bootstrap_5_full') ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <p class="text-secondary p-4 mb-0 text-center">No hits found matching your filters.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Delete selected confirmation modal -->
<div class="modal fade" id="delete-selected-modal" tabindex="-1" aria-labelledby="delete-selected-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="delete-selected-modal-label">
                    <i class="bi bi-trash me-2 text-danger"></i>Delete Selected Records
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to permanently delete <strong id="delete-selected-count">0</strong> selected hit record(s).</p>
                <p class="mb-0 text-danger-emphasis">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="delete-selected-confirm-btn">
                    <i class="bi bi-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
