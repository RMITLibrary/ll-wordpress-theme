document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');

    // Function to perform the search
    function goToSearch() {
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = `/search/?query=${encodeURIComponent(query)}`;
        }
    }

    // Listen for the Enter key press on the search input
    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            goToSearch();
        }
    });

    // Listen for the button click
    searchButton.addEventListener('click', function() {
        goToSearch();
    });
});