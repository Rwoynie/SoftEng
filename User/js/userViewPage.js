
    /* Navigation Buttons */
document.querySelectorAll('#navButtons').forEach(button => {
    button.addEventListener('click', function() {
        const profileContainer = document.getElementById("profileContainer");
        const uploadContainer = document.getElementById("uploadContainer");
      
        const notificationContainer = document.getElementById("notificationContainer");
       

        profileContainer.style.display = "none";
        uploadContainer.style.display = "none";
        
        notificationContainer.style.display = "none";
       
        document.querySelectorAll('[id="navButtons"]').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('start');
        });

        this.classList.add('active');

        if (this.textContent.trim() === "Profile") {
            profileContainer.style.display = "flex";
        } else if (this.textContent.trim() === "Upload") {
            uploadContainer.style.display = "flex";
        
        } else if (this.textContent.trim() === "Notifications") {
            notificationContainer.style.display = "flex";
        } else if (this.textContent.trim() === "Logout") {
            window.location.href = "indexLogin.php";
        }
    });
});
    
  

document.querySelector('#uploadButton').addEventListener('click', function() {
    document.querySelector('.upload-box').style.display = 'flex';
});

document.querySelector('.upload-box').addEventListener('click', function() {
    document.querySelector('.upload-box').style.display = 'none';
});


function upload() {
    const uploadContainer = document.getElementById("uploadContainer");
    const profile = document.getElementById("Profile_Photo");
    uploadContainer.classList.add("show");
    profile.classList.add("remove");
}


function editProfile() {
    const profileInfo = document.getElementById("profileInfo");
    profileInfo.style.display = "flex";
}

function closeUpload() {
    const uploadContainer = document.getElementById("uploadContainer");
    const profile = document.getElementById("Profile_Photo");
    if (uploadContainer.classList.contains("show")) {
        uploadContainer.classList.remove("show");
        profile.classList.remove("remove");
    }
}


//cropping
const fileUpload = document.getElementById('file_Upload');
const image = document.getElementById('image');
const cropButton = document.getElementById('crop-btn');
const canvas = document.getElementById('cropped-image');
const ctx = canvas.getContext('2d');
const profilePhoto = document.getElementById('Profile_Photo');
const cropArea = document.getElementById('crop-area');

const selectionBox = document.createElement('div');
selectionBox.id = 'selection-box';
cropArea.appendChild(selectionBox);



const createResizeHandle = (position) => {
    const handle = document.createElement('div');
    handle.className = `resize-handle ${position}`;
    selectionBox.appendChild(handle);
    return handle;
};

// Create all four resize handles
const topLeftHandle = createResizeHandle('top-left');
const topRightHandle = createResizeHandle('top-right');
const bottomLeftHandle = createResizeHandle('bottom-left');
const bottomRightHandle = createResizeHandle('bottom-right');

let isDragging = false;
let isResizing = false;
let activeHandle = null;
let startX, startY, startWidth, startHeight, startLeft, startTop;

// Initialize selection box
function initSelectionBox() {
    const imgWidth = image.clientWidth;
    const imgHeight = image.clientHeight;
    const boxSize = Math.min(imgWidth, imgHeight) * 0.6; // 60% of smaller dimension
    
    selectionBox.style.display = 'block';
    selectionBox.style.width = `${boxSize}px`;
    selectionBox.style.height = `${boxSize}px`;
    selectionBox.style.left = `${(imgWidth - boxSize) / 2}px`;
    selectionBox.style.top = `${(imgHeight - boxSize) / 2}px`;
}

// Get client coordinates from event (works for both mouse and touch)
function getClientCoords(e) {
    if (e.touches && e.touches.length > 0) {
        return {
            x: e.touches[0].clientX,
            y: e.touches[0].clientY
        };
    }
    return {
        x: e.clientX,
        y: e.clientY
    };
}

