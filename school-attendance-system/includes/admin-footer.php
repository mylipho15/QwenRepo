        </div>
    </main>

    <script src="../js/main.js"></script>
    <script>
        // Show mobile sidebar toggle on small screens
        window.addEventListener('resize', () => {
            const toggleBtn = document.querySelector('.sidebar-toggle');
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
            } else {
                toggleBtn.style.display = 'none';
            }
        });

        // Trigger resize on load
        window.dispatchEvent(new Event('resize'));
    </script>
</body>
</html>
