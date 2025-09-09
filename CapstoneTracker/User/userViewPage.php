<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management Dashboard</title>
    <link rel="icon" href="../Images/gradcap.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="userViewPage.css">
    
    </head>
<body>
<div class="dashboard-container">
    <section class="sidebar">
        <div class="logo">
             <i class="fa fa-graduation-cap icon" aria-hidden="true"></i>
        </div>

        <nav>
            <ul class="menu-options">
                <li class="selected"> <i class="fa fa-th-large icon" aria-hidden="true"></i> </li>
                <li> <i class="fa fa-calendar icon" aria-hidden="true"></i> </li>
                <li id="profileSidebarIcon"> <i class="fa fa-user-o icon" aria-hidden="true"></i> </li>
                <li> <i class="fa fa-wrench icon" aria-hidden="true"></i> </li>
            </ul>
        </nav>

        <div class="more-options">
            <i class="fa fa-ellipsis-h icon" aria-hidden="true"></i>
        </div>
    </section>

    <section class="main-content">
        <header class="header" id="header">
            <div class="title">Thesis Repository</div>
            <div class="menu">
                <button> All </button>
                <button class="selected"> Recent </button>
                <button> Pending </button>
                <button> Approved </button>
                <button> Rejected </button>
            </div>

            
        </header>

        <section class="app-content">
           <!-- Profile Container (initially hidden) -->
    <div class="profile-container" id="profileContainer">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="Images/profile.png" alt="Profile" class="profile-image">
                    <div class="online-status"></div>
                </div>
                <div class="profile-info">
                    <h2 class="profile-name">John Doe</h2>
                    <p class="profile-title">BSIT 2IT</p>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value">15</div>
                            <div class="stat-label">Thesis</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">10</div>
                            <div class="stat-label">Approved</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">5</div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fa fa-user-o icon" aria-hidden="true"></i>
                        Personal Information
                    </h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value">John Doe</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value">johndoe@example.com</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value">09091452546</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Location:</span>
                            <span class="info-value">Tagum City</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Department:</span>
                            <span class="info-value">BSIT</span>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fa fa-cog icon" aria-hidden="true"></i>
                        Account Settings
                    </h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Member Since:</span>
                            <span class="info-value">January 15, 2022</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Login:</span>
                            <span class="info-value">Today, 10:30 AM</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <span class="info-value status-active">Active</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Role:</span>
                            <span class="info-value">Project Manager</span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                            Edit Profile
                        </button>
                        <button class="btn btn-secondary" id="logoutHeaderIcon">
                            <i class="fa fa-sign-out" aria-hidden="true"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

            <div class="app-content-header">
                <div class="searchbox">
                    <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                    <input type="text" name="search" placeholder="Search thesis" class="search-text" id="searchInput">
                </div>

                <div class="app-list-options">
                    <div class="select" id="filterDropdown">
                        <div class="selected">
                            <span>All</span>
                            <i class="fa fa-filter" style="margin-left: 3vw; position: absolute; right: 2.5vw;" aria-hidden="true"></i>
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                            </svg>
                        </div>
                        <div class="options">
                            <div data-value="all">All</div>
                            <div data-value="important">Important</div>
                            <div data-value="enhancement">Enhancement</div>
                            <div data-value="announcement">Announcement</div>
                            <div data-value="news">News</div>
                            <div data-value="discussion">Discussion</div>
                            <div data-value="interesting">Interesting</div>
                            <div data-value="cannot-fix">Cannot Fix</div>
                            <div data-value="off-topic">Off Topic</div>
                            <div data-value="change-declined">Change Declined</div>
                        </div>
                    </div>

                    <div class="display-group">
                        <div class="icon" id="listViewIcon"> <i class="fa fa-bars" aria-hidden="true"></i> </div>
                        <div class="icon selected" id="gridViewIcon"> <i class="fa fa-th" aria-hidden="true"></i> </div>
                    </div>
                </div>
            </div>

            <div class="projects-container">
                <ul class="projects" id="projectsGrid">
                    <li class="project-item" data-tags="important enhancement">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?brand" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Sports Interactive </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> sportsinteractive.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Web resource which contains all about transfer in the world of sports.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="94"></p>
                            <progress max="100" value="94" data-value="94"> 94% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days danger">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 2 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?person" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?woman" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="announcement news">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?logo" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Homechoice </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> homchoice.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Platform for home decoration ideas and interior design inspiration.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="64"></p>
                            <progress max="100" value="64" data-value="64"> 64% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days warning">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 4 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?man" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?user" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="discussion interesting">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?estate" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Big Money Real Estate </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> bigmoneyrealestate.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Luxury real estate platform featuring high-end properties worldwide.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="59"></p>
                            <progress max="100" value="59" data-value="59"> 59% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days warning">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 5 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?person" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?profile" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="cannot-fix off-topic">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?media" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Springfield Media </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> springfieldmedia.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Digital media company specializing in content creation and distribution.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="94"></p>
                            <progress max="100" value="94" data-value="94"> 94% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 7 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?man" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?woman" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="enhancement change-declined">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?logistics" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Regular Logistics </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> regularlogistics.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Supply chain management solution for small and medium businesses.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="44"></p>
                            <progress max="100" value="44" data-value="44"> 44% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 9 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?user" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?person" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="important news">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?agency" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Foursquare Agency </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> foursquareagency.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Full-service digital marketing agency focused on growth strategies.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="39"></p>
                            <progress max="100" value="39" data-value="39"> 39% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days danger">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 11 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?profile" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?man" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="discussion interesting">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?studio" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Piece Studio </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> piecestudio.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Creative design studio specializing in UI/UX and brand identity.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="34"></p>
                            <progress max="100" value="34" data-value="34" class="low"> 34% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 12 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?woman" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?user" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="announcement enhancement">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?foundation" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Legacy Foundation </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> legacyfoundation.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Non-profit organization focused on education and community development.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="32"></p>
                            <progress max="100" value="32" data-value="32"> 32% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 12 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?person" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?profile" alt="User" />
                            </div>
                        </div>
                    </li>
                </ul>

         <!--   <div class="uploadContainer" id="uploadContainer">

            </div>  -->


            </div>

            <p class="notFound" id="notFound">No Results Found.</p>

        </section>
    </section>
