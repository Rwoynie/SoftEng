// Reports Manager Class with Fixed Data Connections
class ReportsManager {
    constructor() {
        this.currentReportType = 'overview';
        this.currentDepartment = 'all';
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

    async downloadReportAsPDF(downloadImmediately = false) {
    try {
        console.log('Download button clicked');
        console.log('Current department:', this.currentDepartment);
        
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
        const reportUrl = `../../../app/Controllers/AdminDashboardController.php?action=generateReport&department=${this.currentDepartment}`;
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
        a.download = `Thesis_Report_${deptName}_${timestamp}.pdf`;
        
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
            
            const response = await fetch(`../../../app/Controllers/AdminDashboardController.php?action=getReports&type=${this.currentReportType}&department=${this.currentDepartment}`);
            
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
        }
        
        if (statCards[1]) {
            statCards[1].querySelector('h3').textContent = stats.total_students || '0';
        }
        
        if (statCards[2]) {
            statCards[2].querySelector('h3').textContent = stats.recent_theses || '0';
        }
    }

    updateDepartmentCounts(programCounts) {
        // Update all programs count
        const allCountElement = document.getElementById('allCount');
        if (allCountElement && programCounts) {
            const totalTheses = programCounts.reduce((sum, program) => sum + (parseInt(program.thesis_count) || 0), 0);
            allCountElement.textContent = totalTheses;
        }

        // Update individual program counts
        const programMap = {
            'bsitReports': 'Bachelor of Science in Information Technology',
            'becedReports': 'Bachelor of Early Childhood Education',
            'bsedReports': 'Bachelor of Secondary Education',
            'btvtedReports': 'Bachelor of Technical-Vocational Teacher Education',
            'beedReports': 'Bachelor of Elementary Education',
            'bsnedReports': 'Bachelor of Special Needs Education',
            'bsabeReports': 'Bachelor of Science in Agricultural and Biosystems Engineering'
        };

        Object.keys(programMap).forEach(programId => {
            const element = document.getElementById(programId);
            if (element) {
                const countElement = element.querySelector('.access-count');
                const programName = programMap[programId];
                const programData = programCounts.find(p => p.program === programName);
                countElement.textContent = programData ? programData.thesis_count : '0';
            }
        });
    }

    updateCharts(data) {
        this.createCourseDistributionChart(data.course_distribution);  
        this.createThesisUploadsChart(data.monthly_uploads);  
    }

    // Replace the createCourseDistributionChart method
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
        return {
            labels: ['No Data Available'],
            data: [0]
        };
    }

    const labels = programThesisCounts.map(item => {
        const program = item.program || item.Thesis_Course;

        if (program.includes('Bachelor of Science in Information Technology')) return 'BSIT';
        if (program.includes('Bachelor of Early Childhood Education')) return 'BECED';
        if (program.includes('Bachelor of Secondary Education')) return 'BSED';
        if (program.includes('Bachelor of Technical-Vocational Teacher Education')) return 'BTVTED';
        if (program.includes('Bachelor of Elementary Education')) return 'BEED';
        if (program.includes('Bachelor of Special Needs')) return 'BSNED';
        if (program.includes('Bachelor of Science Agricultural and Biosystems Engineering')) return 'BSABE';
        return program;
    });
    const data = programThesisCounts.map(item => parseInt(item.thesis_count) || 0);
    
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

updateCharts(data) {
    this.createCourseDistributionChart(data.user_distribution);  
    this.createThesisUploadsChart(data.program_thesis_counts);   
}

    createThesisUploadsChart(monthlyUploads) {
        const ctx = document.getElementById('thesisBarChart');
        if (!ctx) {
            console.error('Thesis bar chart canvas not found');
            return;
        }

        // Destroy existing chart if it exists
        if (this.charts.thesisBar) {
            this.charts.thesisBar.destroy();
        }

        // Process real data for chart
        const chartData = this.processMonthlyUploadsData(monthlyUploads);
        
        console.log('Monthly Uploads Data for Bar Chart:', chartData);

        this.charts.thesisBar = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Thesis Uploads',
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
                        }
                    }
                }
            }
        });

        this.updateChartFooterStats(chartData);
    }

    processCourseDistributionData(courseDistribution) {
        if (!courseDistribution || courseDistribution.length === 0) {
            return {
                labels: ['No Data Available'],
                data: [1]
            };
        }

        // Use the actual data from database
        const labels = courseDistribution.map(item => item.course);
        const data = courseDistribution.map(item => parseInt(item.student_count) || 0);
        
        return { labels, data };
    }

    processMonthlyUploadsData(monthlyUploads) {
        if (!monthlyUploads || monthlyUploads.length === 0) {
            const months = ['BSIT', 'BECED', 'BSED', 'BTVTED', 'BEED', 'BSNED', 'BSABE'];
            return {
                labels: months,
                data: months.map(() => 0)
            };
        }

        // Use the actual data from database
        const labels = monthlyUploads.map(item => item.month);
        const data = monthlyUploads.map(item => parseInt(item.upload_count) || 0);
        
        return { labels, data };
    }

    updateChartFooterStats(uploadData) {
        const chartFooter = document.querySelector('.chart-footer .chart-stats');
        if (!chartFooter) return;
        
        const maxUploads = Math.max(...uploadData.data);
        const maxIndex = uploadData.data.indexOf(maxUploads);
        const totalYear = uploadData.data.reduce((sum, count) => sum + count, 0);
        
        chartFooter.innerHTML = `
            <div class="chart-stat">
                <span class="stat-label">Total This Year:</span>
                <span class="stat-value">${totalYear}</span>
            </div>
        `;
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
        
        // Reload reports data for selected department
        await this.loadReportsData();
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
        // Remove download case
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