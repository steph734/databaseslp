<form action="" id="searchForm">
    <div class="search-container-dash" style="position: relative;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search..." id="searchInput">
        <!-- Suggestions dropdown -->
        <div id="suggestions"
            style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000;">
        </div>
    </div>
</form>

<script>
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    const pageMap = {
        'dashboard': '../layout/web-layout.php?page=dashboard',
        'customer': '../layout/web-layout.php?page=customer',
        'customers': '../layout/web-layout.php?page=customer',
        'membership': '../layout/web-layout.php?page=membership',
        'members': '../layout/web-layout.php?page=membership',
        'points': '../layout/web-layout.php?page=points',
        'sales': '../layout/web-layout.php?page=sales',
        'transactions': '../layout/web-layout.php?page=sales',
        'returns': '../layout/web-layout.php?page=returns',
        'inventory': '../layout/web-layout.php?page=inventory',
        'products': '../layout/web-layout.php?page=products',
        'supplier': '../layout/web-layout.php?page=supplier',
        'suppliers': '../layout/web-layout.php?page=supplier',
        'reports': '../layout/web-layout.php?page=reports',
        'profile': '../layout/web-layout.php?page=profileadmin',
        'account': '../layout/web-layout.php?page=account',
        'accounts': '../layout/web-layout.php?page=account'
    };

    // Find exact match first
    if (pageMap[searchTerm]) {
        window.location.href = pageMap[searchTerm];
        return;
    }

    // Find partial matches
    const matches = Object.entries(pageMap).filter(([key]) => key.includes(searchTerm));
    if (matches.length === 1) {
        window.location.href = matches[0][1];
        return;
    }

    if (matches.length === 0) {
        alert('Page not found. Please try another search term.');
    }
});

// Add real-time suggestions
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    const suggestionsDiv = document.getElementById('suggestions');
    const pageMap = {
        'dashboard': '../layout/web-layout.php?page=dashboard',
        'customer': '../layout/web-layout.php?page=customer',
        'customers': '../layout/web-layout.php?page=customer',
        'membership': '../layout/web-layout.php?page=membership',
        'members': '../layout/web-layout.php?page=membership',
        'points': '../layout/web-layout.php?page=points',
        'sales': '../layout/web-layout.php?page=sales',
        'transactions': '../layout/web-layout.php?page=sales',
        'returns': '../layout/web-layout.php?page=returns',
        'inventory': '../layout/web-layout.php?page=inventory',
        'products': '../layout/web-layout.php?page=products',
        'supplier': '../layout/web-layout.php?page=supplier',
        'suppliers': '../layout/web-layout.php?page=supplier',
        'reports': '../layout/web-layout.php?page=reports',
        'profile': '../layout/web-layout.php?page=profileadmin',
        'account': '../layout/web-layout.php?page=account',
        'accounts': '../layout/web-layout.php?page=account'
    };

    if (!searchTerm) {
        suggestionsDiv.style.display = 'none';
        return;
    }

    // Find matching suggestions
    const matches = Object.keys(pageMap)
        .filter(key => key.includes(searchTerm))
        .sort(); // Sort alphabetically

    if (matches.length > 0) {
        suggestionsDiv.innerHTML = matches.map(key => `
            <div class="suggestion-item" 
                 style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;"
                 onclick="window.location.href='${pageMap[key]}'"
                 onmouseover="this.style.backgroundColor='#f5f5f5'"
                 onmouseout="this.style.backgroundColor='white'">
                ${key}
            </div>
        `).join('');
        suggestionsDiv.style.display = 'block';
    } else {
        suggestionsDiv.innerHTML = '<div style="padding: 8px; color: #666;">No matches found</div>';
        suggestionsDiv.style.display = 'block';
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    const searchContainer = document.querySelector('.search-container-dash');
    if (!searchContainer.contains(e.target)) {
        document.getElementById('suggestions').style.display = 'none';
    }
});

// Clear suggestions when submitting
document.getElementById('searchForm').addEventListener('submit', function() {
    document.getElementById('suggestions').style.display = 'none';
});
</script>

<style>
/* Optional: Add some basic styling */
.suggestion-item:hover {
    background-color: #f5f5f5;
}

.search-container-dash {
    position: relative;
}

#suggestions {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>