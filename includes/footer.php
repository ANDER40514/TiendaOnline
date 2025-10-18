<footer id="footer"></footer>

<!-- JS global -->
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

<!-- JS específicos de la página -->
<?php
if (!empty($extraJS)) {
    foreach ($extraJS as $jsFile) {
        echo '<script src="' . BASE_URL .  "assets/js/" . $jsFile . '"></script>' . PHP_EOL;
    }
}
?>
</body>
</html>
