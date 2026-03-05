    </div> <!-- .app-wrapper -->
    
    <!-- Global JavaScript -->
    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/components.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js (for analytics) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    
    <!-- html2pdf.js (for receipt PDF) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- html2canvas (for receipt image) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <!-- QRCode.js (for receipt QR) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <!-- Sortable.js (for rider delivery priority) -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($page_js)): ?>
        <script src="js/<?php echo $page_js; ?>?v=<?php echo time(); ?>"></script>
    <?php endif; ?>
</body>
</html>
