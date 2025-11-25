// Reports Manager Class with Course Filtering
class ReportsManager {
    constructor() {
        this.currentReportType = 'overview';
        this.currentDepartment = 'all';
        this.currentCourse = 'all';
        this.charts = {};
        this.isInitialized = false;
    }

    async initialize() {
        if (this.isInitialized) return;
        
        this.initializeEventListeners();
        await this.loadReportsData();
        this.isInitialized = true;
    }

    initializeEventListeners() {
        // Department report buttons
        const reportButtons = document.querySelectorAll('#reports-card .accessCard');
        reportButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleDepartmentSelection(e.target.closest('.accessCard'));
            });
        });

        // Course dropdown change event
        const courseSelect = document.getElementById('courseFilter');
        if (courseSelect) {
            courseSelect.addEventListener('change', (e) => {
                this.handleCourseSelection(e.target.value);
            });
        }

        const chartActions = document.querySelectorAll('.chart-action-btn');
        chartActions.forEach(btn => {
            if (btn.title.toLowerCase() === 'refresh') {
                btn.addEventListener('click', (e) => {
                    this.handleChartAction('refresh', e.currentTarget);
                });
            }
        });

        const downloadReportBtn = document.getElementById('downloadReportBtn');
        if (downloadReportBtn) {
            downloadReportBtn.addEventListener('click', () => {
                Swal.fire({
                    title: 'Generate Report',
                    text: 'Would you like to preview the report before downloading?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Preview First',
                    cancelButtonText: 'Download Directly',
                    showDenyButton: true,
                    denyButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.downloadReportAsPDF(false);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        this.downloadReportAsPDF(true);
                    }
                });
            });
        }

        // Preview report button (if exists)
        const previewReportBtn = document.getElementById('previewReportBtn');
        if (previewReportBtn) {
            previewReportBtn.addEventListener('click', () => {
                this.downloadReportAsPDF(false);
            });
        }
    }

    async handleDepartmentSelection(button) {
        // Remove active class from all buttons
        document.querySelectorAll('#reports-card .accessCard').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Add active class to selected button
        button.classList.add('active');
        
        // Get department from button
        const department = this.getDepartmentFromButton(button);
        this.currentDepartment = department;
        
        // Reset course filter when department changes
        this.currentCourse = 'all';
        
        // Update course dropdown
        await this.updateCourseDropdown(department);
        
        // Reload reports data for selected department
        await this.loadReportsData();
    }

    async handleCourseSelection(course) {
        this.currentCourse = course;
        await this.loadReportsData();
    }

    async updateCourseDropdown(department) {
        const courseSelect = document.getElementById('courseFilter');
        if (!courseSelect) return;

        try {
            // Show loading
            courseSelect.innerHTML = '<option value="all">Loading courses...</option>';
            
            const response = await fetch(`../../../app/Controllers/AdminDashboardController.php?action=getReports&type=overview&department=${department}`);
            const data = await response.json();
            
            if (data.success && data.data.available_courses) {
                courseSelect.innerHTML = '<option value="all">All Courses</option>';
                
                data.data.available_courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.Course;
                    option.textContent = course.Course;
                    courseSelect.appendChild(option);
                });
                
                // Reset to "All Courses"
                courseSelect.value = 'all';
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            courseSelect.innerHTML = '<option value="all">All Courses</option>';
        }
    }

    async downloadReportAsPDF(downloadImmediately = false) {
        try {
            console.log('Download button clicked');
            console.log('Current department:', this.currentDepartment);
            console.log('Current course:', this.currentCourse);
            
            // Show loading state
            const swalInstance = Swal.fire({
                title: 'Generating Report',
                text: 'Please wait while we generate your PDF report...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            console.log('Generating actual report...');
            const reportUrl = `../../../app/Controllers/AdminDashboardController.php?action=generateReport&department=${this.currentDepartment}&course=${this.currentCourse}`;
            console.log('Report URL:', reportUrl);

            const response = await fetch(reportUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/pdf',
                }
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            console.log('Content-Type:', response.headers.get('content-type'));

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response error:', errorText);
                
                // Try to parse as JSON for better error message
                try {
                    const errorJson = JSON.parse(errorText);
                    throw new Error(`HTTP error! status: ${response.status}. ${errorJson.error || errorJson.message || errorText}`);
                } catch (e) {
                    throw new Error(`HTTP error! status: ${response.status}. Server says: ${errorText.substring(0, 200)}`);
                }
            }

            const contentType = response.headers.get('content-type');
            console.log('Final Content-Type:', contentType);

            let blob;
            if (contentType && contentType.includes('application/pdf')) {
                blob = await response.blob();
                console.log('PDF blob size:', blob.size);
                
                if (blob.size === 0) {
                    throw new Error('PDF blob is empty (0 bytes)');
                }
            } else {
                // If not PDF, get as text to see what's returned
                const textResponse = await response.text();
                console.log('Non-PDF response (first 500 chars):', textResponse.substring(0, 500));
                
                // Try to parse as JSON for error details
                try {
                    const errorData = JSON.parse(textResponse);
                    throw new Error(`Server returned ${contentType} instead of PDF. Error: ${errorData.error || errorData.message || 'Unknown error'}`);
                } catch (e) {
                    throw new Error(`Server returned ${contentType} instead of PDF. Response: ${textResponse.substring(0, 200)}`);
                }
            }
            
            // Close loading Swal
            Swal.close();

            if (downloadImmediately) {
                this.downloadPDFFile(blob);
            } else {
                this.previewPDF(blob);
            }

        } catch (error) {
            console.error('Error generating report:', error);
            Swal.close();
            
            Swal.fire({
                title: 'Generation Failed',
                html: `
                    <p>Failed to generate PDF report.</p>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p>Check the browser console for details.</p>
                `,
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    }

    // Preview PDF in new tab
    previewPDF(blob) {
        const url = window.URL.createObjectURL(blob);
        const previewWindow = window.open(url, '_blank');
        
        if (!previewWindow) {
            // If popup blocked, show download option directly
            Swal.fire({
                title: 'Popup Blocked',
                text: 'Please allow popups to preview the PDF, or download directly.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Download Now',
                cancelButtonText: 'Try Preview Again'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.downloadPDFFile(blob);
                } else {
                    this.previewPDF(blob); // Retry preview
                }
            });
            return;
        }

        // Show download confirmation after preview
        setTimeout(() => {
            Swal.fire({
                title: 'Report Ready!',
                html: `
                    <p>Your PDF report has been generated successfully.</p>
                    <p>The report has been opened in a new tab for preview.</p>
                    <p>Would you like to download it now?</p>
                `,
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Download PDF',
                cancelButtonText: 'Keep Preview Only',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.downloadPDFFile(blob);
                // Clean up blob URL immediately after download
                    window.URL.revokeObjectURL(url);
                } else {
                    // Clean up blob URL after some time if not downloading
                    setTimeout(() => {
                        window.URL.revokeObjectURL(url);
                    }, 30000); // Clean up after 30 seconds
                }
            });
        }, 2000);
    }

    // Download PDF file
    downloadPDFFile(blob) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        
        // Generate filename with timestamp
        const timestamp = new Date().toISOString().slice(0, 10);
        const deptName = this.getDepartmentDisplayName(this.currentDepartment);
        const courseName = this.currentCourse === 'all' ? 'All_Courses' : this.currentCourse.replace(/[^a-zA-Z0-9]/g, '_');
        a.download = `Thesis_Report_${deptName}_${courseName}_${timestamp}.pdf`;
        
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        // Show success message
        Swal.fire({
            title: 'Report Downloaded!',
            text: 'Your PDF report has been successfully downloaded.',
            icon: 'success',
            confirmButtonColor: '#3085d6'
        });
    }

    getDepartmentDisplayName(departmentValue) {
        const departmentMap = {
            'all': 'All_Programs',
            'bsit': 'BSIT',
            'beced': 'BECED',
            'bsed': 'BSED',
            'btvted': 'BTVTED',
            'beed': 'BEED',
            'bsned': 'BSNED',
            'bsabe': 'BSABE'
        };
        return departmentMap[departmentValue] || 'All_Programs';
    }

    async loadReportsData() {
        try {
            this.showLoadingState();
            
            const url = `../../../app/Controllers/AdminDashboardController.php?action=getReports&type=${this.currentReportType}&department=${this.currentDepartment}&course=${this.currentCourse}`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.displayReportsData(data.data);
                this.updateDepartmentCounts(data.data.program_counts);
            } else {
                throw new Error(data.error || 'Failed to load reports data');
            }
            
        } catch (error) {
            console.error('Error loading reports:', error);
            this.showErrorState(`Failed to load reports: ${error.message}`);
        }
    }

    displayReportsData(data) {
        // Update stats cards with real data
        this.updateStatsCards(data.stats);
        
        // Update charts with real data
        this.updateCharts(data);
        
        // Hide loading state
        this.hideLoadingState();
    }

    updateStatsCards(stats) {
        const statCards = document.querySelectorAll('.stat-card');
        
        if (statCards[0]) {
            statCards[0].querySelector('h3').textContent = stats.total_theses || '0';
            statCards[0].querySelector('p').textContent = 'Total Theses';
        }
        
        if (statCards[1]) {
            statCards[1].querySelector('h3').textContent = stats.total_students || '0';
            statCards[1].querySelector('p').textContent = 'Total Approved Users';
        }
    }

    updateDepartmentCounts(programCounts) {
        console.log('Raw program counts:', programCounts); 
        
        const allEl = document.getElementById('allCount');
        if (allEl) allEl.textContent = programCounts.all || 0;

        // Map department codes to their element IDs
        const departmentMap = {
            'bsitCount': 'bsit',
            'becedCount': 'beced', 
            'bsedCount': 'bsed',
            'btvtedCount': 'btvted',
            'beedCount': 'beed',
            'bsnedCount': 'bsned',
            'bsabeCount': 'bsabe'
        };

        Object.keys(departmentMap).forEach(id => {
            const el = document.getElementById(id);
            const deptCode = departmentMap[id];
            if (el && programCounts[deptCode] !== undefined) {
                el.textContent = programCounts[deptCode];
                console.log(`Setting ${id} (${deptCode}) to:`, programCounts[deptCode]); // Debug
            } else {
                console.log(`Element ${id} not found or no data for ${deptCode}`); // Debug
                if (el) el.textContent = '0';
            }
        });
    }

    updateCharts(data) {
        this.createCourseDistributionChart(data.user_distribution);  
        this.createThesisUploadsChart(data.program_thesis_counts);   
    }

    createCourseDistributionChart(userDistribution) {
        const ctx = document.getElementById('studentPieChart');
        if (!ctx) {
            console.error('Student pie chart canvas not found');
            return;
        }

        // Destroy existing chart if it exists
        if (this.charts.studentPie) {
            this.charts.studentPie.destroy();
        }

        // Process user distribution data for pie chart
        const chartData = this.processUserDistributionData(userDistribution);
        
        console.log('User Distribution Data for Pie Chart:', chartData);

        // Color mapping for user roles
        const roleColors = {
            'student': '#FF6B6B',
            'faculty': '#4ECDC4',
            'admin': '#45B7D1',
            'superAdmin': '#96CEB4',
            'SubAdmin': '#FFEAA7'
        };

        // Assign colors
        const backgroundColors = chartData.labels.map(label => {
            const role = label.toLowerCase();
            return roleColors[role] || '#CCCCCC';
        });

        this.charts.studentPie = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: backgroundColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 11,
                                family: "'Inter', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} users (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    createThesisUploadsChart(programThesisCounts) {
        const ctx = document.getElementById('thesisBarChart');
        if (!ctx) {
            console.error('Thesis bar chart canvas not found');
            return;
        }

        if (this.charts.thesisBar) {
            this.charts.thesisBar.destroy();
        }

        const chartData = this.processProgramThesisData(programThesisCounts);
        
        console.log('Program Thesis Data for Bar Chart:', chartData);

        this.charts.thesisBar = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Thesis Count',
                    data: chartData.data,
                    backgroundColor: 'rgba(186, 30, 31, 0.8)',
                    borderColor: 'rgba(186, 30, 31, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Number of Theses'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Programs'
                        }
                    }
                }
            }
        });

        this.updateChartFooterStats(chartData);
    }

    processUserDistributionData(userDistribution) {
        if (!userDistribution || userDistribution.length === 0) {
            return {
                labels: ['No Data Available'],
                data: [1]
            };
        }

        const labels = userDistribution.map(item => {
            const role = item.User_Role || item.user_role;

            if (role === 'superAdmin') return 'Administrator';
            if (role === 'SubAdmin') return 'Sub-Admin';
            return role.charAt(0).toUpperCase() + role.slice(1);
        });
        const data = userDistribution.map(item => parseInt(item.user_count) || 0);
        
        return { labels, data };
    }

    processProgramThesisData(programThesisCounts) {
        if (!programThesisCounts || programThesisCounts.length === 0) {
            console.log('No program thesis data available');
            return {
                labels: ['No Data Available'],
                data: [0]
            };
        }

        console.log('Raw program thesis data:', programThesisCounts);

        const labels = programThesisCounts.map(item => {
            const program = item.program || item.Thesis_Course;
            console.log('Processing program:', program);

            // Map full program names to short codes
            const programMap = {
                'Bachelor of Science in Information Technology': 'BSIT',
                'Bachelor of Early Childhood Education': 'BECED', 
                'Bachelor of Secondary Education': 'BSED',
                'Bachelor of Technical-Vocational Teacher Education': 'BTVTED',
                'Bachelor of Elementary Education': 'BEED',
                'Bachelor of Special Needs Education': 'BSNED',
                'Bachelor of Science in Agricultural and Biosystems Engineering': 'BSABE',
                'Bachelor of Science in Agriculture and Biosystems Engineering': 'BSABE',
                'BSIT': 'BSIT',
                'BECED': 'BECED',
                'BSED': 'BSED', 
                'BTVTED': 'BTVTED',
                'BEED': 'BEED',
                'BSNED': 'BSNED',
                'BSABE': 'BSABE'
            };

            return programMap[program] || program;
        });

        const data = programThesisCounts.map(item => parseInt(item.thesis_count) || 0);
        
        console.log('Processed chart data - Labels:', labels, 'Data:', data);
        
        return { labels, data };
    }

    updateChartFooterStats(chartData) {
        const chartFooter = document.querySelector('.chart-footer .chart-stats');
        if (!chartFooter) return;

        const totalTheses = chartData.data.reduce((sum, count) => sum + count, 0);
        
        chartFooter.innerHTML = `
            <div class="chart-stat">
                <span class="stat-label">Total Theses:</span>
                <span class="stat-value">${totalTheses}</span>
            </div>
        `;
    }

    getDepartmentFromButton(button) {
        const buttonId = button.id;
        
        const departmentMap = {
            'allReportsBtn': 'all',
            'bsitReports': 'bsit',
            'becedReports': 'beced',
            'bsedReports': 'bsed',
            'btvtedReports': 'btvted',
            'beedReports': 'beed',
            'bsnedReports': 'bsned',
            'bsabeReports': 'bsabe'
        };
        
        return departmentMap[buttonId] || 'all';
    }

    handleChartAction(action, button) {
        if (action === 'refresh') {
            this.refreshChart(button);
        }
    }

    async refreshChart(button) {
        const icon = button.querySelector('i');
        icon.classList.add('fa-spin');
        
        try {
            await this.loadReportsData();
        } finally {
            icon.classList.remove('fa-spin');
        }
    }

    showLoadingState() {
        const mainContent = document.getElementById('reportsMainContent');
        const loadingState = document.getElementById('reportsLoading');
        if (mainContent) mainContent.style.opacity = '0.6';
        if (loadingState) loadingState.style.display = 'block';
    }

    hideLoadingState() {
        const mainContent = document.getElementById('reportsMainContent');
        const loadingState = document.getElementById('reportsLoading');
        if (mainContent) mainContent.style.opacity = '1';
        if (loadingState) loadingState.style.display = 'none';
    }

    showErrorState(message) {
        const mainContent = document.getElementById('reportsMainContent');
        const errorState = document.getElementById('reportsError');
        const errorMessage = document.getElementById('errorMessage');
        
        if (mainContent) mainContent.style.display = 'none';
        if (errorState && errorMessage) {
            errorMessage.textContent = message;
            errorState.style.display = 'block';
        }
    }
}

