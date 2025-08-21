// Meal Plan JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM loaded, calling initializeMealPlan...');
    try {
        initializeMealPlan();
        console.log('✅ initializeMealPlan completed successfully');
    } catch (error) {
        console.error('❌ Error in initializeMealPlan:', error);
    }
});

function initializeMealPlan() {
    console.log('🚀 Starting initializeMealPlan...');
    
    try {
        // Initialize user menu dropdown
        console.log('📋 Initializing user menu...');
        initializeUserMenu();
        
        // Initialize mobile menu
        console.log('📱 Initializing mobile menu...');
        initializeMobileMenu();
        
        // Initialize form interactions
        console.log('📝 Initializing form interactions...');
        initializeFormInteractions();
        
        // Initialize receipt navigation
        console.log('🧭 Initializing receipt navigation...');
        initializeReceiptNavigation();
        
        console.log('✅ All initializations completed');
    } catch (error) {
        console.error('❌ Error during initialization:', error);
    }
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
    console.log('🔍 Starting form interactions setup...');
    
    // Add smooth animations to checkboxes
    const checkboxItems = document.querySelectorAll('.checkbox-item');
    console.log('📋 Found checkbox items:', checkboxItems.length);
    
    if (checkboxItems.length === 0) {
        console.log('❌ No checkbox items found! This is the problem.');
        console.log('🔍 Looking for elements with class "checkbox-item"...');
        const allElements = document.querySelectorAll('*');
        console.log('📊 Total elements on page:', allElements.length);
        
        // Check if the form even exists
        const form = document.querySelector('.preferences-form');
        console.log('📝 Form found:', form);
        
        if (form) {
            console.log('📋 Form HTML:', form.innerHTML.substring(0, 200) + '...');
        }
        return;
    }
    
    checkboxItems.forEach((item, index) => {
        console.log(`📦 Setting up checkbox item ${index}:`, item);
        
        // Get the checkbox within this item
        const checkbox = item.querySelector('input[type="checkbox"]');
        console.log(`🔘 Checkbox ${index}:`, checkbox);
        
        if (checkbox) {
            console.log(`✅ Setting up event listeners for checkbox ${index}:`, checkbox.name);
            // Add change event listener to the checkbox
            checkbox.addEventListener('change', function() {
                console.log(`🎯 Change event fired for checkbox ${index}:`, this.name, '=', this.checked);
                
                try {
                    // Update visual feedback
                    if (this.checked) {
                        item.style.background = '#fff6e0';
                        item.style.borderColor = '#8d5524';
                        item.style.fontWeight = '600';
                        console.log(`🎨 Applied checked styles to item ${index}`);
                    } else {
                        item.style.background = '#f8fafc';
                        item.style.borderColor = 'transparent';
                        item.style.fontWeight = '400';
                        console.log(`🎨 Applied unchecked styles to item ${index}`);
                    }
                    
                    // Call update function
                    updatePreferenceSummary();
                } catch (error) {
                    console.error(`❌ Error updating visual feedback for checkbox ${index}:`, error);
                }
            });
            
            // Set initial visual state
            if (checkbox.checked) {
                item.style.background = '#fff6e0';
                item.style.borderColor = '#8d5524';
            }
            
            // Also add click event to the label for better UX
            item.addEventListener('click', function(e) {
                try {
                    console.log(`🖱️ Label clicked for checkbox ${index}`);
                    
                    // Don't trigger twice if clicking directly on checkbox
                    if (e.target === checkbox) return;
                    
                    // Toggle the checkbox
                    checkbox.checked = !checkbox.checked;
                    console.log(`🔄 Toggled checkbox ${index} to:`, checkbox.checked);
                    
                    // Trigger the change event
                    checkbox.dispatchEvent(new Event('change'));
                } catch (error) {
                    console.error(`❌ Error handling click for checkbox ${index}:`, error);
                }
            });
        }
    });
    
    // Set up form submission
    const form = document.querySelector('.preferences-form');
    console.log('🔍 Form found:', form);
    
    if (form) {
        console.log('✅ Form found, setting up submit handler...');
        
        form.addEventListener('submit', function(e) {
            console.log('🎯 FORM SUBMITTING!');
            
            // Validate form
            if (!validateForm()) {
                console.log('❌ Form validation failed');
                e.preventDefault();
                return;
            }
            
            console.log('✅ Form validation passed - allowing submission');
            // Allow form submission
            this.submit();
        });
        
        console.log('✅ Form submit handler set up successfully');
    } else {
        console.log('❌ Form not found!');
    }
}

