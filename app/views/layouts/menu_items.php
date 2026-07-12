<?php $horizontal = $horizontal ?? false; ?>
<ul class="<?= $horizontal ? 'tm-menu-horizontal-list' : 'tm-menu-list' ?>">
    <?php foreach ($menus as $item): ?>
        <li class="tm-menu-item <?= !empty($item['children']) ? 'has-children' : '' ?>">
            <a href="<?= e(url('/' . str_replace('_', '-', $item['module_key']))) ?>"
               class="tm-menu-link <?= active_menu($item['route']) ?>">
                <i class="bi <?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
                <?php if (!empty($item['children'])): ?><i class="bi bi-chevron-down ms-auto tm-caret"></i><?php endif; ?>
            </a>
            <?php if (!empty($item['children'])): ?>
                <ul class="tm-submenu">
                    <?php foreach ($item['children'] as $child): ?>
                        <li>
                            <a href="<?= e(url('/' . str_replace('_', '-', $child['module_key']))) ?>" class="tm-menu-link <?= active_menu($child['route']) ?>">
                                <i class="bi <?= e($child['icon']) ?>"></i><span><?= e($child['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
