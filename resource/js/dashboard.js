// Log data for debugging
console.log(
  "monthlySales:",
  typeof monthlySales !== "undefined" ? monthlySales : "Not defined"
);
console.log(
  "stockLabels:",
  typeof stockLabels !== "undefined" ? stockLabels : "Not defined"
);
console.log(
  "stockData:",
  typeof stockData !== "undefined" ? stockData : "Not defined"
);
console.log(
  "productLabels:",
  typeof productLabels !== "undefined" ? productLabels : "Not defined"
);
console.log(
  "productData:",
  typeof productData !== "undefined" ? productData : "Not defined"
);

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
        data:
          typeof monthlySales !== "undefined" && monthlySales.length
            ? monthlySales
            : Array(12).fill(0),
        borderColor: "blue",
        borderWidth: 2,
        fill: false,
        tension: 0.1,
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

// Stock Levels Chart (Pie Chart)
const ctx2 = document.getElementById("stockChart").getContext("2d");
new Chart(ctx2, {
  type: "pie",
  data: {
    labels:
      typeof stockLabels !== "undefined" && stockLabels.length
        ? stockLabels
        : ["No Data"],
    datasets: [
      {
        data:
          typeof stockData !== "undefined" && stockData.length
            ? stockData
            : [1],
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
  options: {
    responsive: true,
  },
});

// Top Selling Products (Bar Chart)
const ctx3 = document.getElementById("topProductsChart").getContext("2d");
new Chart(ctx3, {
  type: "bar",
  data: {
    labels:
      typeof productLabels !== "undefined" && productLabels.length
        ? productLabels
        : ["No Data"],
    datasets: [
      {
        label: "Units Sold",
        data:
          typeof productData !== "undefined" && productData.length
            ? productData
            : [0],
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