function initializeReceiptNavigation() {
    let currentDay = 0;
    const totalDays = document.querySelectorAll('.day-receipt').length;
    
    // Set up navigation dots
    const navDots = document.querySelectorAll('.nav-dot');
    navDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showDay(index);
        });
    });
    
    // Set up navigation arrows
    const prevBtn = document.querySelector('.nav-prev');
    const nextBtn = document.querySelector('.nav-next');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentDay > 0) {
                showDay(currentDay - 1);
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (currentDay < totalDays - 1) {
                showDay(currentDay + 1);
            }
        });
    }
    
    // Click anywhere on the receipt to go to next day
    const receiptPaper = document.getElementById('receipt-paper');
    if (receiptPaper) {
        receiptPaper.addEventListener('click', (e) => {
            // Ignore clicks on nav controls themselves
            const target = e.target;
            if ((target.closest && target.closest('.nav-arrow')) || (target.classList && target.classList.contains('nav-dot'))) {
                return;
            }
            const nextIndex = currentDay < totalDays - 1 ? currentDay + 1 : 0;
            showDay(nextIndex);
        });
    }
    
    function showDay(dayIndex) {
        // Hide all days
        document.querySelectorAll('.day-receipt').forEach(day => {
            day.classList.remove('active');
        });
        
        // Show selected day
        const selectedDay = document.querySelector(`[data-day="${dayIndex}"]`);
        if (selectedDay) {
            selectedDay.classList.add('active');
        }
        
        // Update navigation dots
        navDots.forEach((dot, index) => {
            dot.classList.toggle('active', index === dayIndex);
        });
        
        // Update arrow states
        if (prevBtn) prevBtn.disabled = dayIndex === 0;
        if (nextBtn) nextBtn.disabled = dayIndex === totalDays - 1;
        
        currentDay = dayIndex;
    }
}

function validateForm() {
    console.log('🔍 Validating form...');
    
    const servings = document.getElementById('servings').value;
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    
    console.log('🍽️ Servings:', servings);
    console.log('☑️ Checked checkboxes:', checkboxes.length);
    console.log('📋 Checked names:', Array.from(checkboxes).map(cb => cb.name));
    
    if (!servings || servings < 1) {
        console.log('❌ Servings validation failed');
        alert('Please select the number of servings.');
        return false;
    }
    
    if (checkboxes.length === 0) {
        console.log('❌ Checkboxes validation failed');
        alert('Please select at least one dietary preference or restriction.');
        return false;
    }
    
    console.log('✅ Form validation passed');
    return true;
}

function updatePreferenceSummary() {
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
    const restrictions = Array.from(checkedBoxes).map(cb => {
        const textSpan = cb.closest('.checkbox-item').querySelector('.text');
        return textSpan ? textSpan.textContent : cb.name;
    });
    
    // You can add a summary display here if needed
}

function downloadMealPlan() {
    // Implementation for downloading meal plan
    alert('Download functionality coming soon!');
}

function generateGroceryList() {
    // Implementation for generating grocery list
    alert('Grocery list functionality coming soon!');
}

function testCheckboxes() {
    console.log('🧪 Testing checkboxes...');
    
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    console.log('📋 Total checkboxes found:', checkboxes.length);
    
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
    console.log('☑️ Checked checkboxes:', checkedBoxes.length);
    
    checkedBoxes.forEach((cb, i) => {
        console.log(`✅ Checked ${i}:`, cb.name, '=', cb.checked);
    });
    
    // Test visual feedback
    const checkboxItems = document.querySelectorAll('.checkbox-item');
    checkboxItems.forEach((item, index) => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        if (checkbox && checkbox.checked) {
            console.log(`🎨 Item ${index} should have checked styles`);
            console.log(`   Background:`, item.style.background);
            console.log(`   Border:`, item.style.borderColor);
        }
    });
}

// Global functions for onclick handlers
function previousDay() {
    const prevBtn = document.querySelector('.nav-prev');
    if (prevBtn && !prevBtn.disabled) {
        prevBtn.click();
    }
}

function nextDay() {
    const nextBtn = document.querySelector('.nav-next');
    if (nextBtn && !nextBtn.disabled) {
        nextBtn.click();
    }
}
