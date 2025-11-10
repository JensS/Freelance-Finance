import './bootstrap';

// File upload functionality for bank statement modal
document.addEventListener('DOMContentLoaded', function() {
    // Handle file selection from the styled button
    const fileInput = document.getElementById('bank-statement-file');
    const hiddenInput = document.getElementById('bank-statement-hidden');
    
    if (fileInput && hiddenInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                // Transfer the file to the hidden Livewire input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(e.target.files[0]);
                hiddenInput.files = dataTransfer.files;
                
                // Trigger Livewire's file upload
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        
        // Handle drag and drop
        const dropZone = fileInput.closest('div.max-w-lg');
        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-indigo-400');
            });
            
            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('border-indigo-400');
            });
            
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-indigo-400');
                
                if (e.dataTransfer.files.length > 0) {
                    const file = e.dataTransfer.files[0];
                    
                    // Check if it's a PDF
                    if (file.type === 'application/pdf') {
                        // Transfer the file to the hidden Livewire input
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        hiddenInput.files = dataTransfer.files;
                        
                        // Trigger Livewire's file upload
                        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    } else {
                        alert('Bitte wählen Sie eine PDF-Datei aus.');
                    }
                }
            });
        }
    }
});