// Initialize reports when the page loads and when reports tab is clicked
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reports manager globally
    window.reportsManager = new ReportsManager();

    // Remove download buttons from charts in the HTML
    const chartActions = document.querySelectorAll('.chart-actions');
    chartActions.forEach(actionsContainer => {
        const downloadButtons = actionsContainer.querySelectorAll('.chart-action-btn[title="download"]');
        downloadButtons.forEach(btn => btn.remove());
    });

    // Set up tab switching
    const reportsOption = document.querySelector('.menu-options li[data-view="reports"]');
    if (reportsOption) {
        reportsOption.addEventListener('click', function() {
            // Wait for the tab to become visible
            setTimeout(() => {
                window.reportsManager.initialize();
            }, 100);
        });
    }

    // Also check if we're already on the reports view
    const reportsContainer = document.getElementById('reports-container');
    if (reportsContainer && reportsContainer.style.display !== 'none') {
        setTimeout(() => {
            window.reportsManager.initialize();
        }, 300);
    }

    // Set up department button event listeners
    const allReportsBtn = document.getElementById('allReportsBtn');
    const bsitReportsBtn = document.getElementById('bsitReports');
    const becedReportsBtn = document.getElementById('becedReports');
    const bsedReportsBtn = document.getElementById('bsedReports');
    const bsabeReportsBtn = document.getElementById('bsabeReports');
    const bsnedReportsBtn = document.getElementById('bsnedReports');
    const btvtedReportsBtn = document.getElementById('btvtedReports');
    const beedReportsBtn = document.getElementById('beedReports');
    
    function handleDepartmentReportSelection(selectedButton) {
        const allButtons = [allReportsBtn, bsitReportsBtn, becedReportsBtn, bsedReportsBtn, bsabeReportsBtn, bsnedReportsBtn, btvtedReportsBtn, beedReportsBtn];
        allButtons.forEach(button => {
            if (button) button.classList.remove('active');
        });
        
        if (selectedButton) selectedButton.classList.add('active');
        
        const departmentName = selectedButton ? selectedButton.querySelector('h4').textContent : 'All';
        console.log(`Selected department: ${departmentName}`);
    }
    
    // Add event listeners to department buttons
    if (allReportsBtn) {
        allReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }
    
    if (bsitReportsBtn) {
        bsitReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }
    
    if (becedReportsBtn) {
        becedReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }
    
    if (bsedReportsBtn) {
        bsedReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }

    if (bsabeReportsBtn) {
        bsabeReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }

    if (bsnedReportsBtn) {
        bsnedReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }

    if (btvtedReportsBtn) {
        btvtedReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }

    if (beedReportsBtn) {
        beedReportsBtn.addEventListener('click', function() {
            handleDepartmentReportSelection(this);
        });
    }
});