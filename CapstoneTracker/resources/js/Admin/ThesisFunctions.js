
// FOR FULL VIEW THESIS
function handleViewThesis(thesisId, title) {
    if (!thesisId) {
        Swal.fire({
            title: 'Error',
            text: 'Thesis ID not found.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Show loading state
    Swal.fire({
        title: 'Loading Thesis...',
        text: 'Please wait while we load the thesis file',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch the full thesis file
    fetchThesisFileForView(thesisId, title);
}


// FOR FULL VIEW THESIS
// Function to fetch the full thesis file (not just abstract)
async function fetchThesisFileForView(thesisId, title) {
    try {
        if (!thesisId || isNaN(thesisId)) {
            throw new Error('Invalid thesis ID');
        }

        console.log('Fetching thesis with ID:', thesisId);

        // Use the viewThesis endpoint with proper encoding
        const response = await fetch(`/CapstoneTracker/app/Controllers/ThesisController.php?action=viewThesis&id=${encodeURIComponent(thesisId)}`);
        
        console.log('Response status:', response.status);

        if (!response.ok) {
            // Try to get error message from response
            const errorText = await response.text();
            throw new Error(`Server returned ${response.status}: ${errorText}`);
        }
        
        // Check if response is PDF
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        if (!contentType || !contentType.includes('pdf')) {
            // If not PDF, it might be an error message
            const text = await response.text();
            console.error('Non-PDF response:', text);
            throw new Error('Thesis file is not available or not a valid PDF');
        }
        
        // Get the thesis as blob
        const blob = await response.blob();
        
        if (blob.size === 0) {
            throw new Error('Thesis file is empty');
        }
        
        // Create object URL for the blob
        const thesisBlobUrl = URL.createObjectURL(blob);
        
        // Close loading SweetAlert
        Swal.close();
        
        // Preview the full thesis PDF in the modal
        previewThesisPdf(thesisBlobUrl, title);
        
    } catch (error) {
        console.error('Error fetching thesis file:', error);
        Swal.close();
        Swal.fire({
            title: 'Error Loading Thesis',
            text: `Failed to load thesis: ${error.message}`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

function previewThesisPdf(url, title) {
    const pdfViewer = document.getElementById('pdf-viewer');
    const docViewerIframe = document.getElementById('doc-viewer-iframe');
    const unsupportedFile = document.getElementById('unsupported-file');
    const pdfFooterControls = document.getElementById('pdf-footer-controls');
    
    if (!pdfViewer) {
        console.error('PDF viewer element not found');
        return;
    }
    
    // Reset viewer states
    docViewerIframe.style.display = 'none';
    pdfViewer.style.display = 'none';
    unsupportedFile.style.display = 'none';
    pdfFooterControls.style.display = 'none';
    
    // Update modal title to indicate it's the full thesis
    const modalTitle = document.querySelector('.preview-modal .modal-title');
    if (modalTitle) {
        modalTitle.textContent = `${title} - Full Thesis (View Only)`;
    }
    
    // HIDE the download link for thesis files
    const downloadLink = document.getElementById('download-link');
    if (downloadLink) {
        downloadLink.style.display = 'none';
    }
    
    // Use the same PDF preview function
    previewPdf(url);
}

// Function to show thesis in the preview modal
function showThesisInModal(pdfUrl, title) {
    const modalTitle = document.querySelector('.preview-modal .modal-title');
    if (modalTitle) {
        modalTitle.textContent = `${title} - Full Thesis`;
    }
    
    // Reset viewer states
    const docViewerIframe = document.getElementById('doc-viewer-iframe');
    const pdfViewer = document.getElementById('pdf-viewer');
    const unsupportedFile = document.getElementById('unsupported-file');
    const pdfFooterControls = document.getElementById('pdf-footer-controls');
    
    docViewerIframe.style.display = 'none';
    pdfViewer.style.display = 'none';
    unsupportedFile.style.display = 'none';
    pdfFooterControls.style.display = 'none';
    
    // Update download link for full thesis
    const downloadLink = document.getElementById('download-link');
    downloadLink.href = pdfUrl;
    downloadLink.download = `${title.replace(/\s+/g, '_')}_full_thesis.pdf`;
    downloadLink.innerHTML = '<i class="fas fa-download"></i> Download Thesis';
    downloadLink.style.display = 'block';
    
    // Preview the full thesis PDF
    previewPdf(pdfUrl);
    
    // Show the modal
    const previewModal = document.getElementById('previewModal');
    previewModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}