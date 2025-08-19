// Recipes Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initializeRecipesPage();
});

function initializeRecipesPage() {
    // Add event listeners for save recipe buttons
    const saveButtons = document.querySelectorAll('.save-recipe');
    saveButtons.forEach(button => {
        button.addEventListener('click', handleSaveRecipe);
    });

    // Add event listeners for filter changes
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', handleFilterChange);
    });

    // Add search functionality
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', handleSmoothScroll);
    });

    // Initialize tooltips
    initializeTooltips();
}

function handleSaveRecipe(event) {
    const button = event.currentTarget;
    const recipeId = button.dataset.recipeId;
    
    // Toggle save state
    const isSaved = button.classList.contains('saved');
    
    if (isSaved) {
        // Remove from saved
        button.classList.remove('saved');
        button.innerHTML = '<i class="fas fa-heart"></i> Save';
        showNotification('Recipe removed from saved', 'info');
    } else {
        // Add to saved
        button.classList.add('saved');
        button.innerHTML = '<i class="fas fa-heart"></i> Saved';
        showNotification('Recipe saved successfully!', 'success');
    }

    // Here you would typically make an AJAX call to save/unsave the recipe
    // For now, we'll just simulate it
    saveRecipeToDatabase(recipeId, !isSaved);
}

function saveRecipeToDatabase(recipeId, save) {
    // This would be an AJAX call to your backend
    // For now, we'll just log it
    console.log(`${save ? 'Saving' : 'Removing'} recipe ${recipeId}`);
    
    // Example AJAX call (uncomment when backend is ready):
    /*
    fetch('save-recipe.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            recipe_id: recipeId,
            action: save ? 'save' : 'unsave'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
    */
}

function handleFilterChange(event) {
    // Auto-submit the form when filters change
    const form = event.target.closest('form');
    if (form) {
        form.submit();
    }
}

function handleSearch(event) {
    const searchTerm = event.target.value;
    const form = event.target.closest('form');
    
    // If search term is empty and form has other filters, don't submit
    if (searchTerm.length === 0) {
        return;
    }
    
    // Auto-submit after a delay (debounced)
    if (form) {
        form.submit();
    }
}

function handleSmoothScroll(event) {
    event.preventDefault();
    const targetId = event.currentTarget.getAttribute('href');
    const targetElement = document.querySelector(targetId);
    
    if (targetElement) {
        targetElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

function initializeTooltips() {
    // Add tooltips to recipe cards
    const recipeCards = document.querySelectorAll('.recipe-card');
    recipeCards.forEach(card => {
        card.addEventListener('mouseenter', showTooltip);
        card.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const card = event.currentTarget;
    const recipeName = card.querySelector('h3').textContent;
    const cuisineType = card.querySelector('.cuisine-type').textContent;
    
    // Create tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'recipe-tooltip';
    tooltip.innerHTML = `
        <strong>${recipeName}</strong><br>
        Cuisine: ${cuisineType}
    `;
    
    // Position tooltip
    const rect = card.getBoundingClientRect();
    tooltip.style.position = 'fixed';
    tooltip.style.top = rect.top - 60 + 'px';
    tooltip.style.left = rect.left + (rect.width / 2) - 100 + 'px';
    tooltip.style.zIndex = '1000';
    
    document.body.appendChild(tooltip);
    
    // Store reference for removal
    card.tooltip = tooltip;
}

function hideTooltip(event) {
    const card = event.currentTarget;
    if (card.tooltip) {
        card.tooltip.remove();
        card.tooltip = null;
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${getNotificationIcon(type)}"></i>
        <span>${message}</span>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 300px;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Add close functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 3000);
}

function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

function getNotificationColor(type) {
    switch (type) {
        case 'success': return 'linear-gradient(135deg, #4caf50 0%, #45a049 100%)';
        case 'error': return 'linear-gradient(135deg, #f44336 0%, #d32f2f 100%)';
        case 'warning': return 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
        default: return 'linear-gradient(135deg, #2196f3 0%, #1976d2 100%)';
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .recipe-tooltip {
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        pointer-events: none;
        white-space: nowrap;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        font-size: 0.875rem;
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
`;
document.head.appendChild(style);


