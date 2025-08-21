// Recipe Detail Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initializeRecipeDetailPage();
});

function initializeRecipeDetailPage() {
    // Add event listeners for save recipe button
    const saveButton = document.querySelector('.save-recipe');
    if (saveButton) {
        saveButton.addEventListener('click', handleSaveRecipe);
    }

    // Add event listeners for print recipe button
    const printButton = document.querySelector('.print-recipe');
    if (printButton) {
        printButton.addEventListener('click', handlePrintRecipe);
    }

    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', handleSmoothScroll);
    });

    // Initialize ingredient checkboxes
    initializeIngredientCheckboxes();

    // Initialize step tracking
    initializeStepTracking();
}

function handleSaveRecipe(event) {
    const button = event.currentTarget;
    const recipeId = button.dataset.recipeId;
    
    // Toggle save state
    const isSaved = button.classList.contains('saved');
    
    if (isSaved) {
        // Remove from saved
        button.classList.remove('saved');
        button.innerHTML = '<i class="fas fa-heart"></i> Save Recipe';
        showNotification('Recipe removed from saved', 'info');
    } else {
        // Add to saved
        button.classList.add('saved');
        button.innerHTML = '<i class="fas fa-heart"></i> Recipe Saved';
        showNotification('Recipe saved successfully!', 'success');
    }

    // Here you would typically make an AJAX call to save/unsave the recipe
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

function handlePrintRecipe(event) {
    // Create a print-friendly version of the recipe
    const recipeName = document.querySelector('.recipe-header h1').textContent;
    const recipeDescription = document.querySelector('.recipe-description').textContent;
    const ingredients = Array.from(document.querySelectorAll('.ingredient-item span')).map(item => item.textContent);
    const instructions = Array.from(document.querySelectorAll('.step-content p')).map(item => item.textContent);
    
    // Create print window content
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${recipeName} - EasyPrep</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 2rem; }
                h1 { color: #5d4037; border-bottom: 2px solid #f1c27d; padding-bottom: 0.5rem; }
                h2 { color: #8d5524; margin-top: 2rem; }
                .ingredient-item { margin: 0.5rem 0; }
                .instruction-step { margin: 1rem 0; }
                .step-number { background: #f1c27d; color: white; padding: 0.25rem 0.5rem; border-radius: 50%; display: inline-block; margin-right: 0.5rem; }
                .meta-info { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
                @media print { body { margin: 1rem; } }
            </style>
        </head>
        <body>
            <h1>${recipeName}</h1>
            <p>${recipeDescription}</p>
            
            <div class="meta-info">
                <strong>Cooking Time:</strong> ${document.querySelector('.meta-item:nth-child(1) span').textContent}<br>
                <strong>Servings:</strong> ${document.querySelector('.meta-item:nth-child(2) span').textContent}<br>
                <strong>Calories:</strong> ${document.querySelector('.meta-item:nth-child(3) span').textContent}<br>
                <strong>Difficulty:</strong> ${document.querySelector('.meta-item:nth-child(4) span').textContent}
            </div>
            
            <h2>Ingredients</h2>
            ${ingredients.map(ingredient => `<div class="ingredient-item">• ${ingredient}</div>`).join('')}
            
            <h2>Instructions</h2>
            ${instructions.map((instruction, index) => `<div class="instruction-step"><span class="step-number">${index + 1}</span>${instruction}</div>`).join('')}
            
            <div style="margin-top: 2rem; text-align: center; color: #666; font-size: 0.9rem;">
                Recipe from EasyPrep - Making meal planning simple, delicious, and stress-free
            </div>
        </body>
        </html>
    `;
    
    // Open print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    
    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
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

function initializeIngredientCheckboxes() {
    // Add checkboxes to ingredients for shopping list functionality
    const ingredientsList = document.querySelector('.ingredients-list');
    if (ingredientsList) {
        const ingredientItems = ingredientsList.querySelectorAll('.ingredient-item');
        ingredientItems.forEach(item => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'ingredient-checkbox';
            checkbox.style.cssText = `
                margin-right: 0.5rem;
                transform: scale(1.2);
                accent-color: #8d5524;
            `;
            
            // Insert checkbox at the beginning of the item
            item.insertBefore(checkbox, item.firstChild);
            
            // Add event listener for checkbox
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    item.style.textDecoration = 'line-through';
                    item.style.opacity = '0.6';
                } else {
                    item.style.textDecoration = 'none';
                    item.style.opacity = '1';
                }
            });
        });
    }
}

function initializeStepTracking() {
    // Add step tracking functionality
    const instructionSteps = document.querySelectorAll('.instruction-step');
    instructionSteps.forEach((step, index) => {
        const stepNumber = step.querySelector('.step-number');
        if (stepNumber) {
            stepNumber.style.cursor = 'pointer';
            stepNumber.title = 'Click to mark as completed';
            
            stepNumber.addEventListener('click', function() {
                const isCompleted = this.classList.contains('completed');
                if (isCompleted) {
                    this.classList.remove('completed');
                    this.style.background = 'linear-gradient(135deg, #f1c27d 0%, #e6b366 100%)';
                    step.style.opacity = '1';
                } else {
                    this.classList.add('completed');
                    this.style.background = 'linear-gradient(135deg, #4caf50 0%, #45a049 100%)';
                    step.style.opacity = '0.7';
                }
            });
        }
    });
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
    
    .ingredient-checkbox:checked + span {
        text-decoration: line-through;
        opacity: 0.6;
    }
    
    .step-number.completed {
        background: linear-gradient(135deg, #4caf50 0%, #45a049 100%) !important;
    }
`;
document.head.appendChild(style);






