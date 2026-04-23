
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo">
                    <span class="logo-icon">💕</span>
                    <span class="logo-text">HIM</span>
                </div>
                <p>Her Intelligent Mate — Your AI-powered period companion for emotional, physical, and mental wellness.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="cycle_tracker.php">Cycle Tracker</a>
                    <a href="chat.php">AI Chat</a>
                    <a href="wellness.php">Wellness Hub</a>
                <?php else: ?>
                    <a href="index.php#features">Features</a>
                    <a href="index.php#how-it-works">How It Works</a>
                    <a href="register.php">Get Started</a>
                <?php endif; ?>
            </div>
            <div class="footer-links">
                <h4>Support</h4>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Use</a>
                <a href="#">Contact</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> HIM - Her Intelligent Mate. Made with 💕 for women everywhere.</p>
        </div>
    </div>
</footer>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>

<!-- AOS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- Global JS -->
<script src="assets/js/app.js"></script>

<?php if (isset($extraJS)): ?>
    <?php foreach ($extraJS as $js): ?>
        <script src="<?= $js ?>?v=<?= time() ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