</div>

<div class="fab-icon"> + </div>

<!-- Upload Thesis Modal -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Upload Thesis</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="thesis-form">
                <div class="thesis-form-group">
                    <h3>Thesis Title *</h3>
                    <input type="text" name="thesistitle" placeholder="Enter thesis title" class="thesis-form-input" id="thesisTitle" required>
                </div>
                
                <div class="thesis-form-group">
                    <h3>Author/s</h3>
                    <input type="text" name="thesisauthor" placeholder="Enter author name(s)" class="thesis-form-input" id="thesisAuthor">
                </div>
            </div>
            
            <div class="upload-area" id="dropArea">
                <div class="upload-icon">
                    <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                </div>
                <div class="upload-text">
                    <h3>Drag & Drop your files here</h3>
                    <p>Supported files: docx, pdf, zip</p>
                </div>
                <div class="browse-btn">Browse files</div>
                <input type="file" class="file-input" id="fileInput" multiple accept=".docx,.pdf,.zip">
            </div>
            
            <div class="file-previews">
                <h4>Selected Files</h4>
                <div class="file-list-grid" id="fileList">
                    <div class="empty-state">
                        <i class="far fa-folder-open"></i>
                        <p>No files selected</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-cancel">Cancel</button>
            <button class="btn btn-upload" id="uploadBtn" disabled>Upload Thesis</button>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal-overlay preview-modal" id="previewModal">
    <div class="modal preview-modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Document Preview</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="document-viewer">
                <iframe id="doc-viewer-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                <div id="pdf-viewer" style="display: none; width: 100%; height: 500px;"></div>
                <div id="unsupported-file" style="display: none; text-align: center; padding: 50px;">
                    <i class="fa fa-exclamation-triangle" style="font-size: 48px; color: #ff9800;"></i>
                    <h3>Preview not available</h3>
                    <p>This file type cannot be previewed in the browser.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-cancel" id="closePreview">Close</button>
            <a id="download-link" class="btn btn-primary" download>Download</a>
        </div>
    </div>
</div>


</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script type="text/javascript" src="userViewPage.js"></script>
</html>