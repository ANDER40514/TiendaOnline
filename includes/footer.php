<footer id="footer"></footer>

<!-- JS global -->
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

<!-- JS específicos de la página -->
<?php
if (!empty($extraJS)) {
    foreach ($extraJS as $jsFile) {
        if (preg_match('/^(?:https?:)?\/\//i', $jsFile)) {
            echo '<script src="' . $jsFile . '"></script>' . PHP_EOL;
        } else {
            echo '<script src="' . BASE_URL . ltrim($jsFile, '/') . '"></script>' . PHP_EOL;
        }
    }
}
?>
</body>
</html>
