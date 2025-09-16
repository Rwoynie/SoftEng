<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Project Management Dashboard</title>
    <link rel="icon" href="../Images/gradcap.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="faculty.css">
    
</head>
<body>
<div class="dashboard-container">
    <section class="sidebar">
        <div class="logo">
             <i class="fa fa-graduation-cap icon" aria-hidden="true"></i>
        </div>

        <nav>
            <ul class="menu-options">
                <li class="selected" id="dashboardIcon"> <i class="fa fa-th-large icon" aria-hidden="true"></i> </li>
                <li id="approvalIcon"> <i class="fa fa-clipboard-check icon" aria-hidden="true"></i> </li>
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
                <button class="selected" id="allButton"> All </button>
                <button id="recentButton"> Recent </button>
                <button id="pendingButton"> Pending </button>
                <button id="approvedButton"> Approved </button>
                <button id="rejectedButton"> Rejected </button>
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
                           <h2 class="profile-name">Jane Doe</h2>
                           <p class="profile-title">BSIT Faculty</p>
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
                                   <span class="info-value">janedoe@example.com</span>
                               </div>
                               <div class="info-item">
                                   <span class="info-label">Phone:</span>
                                   <span class="info-value">09666949328</span>
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

           <!-- Approval Container -->
            <div class="approval-container" id="approvalContainer">
                <div class="approval-content">
                    <div class="approval-header">
                        <h2><i class="fa fa-clipboard-check"></i> Thesis Approval Queue</h2>
                        <p>Review and approve pending thesis submissions</p>
                    </div>

                    <div class="approval-stats">
                        <div class="stat-card">
                            <div class="stat-icon pending">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <div class="stat-info">
                                <h3>5</h3>
                                <p>Pending Reviews</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon approved">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3>12</h3>
                                <p>Approved This Month</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon revision">
                                <i class="fa fa-edit"></i>
                            </div>
                            <div class="stat-info">
                                <h3>3</h3>
                                <p>Needs Revision</p>
                            </div>
                        </div>
                    </div>

                    <!-- Updated Approval Search and Filter (Matching Dashboard) -->
                    <div class="approval-search-filter">
                        <div class="approval-searchbox">
                            <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                            <input type="text" name="search" placeholder="Search thesis..." class="search-text" id="approvalSearchInput">
                        </div>

                        <div class="approval-list-options">
                            <div class="select" id="approvalFilterDropdown">
                                <div class="selected">
                                    <span>All Pending</span>
                                    <i class="fa fa-filter" style="margin-left: 3vw; position: absolute; right: 2.5vw;" aria-hidden="true"></i>
                                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                        <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                                    </svg>
                                </div>
                                <div class="options">
                                    <div data-value="all">All Pending</div>
                                    <div data-value="today">Today</div>
                                    <div data-value="week">This Week</div>
                                    <div data-value="month">This Month</div>
                                </div>
                            </div>

                            <div class="display-group">
                                <div class="icon" id="approvalListViewIcon"> <i class="fa fa-bars" aria-hidden="true"></i> </div>
                                <div class="icon selected" id="approvalGridViewIcon"> <i class="fa fa-th" aria-hidden="true"></i> </div>
                            </div>
                        </div>
                    </div>

                    <div class="approval-list-container approval-grid-view" id="approvalListContainer">
                        <div class="approval-list">
                            <!-- Pending Thesis Item 1 -->
                            <div class="approval-item" data-status="pending" data-date="2024-01-15">
                                <div class="thesis-info">
                                    <div class="thesis-header">
                                        <h3>AI-Powered Learning Systems</h3>
                                        <span class="status-badge pending">Pending Review</span>
                                    </div>
                                    <div class="thesis-details">
                                        <p><strong>Student:</strong> John Smith</p>
                                        <p><strong>Program:</strong> BS Information Technology</p>
                                        <p><strong>Submitted:</strong> 2 hours ago</p>
                                        <p><strong>Adviser:</strong> Dr. Emily Johnson</p>
                                    </div>
                                    <div class="thesis-description">
                                        <p>Artificial intelligence applications in modern education systems and adaptive learning platforms.</p>
                                    </div>
                                </div>
                                <div class="approval-actions">
                                    <button class="btn-view" onclick="viewThesis(1)">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <button class="btn-approve" onclick="approveThesis(1)">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn-reject" onclick="rejectThesis(1)">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </div>
                            </div>

                            <!-- Pending Thesis Item 2 -->
                            <div class="approval-item" data-status="pending" data-date="2024-01-14">
                                <div class="thesis-info">
                                    <div class="thesis-header">
                                        <h3>Blockchain Security Framework</h3>
                                        <span class="status-badge pending">Pending Review</span>
                                    </div>
                                    <div class="thesis-details">
                                        <p><strong>Student:</strong> Sarah Wilson</p>
                                        <p><strong>Program:</strong> BS Information Technology</p>
                                        <p><strong>Submitted:</strong> 1 day ago</p>
                                        <p><strong>Adviser:</strong> Prof. Michael Brown</p>
                                    </div>
                                    <div class="thesis-description">
                                        <p>Advanced cryptographic techniques for securing blockchain transactions and smart contracts.</p>
                                    </div>
                                </div>
                                <div class="approval-actions">
                                    <button class="btn-view" onclick="viewThesis(2)">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <button class="btn-approve" onclick="approveThesis(2)">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn-reject" onclick="rejectThesis(2)">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </div>
                            </div>

                            <!-- Pending Thesis Item 3 -->
                            <div class="approval-item" data-status="revision" data-date="2024-01-13">
                                <div class="thesis-info">
                                    <div class="thesis-header">
                                        <h3>Medical Diagnosis AI</h3>
                                        <span class="status-badge revision">Revision Requested</span>
                                    </div>
                                    <div class="thesis-details">
                                        <p><strong>Student:</strong> David Lee</p>
                                        <p><strong>Program:</strong> BS Information Technology</p>
                                        <p><strong>Submitted:</strong> 2 days ago</p>
                                        <p><strong>Adviser:</strong> Dr. Rachel Garcia</p>
                                    </div>
                                    <div class="thesis-description">
                                        <p>Machine learning algorithms for early detection and diagnosis of medical conditions.</p>
                                    </div>
                                    <div class="revision-notes">
                                        <p><strong>Revision Notes:</strong> Please add more detailed methodology section and statistical analysis.</p>
                                    </div>
                                </div>
                                <div class="approval-actions">
                                    <button class="btn-view" onclick="viewThesis(3)">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <button class="btn-approve" onclick="approveThesis(3)">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn-request-revision" onclick="requestRevision(3)">
                                        <i class="fa fa-edit"></i> Request Revision
                                    </button>
                                </div>
                            </div>

                            <!-- Add more items to test scrolling -->
                            <div class="approval-item" data-status="pending" data-date="2024-01-12">
                                <div class="thesis-info">
                                    <div class="thesis-header">
                                        <h3>Cloud Computing Security</h3>
                                        <span class="status-badge pending">Pending Review</span>
                                    </div>
                                    <div class="thesis-details">
                                        <p><strong>Student:</strong> Maria Rodriguez</p>
                                        <p><strong>Program:</strong> BS Information Technology</p>
                                        <p><strong>Submitted:</strong> 3 days ago</p>
                                        <p><strong>Adviser:</strong> Dr. James Wilson</p>
                                    </div>
                                    <div class="thesis-description">
                                        <p>Security protocols and best practices for cloud computing environments in enterprise settings.</p>
                                    </div>
                                </div>
                                <div class="approval-actions">
                                    <button class="btn-view" onclick="viewThesis(4)">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <button class="btn-approve" onclick="approveThesis(4)">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn-reject" onclick="rejectThesis(4)">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </div>
                            </div>

                            <div class="approval-item" data-status="pending" data-date="2024-01-11">
                                <div class="thesis-info">
                                    <div class="thesis-header">
                                        <h3>IoT Smart Home System</h3>
                                        <span class="status-badge pending">Pending Review</span>
                                    </div>
                                    <div class="thesis-details">
                                        <p><strong>Student:</strong> Robert Chen</p>
                                        <p><strong>Program:</strong> BS Information Technology</p>
                                        <p><strong>Submitted:</strong> 4 days ago</p>
                                        <p><strong>Adviser:</strong> Prof. Lisa Thompson</p>
                                    </div>
                                    <div class="thesis-description">
                                        <p>Internet of Things implementation for smart home automation and energy efficiency optimization.</p>
                                    </div>
                                </div>
                                <div class="approval-actions">
                                    <button class="btn-view" onclick="viewThesis(5)">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <button class="btn-approve" onclick="approveThesis(5)">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn-reject" onclick="rejectThesis(5)">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                </div>
            </div>
            
            <!-- Dashboard Content (Visible by Default) -->
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
                </ul>
            </div>

            <p class="notFound" id="notFound">No Results Found.</p>

        </section>
    </section>
</div>

<script type="text/javascript" src="faculty.js"></script>
</body>
</html>