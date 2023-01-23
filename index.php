<?php require_once __DIR__.'/engine.php'; ?>

<?= component('comp.page', title: 'My PHP Page') ?>
    <?= slot('header') ?>
        <?= simple_component('comp.header', username: 'seb') ?>
    <?= end_slot('header') ?>
    <?= slot('footer') ?>
        <div>THIS IS THE FOOTER</div>
    <?= end_slot('footer') ?>
    <!-- This is the unnamed slot here: -->
    Main content is in the un-named slot.
<?= end_component('comp.page') ?>
