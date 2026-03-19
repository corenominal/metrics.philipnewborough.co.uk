<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom border-1 mb-4 pb-3 d-flex align-items-center gap-3">
        <a href="/admin" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0"><i class="bi bi-globe2 me-2 text-secondary"></i>Domains</h2>
    </div>

    <div class="card border-0 bg-dark-subtle">
        <div class="card-body p-0">
            <?php if (!empty($domains)): ?>

            <?php
            $totalHits = array_sum(array_column($domains, 'hits'));
            ?>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Domain</th>
                            <th class="text-end">Hits</th>
                            <th class="text-end pe-3">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($domains as $i => $row):
                            $pct = $totalHits > 0 ? round(($row['hits'] / $totalHits) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td class="ps-3 text-secondary small"><?= $i + 1 ?></td>
                            <td>
                                <a href="/admin/metrics/domain/<?= urlencode($row['domain']) ?>"
                                   class="text-decoration-none fw-semibold">
                                    <?= esc($row['domain']) ?>
                                </a>
                            </td>
                            <td class="text-end fw-semibold"><?= number_format((int)$row['hits']) ?></td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div class="progress flex-grow-1" style="height:8px;min-width:80px">
                                        <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <span class="text-secondary small" style="min-width:3.5rem;text-align:right">
                                        <?= $pct ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-secondary p-4 mb-0 text-center">No domains recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
