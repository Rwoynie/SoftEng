<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand">
    <link rel="stylesheet" href="publicView.css">
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
                <li> <i class="fa fa-wrench icon" aria-hidden="true"></i> </li>
            </ul>
        </nav>

        <div class="more-options">
            <i class="fa fa-ellipsis-h icon" aria-hidden="true"></i>
        </div>
    </section>

    <section class="main-content">
        <header class="header">
            <div class="title">Thesis Repository</div>
            <div class="menu">
                <button id="allButton"> All </button>
                <button class="selected" id="recentButton"> Recent </button>
            </div>
        </header>

        <section class="app-content">
            <div class="app-content-header">
                <div class="searchbox">
                    <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                    <input type="text" name="search" placeholder="Search thesis" class="search-text">
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
                        <div class="icon"> <i class="fa fa-bars" aria-hidden="true"></i> </div>
                        <div class="icon selected"> <i class="fa fa-th" aria-hidden="true"></i> </div>
                    </div>
                </div>
            </div>

            <!-- Projects Container (scrollable) -->
            <div class="projects-container">
                <!-- Recent Projects View -->
                <ul class="projects" id="recentView">
                    <li class="project-item" data-tags="important enhancement">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?technology" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> AI-Powered Learning Systems </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> ailearning.edu </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Artificial intelligence applications in modern education systems and adaptive learning platforms.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="94"></p>
                            <progress max="100" value="94" data-value="94"> 94% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days danger">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 2 days ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?student" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?graduate" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="announcement news">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?blockchain" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Blockchain Security </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> blockchainsec.io </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Advanced cryptographic techniques for securing blockchain transactions and smart contracts.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="64"></p>
                            <progress max="100" value="64" data-value="64"> 64% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days warning">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 4 days ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?researcher" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?scientist" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="discussion interesting">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?health" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Medical Diagnosis AI </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> medai.diagnosis </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Machine learning algorithms for early detection and diagnosis of medical conditions.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="59"></p>
                            <progress max="100" value="59" data-value="59"> 59% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days warning">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 5 days ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?doctor" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?nurse" alt="User" />
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- All Projects View (initially hidden) -->
                <ul class="projects all-projects" id="allView">
                    <li class="project-item" data-tags="cannot-fix off-topic">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?network" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> 5G Network Optimization </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> 5goptimization.tech </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Advanced algorithms for optimizing 5G network performance and coverage in urban areas.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="78"></p>
                            <progress max="100" value="78" data-value="78"> 78% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 2 weeks ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?engineer" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?technician" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="enhancement change-declined">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?robot" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Autonomous Robotics </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> autonobot.org </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Development of autonomous navigation systems for industrial and service robotics applications.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="82"></p>
                            <progress max="100" value="82" data-value="82"> 82% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 3 weeks ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?roboticist" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?mechanic" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="news discussion">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?data" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Big Data Analytics </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> bigdataresearch.edu </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Novel approaches to processing and analyzing large-scale datasets for scientific research.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="91"></p>
                            <progress max="100" value="91" data-value="91"> 91% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 1 month ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?analyst" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?statistician" alt="User" />
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
<script type="text/javascript" src="publicView.js"></script>
</body>
</html>