function handleMove(e) {
    if (!isDragging && !isResizing) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const coords = getClientCoords(e);
    if (!coords.x || !coords.y) return;
    
    const imageRect = image.getBoundingClientRect();
    const cropAreaRect = cropArea.getBoundingClientRect();
    
    // Convert to relative coordinates within the crop area
    const relX = coords.x - cropAreaRect.left;
    const relY = coords.y - cropAreaRect.top;
    
    if (isDragging) {
        // Calculate new position
        let newLeft = relX - startX;
        let newTop = relY - startY;
        
        // Constrain to image bounds
        newLeft = Math.max(0, Math.min(imageRect.width - parseInt(selectionBox.style.width), newLeft));
        newTop = Math.max(0, Math.min(imageRect.height - parseInt(selectionBox.style.height), newTop));
        
        selectionBox.style.left = `${newLeft}px`;
        selectionBox.style.top = `${newTop}px`;
    } else if (isResizing && activeHandle) {
        // Calculate size changes
        const deltaX = relX - startX;
        const deltaY = relY - startY;
        
        let newWidth = startWidth;
        let newHeight = startHeight;
        let newLeft = startLeft;
        let newTop = startTop;
        
        const minSize = 50; // Minimum size in pixels
        
        // Handle different resize directions
        if (activeHandle.classList.contains('top-left')) {
            newWidth = Math.max(minSize, startWidth - deltaX);
            newHeight = Math.max(minSize, startHeight - deltaY);
            newLeft = startLeft + (startWidth - newWidth);
            newTop = startTop + (startHeight - newHeight);
        } else if (activeHandle.classList.contains('top-right')) {
            newWidth = Math.max(minSize, startWidth + deltaX);
            newHeight = Math.max(minSize, startHeight - deltaY);
            newTop = startTop + (startHeight - newHeight);
        } else if (activeHandle.classList.contains('bottom-left')) {
            newWidth = Math.max(minSize, startWidth - deltaX);
            newHeight = Math.max(minSize, startHeight + deltaY);
            newLeft = startLeft + (startWidth - newWidth);
        } else if (activeHandle.classList.contains('bottom-right')) {
            newWidth = Math.max(minSize, startWidth + deltaX);
            newHeight = Math.max(minSize, startHeight + deltaY);
        }
        
        // Constrain to image bounds
        if (newLeft < 0) {
            newWidth += newLeft;
            newLeft = 0;
        }
        if (newTop < 0) {
            newHeight += newTop;
            newTop = 0;
        }
        if (newLeft + newWidth > imageRect.width) {
            newWidth = imageRect.width - newLeft;
        }
        if (newTop + newHeight > imageRect.height) {
            newHeight = imageRect.height - newTop;
        }
        
        // Apply changes
        selectionBox.style.width = `${newWidth}px`;
        selectionBox.style.height = `${newHeight}px`;
        selectionBox.style.left = `${newLeft}px`;
        selectionBox.style.top = `${newTop}px`;
    }
}

// Handle start events (both mouse and touch)
function handleStart(e) {
    // Ignore if not a resize handle or the selection box
    if (!e.target.classList.contains('resize-handle') && e.target !== selectionBox) {
        return;
    }
    
    e.preventDefault();
    e.stopPropagation();
    
    const coords = getClientCoords(e);
    if (!coords.x || !coords.y) return;
    
    const cropAreaRect = cropArea.getBoundingClientRect();
    const relX = coords.x - cropAreaRect.left;
    const relY = coords.y - cropAreaRect.top;
    
    if (e.target.classList.contains('resize-handle')) {
        isResizing = true;
        activeHandle = e.target;
        startX = relX;
        startY = relY;
        startWidth = parseInt(selectionBox.style.width, 10);
        startHeight = parseInt(selectionBox.style.height, 10);
        startLeft = parseInt(selectionBox.style.left, 10);
        startTop = parseInt(selectionBox.style.top, 10);
    } else {
        isDragging = true;
        startX = relX - parseInt(selectionBox.style.left, 10);
        startY = relY - parseInt(selectionBox.style.top, 10);
    }
}

// Handle end events (both mouse and touch)
function handleEnd() {
    isDragging = false;
    isResizing = false;
    activeHandle = null;
}

// Set up event listeners
function setupEventListeners() {
    // Mouse events
    selectionBox.addEventListener('mousedown', handleStart);
    document.addEventListener('mousemove', handleMove);
    document.addEventListener('mouseup', handleEnd);
    
    // Touch events
    selectionBox.addEventListener('touchstart', handleStart, { passive: false });
    document.addEventListener('touchmove', handleMove, { passive: false });
    document.addEventListener('touchend', handleEnd);
}

// Initialize when image loads
fileUpload.addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            image.src = e.target.result;
            image.onload = function() {
                image.style.display = 'block';
                cropButton.classList.add("show");
                initSelectionBox();
                setupEventListeners();
            };
        };
        reader.readAsDataURL(file);
    }
});


// Cropping Function
cropButton.addEventListener('click', function() {
    const scaleX = image.naturalWidth / image.clientWidth;
    const scaleY = image.naturalHeight / image.clientHeight;

    const imageRect = image.getBoundingClientRect();
    const selectionRect = selectionBox.getBoundingClientRect();

    const cropX = (selectionRect.left - imageRect.left) * scaleX;
    const cropY = (selectionRect.top - imageRect.top) * scaleY;
    const cropSize = parseInt(selectionBox.style.width) * scaleX;

    canvas.width = cropSize;
    canvas.height = cropSize;
    ctx.drawImage(image, cropX, cropY, cropSize, cropSize, 0, 0, cropSize, cropSize);

    const imageDataUrl = canvas.toDataURL('image/jpeg', 0.8);
    profilePhoto.src = imageDataUrl;
    
    uploadImageToServer(imageDataUrl);

    selectionBox.style.display = 'none';
    image.src = '';
    image.style.display = 'none';
    cropButton.classList.remove("show");
    fileUpload.value = '';
    uploadContainer.classList.remove("show");
    
});

function uploadImageToServer(imageData) {
    Swal.fire({
        title: 'Uploading...',
        text: 'Please wait while we upload your profile picture',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('Profile_Controller.php', {
        method: 'POST',
        body: JSON.stringify({ image: imageData }),
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error(`Invalid response: ${text.substring(0, 100)}`);
            });
        }
        return response.json();
    })
    .then(data => {
        Swal.close();
        if (data && data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message || 'Profile picture updated successfully',
                icon: 'success'
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data?.message || 'Failed to update profile picture',
                icon: 'error'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: 'Error occurred: ' + error.message,
            icon: 'error'
        });
        console.error('Upload error:', error);
    });
}