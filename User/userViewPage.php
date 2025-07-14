<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/gradcap.png" type="image/x-icon">
    <title>ThesisComp</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/userViewPage.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="Line"></div>
    <div class="yellow"></div>
    <div class="top-nav" id="top-nav">
        <div class="searchBar">
            <input class="search" placeholder="Search">
            <i class="fi fi-rr-search"></i>
        </div>
        
        <button class="nav-button start" data-target="uploadContainer">Upload</button>
        <button class="nav-button" data-target="profileContainer">Profile</button>
        <button class="nav-button" data-target="notificationContainer">Notifications</button>
        <button class="nav-button logout">Logout</button>
    </div>

    <!-- Content Containers -->
    <div id="uploadContainer" class="content-container" style="display: flex;">
        <div>
            <p class="icon"><i class="fi fi-rr-upload"></i></p>
            <img src="../images/add.png" alt="Upload" class="upload-icon">
            <?php if (!empty($assigned_subjects)): ?>
                    <?php foreach (array_unique($assigned_subjects, SORT_REGULAR) as $subject): ?>
                        <div class="info-card">
                            <h3><?php echo htmlspecialchars($subject['subject_code']); ?></h3>
                            <p><?php echo htmlspecialchars($subject['subject_description']); ?> ( <?php echo htmlspecialchars($subject['Grade_Level']); ?>)</p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    
                        <h5>No Thesis Uploaded</h5>
                        
                    
                <?php endif; ?>
        </div>    
        
        

    </div>

    <div class="upload-box">
            <input type="text" placeholder="Title">
            <input type="text" placeholder="Description">
            <input type="text" placeholder="Subject">
            <input type="text" placeholder="Grade Level">
            <input type="text" placeholder="Date">
            <input type="text" placeholder="Time">
            <input type="text" placeholder="Location">
            <input type="file" id="fileInput" style="display: none;">
            <label for="fileInput" class="upload-label">
                <i class="fi fi-rr-upload"></i>
                <p>Upload</p>
            </label>
        </div>
        
    <div id="profileContainer" class="content-container">
        <!-- Profile content goes here -->
    </div>
    <div id="notificationContainer" class="content-container">
        <!-- Notifications content goes here -->
    </div>

    <script type="text/javascript" src="js/userViewPage.js"></script>
</body>
</html>