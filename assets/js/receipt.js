/**
 * Azeu Water Station - Receipt JavaScript
 * QR code generation, PDF download, image download
 */

// Initialize Receipt Page
function initReceipt() {
    generateQRCode();
    initDownloadButtons();
}

// Generate QR Code
function generateQRCode() {
    const qrContainer = document.getElementById('qr-code');
    if (!qrContainer) return;
    
    // Get current page URL
    const receiptURL = window.location.href;
    
    // Check if QRCode library is loaded
    if (typeof QRCode !== 'undefined') {
        new QRCode(qrContainer, {
            text: receiptURL,
            width: 150,
            height: 150,
            colorDark: '#1565C0',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        console.error('QRCode library not loaded');
    }
}

// Initialize Download Buttons
function initDownloadButtons() {
    const downloadPDFBtn = document.getElementById('download-pdf');
    const downloadImageBtn = document.getElementById('download-image');
    
    if (downloadPDFBtn) {
        downloadPDFBtn.addEventListener('click', downloadAsPDF);
    }
    
    if (downloadImageBtn) {
        downloadImageBtn.addEventListener('click', downloadAsImage);
    }
}

// Download as PDF
async function downloadAsPDF() {
    const receiptContainer = document.querySelector('.receipt-container');
    if (!receiptContainer) return;
    
    // Check if html2pdf is loaded
    if (typeof html2pdf === 'undefined') {
        alert('PDF library not loaded. Please refresh the page.');
        return;
    }
    
    showLoading();
    
    try {
        // Get order ID for filename
        const orderIdElement = document.querySelector('[data-order-id]');
        const orderId = orderIdElement ? orderIdElement.getAttribute('data-order-id') : 'receipt';
        
        const opt = {
            margin: 0.5,
            filename: `receipt-${orderId}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        
        await html2pdf().from(receiptContainer).set(opt).save();
        
        showToast('Receipt downloaded as PDF!', 'success');
    } catch (error) {
        console.error('PDF download error:', error);
        showToast('Failed to download PDF', 'error');
    } finally {
        hideLoading();
    }
}

// Download as Image
async function downloadAsImage() {
    const receiptContainer = document.querySelector('.receipt-container');
    if (!receiptContainer) return;
    
    // Check if html2canvas is loaded
    if (typeof html2canvas === 'undefined') {
        alert('Image library not loaded. Please refresh the page.');
        return;
    }
    
    showLoading();
    
    try {
        // Get order ID for filename
        const orderIdElement = document.querySelector('[data-order-id]');
        const orderId = orderIdElement ? orderIdElement.getAttribute('data-order-id') : 'receipt';
        
        const canvas = await html2canvas(receiptContainer, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff'
        });
        
        // Convert to blob and download
        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `receipt-${orderId}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showToast('Receipt downloaded as image!', 'success');
            hideLoading();
        });
    } catch (error) {
        console.error('Image download error:', error);
        showToast('Failed to download image', 'error');
        hideLoading();
    }
}

// Print Receipt
function printReceipt() {
    window.print();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initReceipt();
});
