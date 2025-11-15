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

        // Chart action buttons
        const chartActions = document.querySelectorAll('.chart-action-btn');
        chartActions.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.currentTarget.title.toLowerCase();
                this.handleChartAction(action, e.currentTarget);
            });
        });
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

    createCourseDistributionChart(courseDistribution) {
        const ctx = document.getElementById('studentPieChart');
        if (!ctx) {
            console.error('Student pie chart canvas not found');
            return;
        }

        // Destroy existing chart if it exists
        if (this.charts.studentPie) {
            this.charts.studentPie.destroy();
        }

        // Process real data for chart
        const chartData = this.processCourseDistributionData(courseDistribution);
        
        console.log('Course Distribution Data for Pie Chart:', chartData);

        // Color mapping for courses
        const courseColors = {
            'Bachelor of Science in Information Technology': '#FF6B6B',
            'Bachelor of Early Childhood Education': '#4ECDC4',
            'Bachelor of Secondary Education': '#45B7D1',
            'Bachelor of Technical-Vocational Teacher Education': '#96CEB4',
            'Bachelor of Elementary Education': '#FFEAA7',
            'Bachelor of Special Needs Education': '#cd84cdff',
            'Bachelor of Science in Agricultural and Biosystems Engineering': '#48ffd1ff',
            'Bachelor of Science in Agriculture and Biosystems Engineering': '#48ffd1ff'
        };

        // Assign colors
        const backgroundColors = chartData.labels.map(label => {
            // Try exact match first
            if (courseColors[label]) {
                return courseColors[label];
            }
            
            // Try partial match
            for (const [key, value] of Object.entries(courseColors)) {
                if (label.toLowerCase().includes(key.toLowerCase()) || 
                    key.toLowerCase().includes(label.toLowerCase())) {
                    return value;
                }
            }
            
            // Fallback color
            return '#CCCCCC';
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
                                return `${label}: ${value} students (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
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
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
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
        const peakMonth = uploadData.labels[maxIndex] || 'No data';
        const totalYear = uploadData.data.reduce((sum, count) => sum + count, 0);
        
        chartFooter.innerHTML = `
            <div class="chart-stat">
                <span class="stat-label">Peak Month:</span>
                <span class="stat-value">${peakMonth} (${maxUploads})</span>
            </div>
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
        switch (action) {
            case 'download':
                this.downloadChartData(button);
                break;
            case 'refresh':
                this.refreshChart(button);
                break;
        }
    }

    downloadChartData(button) {
        const chartType = button.closest('.chart-card').querySelector('h3').textContent;
        Swal.fire({
            title: 'Download Chart Data',
            text: `Download ${chartType} data as CSV?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Download CSV'
        });
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