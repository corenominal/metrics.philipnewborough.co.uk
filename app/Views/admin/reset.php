<?= $this->extend('templates/dashboard') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom border-1 mb-4 pb-3 d-flex align-items-center gap-3">
        <h2 class="mb-0"><i class="bi bi-trash3-fill me-2 text-secondary"></i>Reset Stats</h2>
    </div>

    <div id="reset-alerts"></div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card border-danger bg-dark-subtle">
                <div class="card-header border-danger d-flex align-items-center gap-2 text-danger fw-semibold">
                    <i class="bi bi-exclamation-triangle-fill"></i> Danger Zone
                </div>
                <div class="card-body">
                    <h5 class="card-title text-danger">Clear All Stats</h5>
                    <p class="card-text text-secondary mb-1">
                        This will permanently delete <strong class="text-white">all recorded metrics</strong>,
                        including every hit, domain, path, and visitor record.
                    </p>
                    <p class="card-text mb-4">
                        <strong class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i>This action cannot be undone.</strong>
                    </p>
                    <button id="open-reset-modal" class="btn btn-danger" type="button">
                        <i class="bi bi-trash3-fill me-1"></i> Reset All Stats
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Reset confirmation modal -->
<div class="modal fade" id="reset-modal" tabindex="-1" aria-labelledby="reset-modal-label" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger" id="reset-modal-label">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Confirm Reset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary">
                    You are about to permanently delete <strong class="text-white">all stats</strong>.
                    This action is <strong class="text-danger">irreversible</strong> and cannot be undone.
                </p>
                <p class="mb-2 text-secondary">
                    To confirm, type <strong class="text-white font-monospace">RESET ALL DATA</strong> in the field below:
                </p>
                <input id="reset-phrase" type="text" class="form-control" placeholder="RESET ALL DATA" autocomplete="off" spellcheck="false">
            </div>
            <div class="modal-footer border-danger">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirm-reset" type="button" class="btn btn-danger" disabled>
                    <i class="bi bi-trash3-fill me-1"></i> Confirm Reset
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
