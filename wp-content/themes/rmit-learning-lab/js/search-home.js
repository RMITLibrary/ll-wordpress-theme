document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const searchForm = searchInput ? searchInput.closest('form') : null;

    if (!searchInput) {
        return;
    }

    // Function to perform the search
    function goToSearch() {
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = `/search/?query=${encodeURIComponent(query)}`;
        }
    }

    // Keep form submit from triggering default page refresh
    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            goToSearch();
        });
    }

    // Listen for the Enter key press on the search input
    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            goToSearch();
        }
    });

    // Listen for the button click
    if (searchButton) {
        searchButton.addEventListener('click', function(event) {
            event.preventDefault();
            goToSearch();
        });
    }
});
