<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
    <ul class="pagination pagination-sm justify-content-center mb-0">

        <?php if ($pager->hasPrevious()): ?>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
                &laquo;&laquo;
            </a>
        </li>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>">
                &laquo;
            </a>
        </li>
        <?php else: ?>
        <li class="page-item disabled">
            <span class="page-link">&laquo;&laquo;</span>
        </li>
        <li class="page-item disabled">
            <span class="page-link">&laquo;</span>
        </li>
        <?php endif; ?>

        <?php foreach ($pager->links() as $link): ?>
        <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
        </li>
        <?php endforeach; ?>

        <?php if ($pager->hasNext()): ?>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>">
                &raquo;
            </a>
        </li>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
                &raquo;&raquo;
            </a>
        </li>
        <?php else: ?>
        <li class="page-item disabled">
            <span class="page-link">&raquo;</span>
        </li>
        <li class="page-item disabled">
            <span class="page-link">&raquo;&raquo;</span>
        </li>
        <?php endif; ?>

    </ul>
</nav>
