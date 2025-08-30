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
        <div class="search-container">
            <input class="search" placeholder="gwapa">
            <button class="search-icon-btn" type="submit">
                <img class="searchIcon" src="../images/search-interface-symbol.png">
            </button>
        </div>
        
        <p class="text">Sort By: </p>
        <select class="sort">
            <option>Title</option>
            <option>Author</option>
            <option>Date Published</option>
        </select>
    </div>

        

        <button id="navButtons" class="start">Profile</button>
        <button id="navButtons">Upload</button>  
        <button id="navButtons">Notifications</button>
        <button id="navButtons">Logout</button>
    </div>


    <!-- Content Containers -->

    <div id="profileContainer" class="content-container">
        <div class="mainProfile">
            <img class="profileIcon" src="../images/profile.png">
            <div>
                <h4>Juan Dela Cruz</h4>
                <p>juancruz@email.com</p>
            </div>
            <div>
                <h6>Recently Published:</h6>
                <p>How to be Gwapo Thesis</p>
            </div>

            <div>
                
            </div>
        </div>
<!--
     <div class="subProfile">
            <div class="personalProfile">
                <h5>Personal Information</h5>
                <label>Email: </label>
                <p>juancruz@email.com<?php echo htmlspecialchars($user_data['Email']); ?></p>
                <label>School ID:</label>
                <p>12345677<?php echo htmlspecialchars($user_data['Email']); ?></p>
            </div>

            <div class="passProfile">

                <label class="lbl">Current Password</label>
                <input class="profileInputs" type="text">
                
                <label class="lbl">New Password</label>
                <input class="profileInputs" type="text">
                
                <label class="lbl">Confirm New Password</label>
                <input class="profileInputs" type="text">
                <button class="changeButton">Change Password</button>
            </div>
        </div> -->
      
    </div>

    <div id="uploadContainer" class="content-container">
        <div>
            
            
                <img src="../images/add.png" onclick="dropzoneContainer()" alt="Upload" id="upload-icon" class="upload-icon">
                <div id="dropzone-container" class="dropzone-container">
                    <input type="file">
                        <form action="https://httpbin.org/post" method="post" enctype="multipart/form-data">
                            <input name="file" type="file" multiple>
                            <button type="submit">Upload</button>
                    </form>

                    
                </div>
            
            
            <?php if (!empty($published_thesis)): ?>
                    <?php foreach (array_unique($published_thesis, SORT_REGULAR) as $thesis): ?>
                        <div class="info-card">
                            <h3><?php echo htmlspecialchars($thesis['title']); ?></h3>
                            <p>Author: <?php echo htmlspecialchars($thesis['author_name']); ?></p>
                            <p>Published: <?php echo htmlspecialchars($thesis['date_published']); ?></p>                        
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
        
    
    <div id="notificationContainer" class="content-container">
        <p>ajshdkjasd</p>
    </div>

    <script type="text/javascript" src="js/userViewPage.js"></script>
</body>
</html>