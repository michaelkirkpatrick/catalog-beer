<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// Load Version
require_once ROOT . '/config/version.php';

// HTML Head
$htmlHead = new htmlHead('Build Info');
echo $htmlHead->html;
?>
<body>
    <?php echo $nav->navbar(''); ?>
    <div class="cb-page cb-page--narrow">
        <h1>Build Info</h1>
        <table class="table">
            <tbody>
                <tr>
                    <th>Commit</th>
                    <td><?php echo h(VERSION_COMMIT); ?></td>
                </tr>
                <tr>
                    <th>Branch</th>
                    <td><?php echo h(VERSION_BRANCH); ?></td>
                </tr>
                <tr>
                    <th>Deployed</th>
                    <td><?php echo h(VERSION_TIMESTAMP); ?></td>
                </tr>
                <?php if(VERSION_DIRTY){ ?>
                <tr>
                    <th>Note</th>
                    <td>Deployed with uncommitted changes</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
