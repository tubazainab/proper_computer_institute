/**
 * Proper Computer - Public Website JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Live Course Search & Filtering on courses.php
    const searchInput = document.getElementById('courseSearchInput');
    const categoryButtons = document.querySelectorAll('.category-filter-btn');
    const courseCards = document.querySelectorAll('.course-item-col');
    const noResultsMsg = document.getElementById('noCoursesFound');

    let activeCategory = 'all';

    function filterCourses() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let visibleCount = 0;

        courseCards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const category = card.getAttribute('data-category') || '';
            const desc = card.getAttribute('data-desc') || '';

            const matchesCategory = (activeCategory === 'all' || category === activeCategory);
            const matchesQuery = (query === '' || title.toLowerCase().includes(query) || desc.toLowerCase().includes(query));

            if (matchesCategory && matchesQuery) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResultsMsg) {
            noResultsMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCourses);
    }

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            categoryButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeCategory = this.getAttribute('data-category');
            filterCourses();
        });
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
});
