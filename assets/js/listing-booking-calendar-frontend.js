// Function to get query parameter value
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Check for wpbs-search-guests parameter and update select value
document.addEventListener('DOMContentLoaded', function() {
    const guestsParam = getQueryParam('wpbs-search-guests');
    if (guestsParam) {
        const guestsSelect = document.querySelector('.guests-count select');
        if (guestsSelect) {
            // Find the option that matches the display value
            const option = Array.from(guestsSelect.options).find(opt => {
                return opt.dataset.displayValue === guestsParam;
            });

            if (option) {
                guestsSelect.value = option.value;
                // Trigger change event to update any dependent elements
                const event = new Event('change');
                guestsSelect.dispatchEvent(event);
            }
        }
    }
});