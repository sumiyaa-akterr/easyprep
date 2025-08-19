// Meal Plan JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeMealPlan();
});

function initializeMealPlan() {
    // Initialize user menu dropdown
    initializeUserMenu();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize form interactions
    initializeFormInteractions();
}

function initializeUserMenu() {
    const userMenuToggle = document.querySelector('.user-menu-toggle');
    const userDropdown = document.querySelector('.user-dropdown');

    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuToggle.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });
    }
}

function initializeMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('nav');

    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function() {
            nav.classList.toggle('mobile-active');
            this.classList.toggle('active');
        });
    }
}

function initializeFormInteractions() {
    // Add smooth animations to checkboxes
    const checkboxItems = document.querySelectorAll('.checkbox-item');
    
    checkboxItems.forEach(item => {
        item.addEventListener('click', function(e) {
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (e.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
            }
            
            // Add visual feedback
            if (checkbox.checked) {
                this.style.background = '#fff6e0';
                this.style.borderColor = '#8d5524';
            } else {
                this.style.background = '#f8fafc';
                this.style.borderColor = 'transparent';
            }
        });
    });

    // Form validation
    const form = document.querySelector('.preferences-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const servings = document.querySelector('#servings').value;
            if (!servings) {
                e.preventDefault();
                alert('Please select the number of servings.');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                submitBtn.disabled = true;
            }
        });
    }
}

// Download meal plan functionality
function downloadMealPlan() {
    // Create a printable version
    const printWindow = window.open('', '_blank');
    const mealPlanContent = document.getElementById('meal-plan-content').innerHTML;
    
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>EasyPrep - Weekly Meal Plan</title>
            <style>
                body {
                    font-family: 'Inter', sans-serif;
                    margin: 20px;
                    background: white;
                    color: #333;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #8d5524;
                    padding-bottom: 20px;
                }
                
                .header h1 {
                    color: #5d4037;
                    margin: 0;
                    font-size: 2rem;
                }
                
                .header p {
                    color: #8d5524;
                    margin: 10px 0 0 0;
                }
                
                .meal-plan-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 20px;
                }
                
                .day-card {
                    border: 1px solid #ddd;
                    border-radius: 10px;
                    padding: 15px;
                    break-inside: avoid;
                }
                
                .day-header {
                    border-bottom: 2px solid #f1c27d;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                }
                
                .day-header h3 {
                    color: #5d4037;
                    margin: 0;
                }
                
                .day-stats {
                    font-size: 0.9rem;
                    color: #8d5524;
                    margin-top: 5px;
                }
                
                .meal-item {
                    background: #fff6e0;
                    border-radius: 8px;
                    padding: 10px;
                    margin-bottom: 10px;
                    border-left: 4px solid #8d5524;
                }
                
                .meal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 5px;
                }
                
                .meal-type {
                    font-size: 0.8rem;
                    font-weight: 600;
                    color: #8d5524;
                    text-transform: uppercase;
                }
                
                .cuisine-badge {
                    background: rgba(141, 85, 36, 0.2);
                    color: #5d4037;
                    padding: 2px 8px;
                    border-radius: 10px;
                    font-size: 0.7rem;
                    font-weight: 600;
                }
                
                .meal-item h4 {
                    color: #5d4037;
                    margin: 0 0 5px 0;
                    font-size: 1rem;
                }
                
                .meal-meta {
                    font-size: 0.8rem;
                    color: #8d5524;
                }
                
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    color: #8d5524;
                }
                
                @media print {
                    body { margin: 0; }
                    .meal-plan-grid { grid-template-columns: repeat(2, 1fr); }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🍽️ EasyPrep Weekly Meal Plan</h1>
                <p>Generated on ${new Date().toLocaleDateString()}</p>
            </div>
            
            <div class="meal-plan-grid">
                ${mealPlanContent}
            </div>
            
            <div class="footer">
                <p>Created with ❤️ by EasyPrep | Visit us at easyprep.com</p>
            </div>
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Trigger print dialog
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

// Generate grocery list functionality
function generateGroceryList() {
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';
    btn.disabled = true;
    
    // Simulate API call to calculate groceries
    setTimeout(() => {
        // Calculate all ingredients needed for the week
        const groceryItems = calculateGroceriesFromMealPlan();
        
        // Create grocery list in database and redirect
        if (confirm(`Found ${groceryItems.length} items needed for your meal plan! 🛒\\n\\nTotal estimated cost: $${calculateTotalCost(groceryItems)}\\n\\nProceed to place order?`)) {
            // Redirect to grocery page with auto-filled cart
            window.location.href = 'grocery.php?auto_fill=true&meal_plan=true';
        }
        
        // Restore button
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function calculateGroceriesFromMealPlan() {
    // This would parse all meals and extract ingredients
    // For now, return sample grocery list
    return [
        { name: 'Rice', quantity: '2kg', category: 'Grains', price: 150 },
        { name: 'Chicken', quantity: '1kg', category: 'Meat', price: 300 },
        { name: 'Vegetables Mix', quantity: '2kg', category: 'Vegetables', price: 200 },
        { name: 'Spices Pack', quantity: '1 set', category: 'Spices', price: 180 },
        { name: 'Oil', quantity: '1L', category: 'Cooking', price: 120 },
        { name: 'Onions', quantity: '1kg', category: 'Vegetables', price: 80 },
        { name: 'Garlic', quantity: '250g', category: 'Vegetables', price: 60 },
        { name: 'Eggs', quantity: '12 pieces', category: 'Dairy', price: 90 }
    ];
}

function calculateTotalCost(items) {
    const total = items.reduce((sum, item) => sum + item.price, 0);
    return total.toFixed(0);
}

// Smooth scrolling for internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add loading animation to buttons
document.querySelectorAll('.btn-primary, .btn-secondary').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!this.disabled) {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        }
    });
});

// Form enhancement - show preference summary
function updatePreferenceSummary() {
    const servings = document.querySelector('#servings')?.value;
    const restrictions = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
        .map(cb => cb.nextElementSibling.nextElementSibling.textContent);
    
    // Create or update summary (could be displayed somewhere)
    console.log('Preferences:', { servings, restrictions });
}

// Listen for form changes
document.addEventListener('change', function(e) {
    if (e.target.matches('#servings, input[type="checkbox"]')) {
        updatePreferenceSummary();
    }
});

