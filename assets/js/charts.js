/**
 * SIMS - Charts JavaScript
 * Student Information Management System
 * 
 * Interactive charts and data visualization
 */

// Chart Configuration
const ChartConfig = {
    colors: {
        primary: '#00f0ff',
        secondary: '#ff00ff',
        accent: '#00ff88',
        warning: '#ffaa00',
        danger: '#ff3366',
        background: 'rgba(15, 15, 25, 0.8)'
    },
    fonts: {
        family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
        size: 12
    }
};

// Initialize Charts
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart !== 'undefined') {
        initializeStudentPerformanceChart();
        initializeAttendanceChart();
        initializeGPATrendChart();
        initializeSubjectDistributionChart();
    }
});

// Student Performance Chart
function initializeStudentPerformanceChart() {
    const ctx = document.getElementById('studentPerformanceChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Average GPA',
                data: [3.2, 3.4, 3.3, 3.5, 3.6, 3.7],
                borderColor: ChartConfig.colors.primary,
                backgroundColor: 'rgba(0, 240, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: ChartConfig.colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            }, {
                label: 'Target GPA',
                data: [3.5, 3.5, 3.5, 3.5, 3.5, 3.5],
                borderColor: ChartConfig.colors.secondary,
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#ffffff',
                        font: {
                            family: ChartConfig.fonts.family,
                            size: ChartConfig.fonts.size
                        },
                        usePointStyle: true
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#a0a0b0',
                        font: {
                            family: ChartConfig.fonts.family
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#a0a0b0',
                        font: {
                            family: ChartConfig.fonts.family
                        }
                    },
                    min: 0,
                    max: 4.0
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Attendance Chart
function initializeAttendanceChart() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Excused'],
            datasets: [{
                data: [75, 10, 8, 7],
                backgroundColor: [
                    ChartConfig.colors.accent,
                    ChartConfig.colors.danger,
                    ChartConfig.colors.warning,
                    ChartConfig.colors.secondary
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        color: '#ffffff',
                        font: {
                            family: ChartConfig.fonts.family,
                            size: ChartConfig.fonts.size
                        },
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            cutout: '70%'
        }
    });
}

// GPA Trend Chart
function initializeGPATrendChart() {
    const ctx = document.getElementById('gpaTrendChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Math', 'Science', 'English', 'History', 'Art', 'PE'],
            datasets: [{
                label: 'Current GPA',
                data: [3.8, 3.5, 3.2, 3.9, 3.6, 3.4],
                backgroundColor: [
                    'rgba(0, 240, 255, 0.8)',
                    'rgba(255, 0, 255, 0.8)',
                    'rgba(0, 255, 136, 0.8)',
                    'rgba(255, 170, 0, 0.8)',
                    'rgba(255, 51, 102, 0.8)',
                    'rgba(0, 240, 255, 0.8)'
                ],
                borderRadius: 10,
                borderSkipped: false
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
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#a0a0b0',
                        font: {
                            family: ChartConfig.fonts.family
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#a0a0b0',
                        font: {
                            family: ChartConfig.fonts.family
                        }
                    },
                    min: 0,
                    max: 4.0
                }
            }
        }
    });
}

// Subject Distribution Chart
function initializeSubjectDistributionChart() {
    const ctx = document.getElementById('subjectDistributionChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: ['STEM', 'Humanities', 'Arts', 'Languages', 'Physical Ed'],
            datasets: [{
                data: [35, 25, 15, 15, 10],
                backgroundColor: [
                    'rgba(0, 240, 255, 0.7)',
                    'rgba(255, 0, 255, 0.7)',
                    'rgba(0, 255, 136, 0.7)',
                    'rgba(255, 170, 0, 0.7)',
                    'rgba(255, 51, 102, 0.7)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        color: '#ffffff',
                        font: {
                            family: ChartConfig.fonts.family,
                            size: ChartConfig.fonts.size
                        },
                        usePointStyle: true
                    }
                }
            },
            scales: {
                r: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        display: false
                    },
                    pointLabels: {
                        color: '#a0a0b0',
                        font: {
                            family: ChartConfig.fonts.family
                        }
                    }
                }
            }
        }
    });
}

// Update Chart Data
function updateChartData(chartId, newData) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        chart.data.datasets.forEach((dataset, index) => {
            dataset.data = newData[index] || dataset.data;
        });
        chart.update();
    }
}

// Create Custom Chart
function createCustomChart(canvasId, type, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                labels: {
                    color: '#ffffff',
                    font: {
                        family: ChartConfig.fonts.family
                    }
                }
            }
        }
    };
    
    return new Chart(ctx, {
        type: type,
        data: data,
        options: { ...defaultOptions, ...options }
    });
}

// Export Chart as Image
function exportChart(chartId, filename = 'chart.png') {
    const chart = Chart.getChart(chartId);
    if (chart) {
        const link = document.createElement('a');
        link.download = filename;
        link.href = chart.toBase64Image();
        link.click();
    }
}

// Real-time Chart Updates
function enableRealTimeUpdates(chartId, updateInterval = 5000) {
    setInterval(() => {
        // Simulate real-time data update
        const chart = Chart.getChart(chartId);
        if (chart) {
            const newData = chart.data.datasets[0].data.map(value => {
                const change = (Math.random() - 0.5) * 0.2;
                return Math.max(0, Math.min(4.0, value + change));
            });
            updateChartData(chartId, [newData]);
        }
    }, updateInterval);
}

// Export functions
window.SIMSCharts = {
    initializeStudentPerformanceChart,
    initializeAttendanceChart,
    initializeGPATrendChart,
    initializeSubjectDistributionChart,
    updateChartData,
    createCustomChart,
    exportChart,
    enableRealTimeUpdates
};
