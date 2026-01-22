/**
 * Chart Handler Module
 * Manages Chart.js visualizations (Pie Chart & Bar Chart)
 */
const ChartHandler = {
  pieChart: null,
  barChart: null,

  /**
   * Initialize chart handler
   * Creates both Pie Chart and Bar Chart
   */
  init() {
    this.createPieChart();
    this.createBarChart();
  },

  /**
   * Create Pie Chart - Expense by Category
   * Shows breakdown of spending by category
   * @async
   */
  async createPieChart() {
    const canvas = document.getElementById("expensePieChart");
    if (!canvas) return;

    try {
      // Fetch data from API
      const response = await fetch(
        "api/analytics/summary.php?type=expense_by_category"
      );
      const result = await response.json();

      if (!result.success || result.data.length === 0) {
        this.showEmptyState(canvas, "Chưa có dữ liệu chi tiêu");
        return;
      }

      // Prepare chart data
      const labels = result.data.map((item) => item.category_name);
      const amounts = result.data.map((item) =>
        Number.parseFloat(item.total_amount)
      );
      const percentages = result.data.map((item) =>
        Number.parseFloat(item.percentage)
      );

      // Generate colors
      const colors = this.generateColors(labels.length);

      // Destroy existing chart
      if (this.pieChart) {
        this.pieChart.destroy();
      }

      // Create new chart
      this.pieChart = new Chart(canvas, {
        type: "pie",
        data: {
          labels: labels,
          datasets: [
            {
              data: amounts,
              backgroundColor: colors,
              borderWidth: 2,
              borderColor: "#fff",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                padding: 15,
                font: { size: 12 },
              },
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const label = context.label || "";
                  const value = this.formatMoney(context.parsed);
                  const percentage = percentages[context.dataIndex];
                  return `${label}: ${value} (${percentage}%)`;
                },
              },
            },
          },
        },
      });
    } catch (error) {
      // Log error for debugging if needed
      if (typeof console !== "undefined" && console.error) {
        console.error("Pie Chart Error:", error);
      }
      this.showEmptyState(canvas, "Không thể tải dữ liệu biểu đồ");
    }
  },

  /**
   * Create Bar Chart - Income vs Expense by Month
   * Compares income and expense over last 12 months
   * @async
   */
  async createBarChart() {
    const canvas = document.getElementById("incomeExpenseBarChart");
    if (!canvas) return;

    try {
      // Fetch data from API
      const response = await fetch(
        "api/analytics/summary.php?type=income_vs_expense_monthly"
      );
      const result = await response.json();

      if (!result.success || result.data.length === 0) {
        this.showEmptyState(canvas, "Chưa có dữ liệu theo tháng");
        return;
      }

      // Prepare chart data
      const labels = result.data.map((item) => item.month_label);
      const incomeData = result.data.map((item) =>
        Number.parseFloat(item.total_income)
      );
      const expenseData = result.data.map((item) =>
        Number.parseFloat(item.total_expense)
      );

      // Destroy existing chart
      if (this.barChart) {
        this.barChart.destroy();
      }

      // Create new chart
      this.barChart = new Chart(canvas, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Thu nhập",
              data: incomeData,
              backgroundColor: "rgba(46, 204, 113, 0.8)",
              borderColor: "rgba(46, 204, 113, 1)",
              borderWidth: 1,
            },
            {
              label: "Chi tiêu",
              data: expenseData,
              backgroundColor: "rgba(231, 76, 60, 0.8)",
              borderColor: "rgba(231, 76, 60, 1)",
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "top",
              labels: {
                padding: 15,
                font: { size: 12 },
              },
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const label = context.dataset.label || "";
                  const value = this.formatMoney(context.parsed.y);
                  return `${label}: ${value}`;
                },
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value) => this.formatMoney(value),
              },
            },
          },
        },
      });
    } catch (error) {
      // Log error for debugging if needed
      if (typeof console !== "undefined" && console.error) {
        console.error("Bar Chart Error:", error);
      }
      this.showEmptyState(canvas, "Không thể tải dữ liệu biểu đồ");
    }
  },

  /**
   * Refresh both charts
   * Called after transactions are added/updated/deleted
   */
  refresh() {
    this.createPieChart();
    this.createBarChart();
  },

  /**
   * Generate distinct colors for pie chart segments
   * @param {number} count - Number of colors needed
   * @returns {Array<string>} Array of color strings
   */
  generateColors(count) {
    const baseColors = [
      "#3498db",
      "#e74c3c",
      "#2ecc71",
      "#f39c12",
      "#9b59b6",
      "#1abc9c",
      "#34495e",
      "#e67e22",
      "#95a5a6",
      "#d35400",
    ];

    if (count <= baseColors.length) {
      return baseColors.slice(0, count);
    }

    // Generate additional colors if needed
    const colors = [...baseColors];
    for (let i = baseColors.length; i < count; i++) {
      colors.push(this.getRandomColor());
    }
    return colors;
  },

  /**
   * Generate random color
   * @returns {string} Random hex color
   */
  getRandomColor() {
    const letters = "0123456789ABCDEF";
    let color = "#";
    for (let i = 0; i < 6; i++) {
      color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
  },

  /**
   * Format number to Vietnamese currency
   * @param {number} amount - Amount to format
   * @returns {string} Formatted money string
   */
  formatMoney(amount) {
    return new Intl.NumberFormat("vi-VN").format(amount) + " Đ";
  },

  /**
   * Show empty state message on canvas
   * @param {HTMLCanvasElement} canvas - Canvas element
   * @param {string} message - Message to display
   */
  showEmptyState(canvas, message) {
    const ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = "14px Arial";
    ctx.fillStyle = "#95a5a6";
    ctx.textAlign = "center";
    ctx.fillText(message, canvas.width / 2, canvas.height / 2);
  },
};
