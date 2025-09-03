<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="userViewPage.css">

</head>
<body>
<div class="dashboard-container">
    <section class="sidebar">
        <div class="logo">
            <i class="fa fa-instagram icon" aria-hidden="true"></i>
        </div>

        <nav>
            <ul class="menu-options">
                <li> <i class="fa fa-home icon" aria-hidden="true"></i> </li>
                <li class="selected"> <i class="fa fa-th-large icon" aria-hidden="true"></i> </li>
                <li> <i class="fa fa-calendar icon" aria-hidden="true"></i> </li>
                <li> <i class="fa fa-comment-o icon" aria-hidden="true"></i> </li>

                <li id="profileSidebarIcon"> <i class="fa fa-user-o icon" aria-hidden="true"></i> </li>
                <li> <i class="fa fa-wrench icon" aria-hidden="true"></i> </li>
            </ul>
        </nav>

        <div class="more-options">
            <i class="fa fa-ellipsis-h icon" aria-hidden="true"></i>
        </div>
    </section>

    <section class="main-content">
        <header class="header">
            <div class="title">Current Thesis</div>
            <ul class="menu">
                <li> All

                </li>
                <li class="selected"> Current
             <!--       <div class="badge"> 6 </div>  -->
                </li>
                <li> Pending

                </li>
                <li> Completed

                </li>
                <li> Failed </li>
            </ul>

            <div class="user-options">
                <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                <div class="icon"> <i class="fa fa-bell-o" aria-hidden="true"></i>
                        <div class="badge"> 3 </div>
                </div>
                <div class="icon user-img" id="profileHeaderIcon">
                    <img src="../Images/profile.png" alt="User Profile" />
                </div>
            </div>
        </header>

        <section class="app-content">
            <!-- Profile Container (initially hidden) -->
            <div class="profile-container" id="profileContainer">
                <div class="profile-header">
                    <img src="https://source.unsplash.com/120x120/?person" alt="Profile" class="profile-image">
                    <div class="profile-info">
                        <h2>John Doe</h2>
                        <p>BSIT 2IT</p>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <div class="stat-value">15</div>
                                <div class="stat-label">Thesis</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">10</div>
                                <div class="stat-label">Thesis Approved</div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="profile-content">
                    <div class="profile-section">
                        <h3>Personal Information</h3>
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

                    <div class="profile-section">
                        <h3>Account Settings</h3>
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
                                <span class="info-value" style="color: var(--color-good);">Active</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Role:</span>
                                <span class="info-value">Project Manager</span>
                            </div>
                            <div class="info-item">
                                <button class="edit-profile-btn">Edit Profile</button>
                            </div>
                    </div>
                </div>
            </div>

            <div class="app-content-header">
                <div class="searchbox">
                    <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                    <input type="text" name="search" placeholder="Search thesis" class="search-text">
                </div>

                <div class="app-list-options">
                    <div class="sort-dropdown">
                        Sort by <span class="by"> Project progress </span> <i class="fa fa-sort-amount-desc" aria-hidden="true"></i>
                        <div class="drop"> <i class="fa fa-caret-down" aria-hidden="true"></i> </div>
                </div>
                <div class="icon"> <i class="fa fa-filter" aria-hidden="true"></i> </div>
                <div class="display-group">
                    <div class="icon"> <i class="fa fa-bars" aria-hidden="true"></i> </div>
                    <div class="icon selected"> <i class="fa fa-th" aria-hidden="true"></i> </div>
                </div>
            </div>
</div>

<ul class="projects">
    <li class="project-item">
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
                <img src="https://source.unsplash.com/30极速飞艇30/?person" alt="User" />
                <img src="https://source.unsplash.com/30x30/?woman" alt="User" />
            </div>
        </div>
    </li>

    <li class="project-item">
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

    <li class="project-item">
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

    <li class="project-item">
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

    <li class="project-item">
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

    <li class="project-item">
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

    <li class="project-item">
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

    <li class="project-item">
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

    <!-- Additional project items would go here -->
</ul>
</section>
</section>
</div>

<div class="fab-icon"> + </div>
</body>
<script type="text/javascript" src="userViewPage.js"></script>
</html>
