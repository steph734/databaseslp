// Sample data (Replace with dynamic data from your database)
const salesData = [1200, 1500, 1000, 1800, 2200, 2500, 2700];
const stockData = [50, 30, 80, 20, 60]; // Stock levels for different categories
const productLabels = ["Rice", "Cans", "Noodles", "Shampoo", "Soap"];

// Sales Trend Chart (Line Chart)
const ctx1 = document.getElementById("salesChart").getContext("2d");
new Chart(ctx1, {
  type: "line",
  data: {
    labels: [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ],
    datasets: [
      {
        label: "Monthly Sales (₱)",
        data: salesData,
        borderColor: "blue",
        borderWidth: 2,
        fill: false,
        tension: 0.1,
      },
    ],
  },
});

// Stock Levels Chart (Pie Chart)
const ctx2 = document.getElementById("stockChart").getContext("2d");
new Chart(ctx2, {
  type: "pie",
  data: {
    labels: productLabels,
    datasets: [
      {
        data: stockData,
        backgroundColor: [
          "rgb(52, 102, 165)",
          "rgb(234, 128, 60)",
          "rgb(149, 176, 40)",
          "rgb(245, 200, 83)",
          "rgb(171, 112, 104)",
        ],
      },
    ],
  },
});

// Top Selling Products (Bar Chart)
const ctx3 = document.getElementById("topProductsChart").getContext("2d");
new Chart(ctx3, {
  type: "bar",
  data: {
    labels: productLabels,
    datasets: [
      {
        label: "Units Sold",
        data: [500, 700, 400, 650, 300],
        backgroundColor: "rgb(52, 102, 165)",
      },
    ],
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  },
});
