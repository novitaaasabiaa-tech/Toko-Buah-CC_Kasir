<?php require_once __DIR__ . '/../config/config.php'; ?>
        <p class="note">CC KASIR • <?= MITRA_NAME ?> • Project Mandiri</p>
    </main>
</div>
<script src="<?= BASE_URL ?>assets/js/app.js"></script>
<?php foreach (($extraJs ?? []) as $berkasJs): ?>
<script src="<?= BASE_URL ?>assets/js/<?= $berkasJs ?>"></script>
<?php endforeach; ?>
</body>
</html>
