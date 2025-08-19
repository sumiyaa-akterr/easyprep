<?php
session_start();
include 'connect.php';

// Initialize message variable
$message = '';

// SIGN IN
if(isset($_POST['signIn'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password);

    // Use prepared statement for security
    $sql = "SELECT * FROM users WHERE email=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        
        // Set session variables - just use email since that's what we have
        $_SESSION['email'] = $row['email'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Not Found, Incorrect Email or Password";
    }
}

// SIGN UP
if(isset($_POST['signUp'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password);

    $checkEmail = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $message = "Email Address Already Exists!";
    } else {
        $insertQuery = "INSERT INTO users(email,password) VALUES (?,?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ss", $email, $password);
        if($stmt->execute()){
            $message = "Registration successful! Please log in.";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In to your EasyPrep Account</title>
    <link rel="stylesheet" href="styles/login.css?v=1.1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Backup inline styles for logo */
        .logo-section .logo,
        .logo-section img.logo,
        header .logo-section .logo,
        header .logo-section img.logo,
        .logo {
            width: 80px !important;
            height: 55px !important;
            max-width: 80px !important;
            max-height: 55px !important;
            border-radius: 8px !important;
            object-fit: contain !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-inner">
            <div class="logo-section">
                <a href="index.php">
                    <img src="images/easyprep-logo.png" alt="EasyPrep Logo" class="logo">
                </a>
            </div>
            <nav>
                <a href="index.php#how-it-works">How it Works</a>
                <a href="index.php#meal-plans">Meal Plans</a>
                <a href="index.php#grocery-delivery">Grocery Delivery</a>
                <a href="index.php#recipes">Recipes</a>
                <a href="index.php#pricing">Pricing</a>
                <a href="index.php#about">About</a>
            </nav>
            <div class="header-actions">
                <a href="login.php" class="login-btn">Log in</a>
            </div>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <div class="login-container">
        <div class="login-box">
            <?php if(!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Sign In Form -->
            <div class="form-section" id="signIn">
                <h2>Sign In</h2>
                <form method="post" class="login-form">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <button type="submit" name="signIn" class="btn">Sign In</button>
                </form>
                <p class="switch-text">Don't have an account? <a href="#" id="signUpButton">Sign Up</a></p>
            </div>
            
            <!-- Sign Up Form -->
            <div class="form-section" id="signUp" style="display: none;">
                <h2>Sign Up</h2>
                <form method="post" class="login-form">
                    <div class="form-group">
                        <label for="signup-email">Email:</label>
                        <input type="email" name="email" id="signup-email" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-password">Password:</label>
                        <input type="password" name="password" id="signup-password" required>
                    </div>
                    <button type="submit" name="signUp" class="btn">Sign Up</button>
                </form>
                <p class="switch-text">Already have an account? <a href="#" id="signInButton">Sign In</a></p>
            </div>
        </div>
        
        <!-- Decorative Image Section -->
        <div class="login-image-section">
            <div class="image-content">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Beautiful food background" class="background-image">
                <div class="image-overlay">
                    <h2>Start your culinary journey</h2>
                    <p>Join thousands of families who are already enjoying stress-free meal planning with EasyPrep</p>
                    <div class="benefits">
                        <div class="benefit-item">
                            <i class="fas fa-utensils"></i>
                            <span>Personalized meal plans</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Fresh grocery delivery</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-clock"></i>
                            <span>Save time cooking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="images/easyprep-logo.png" alt="EasyPrep Logo" class="footer-logo-img">
                    <span>EasyPrep</span>
                </div>
                <p>Making meal planning simple, delicious, and stress-free for everyone.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Product</h4>
                <a href="index.php#meal-plans">Meal Plans</a>
                <a href="index.php#grocery-delivery">Grocery Delivery</a>
                <a href="index.php#recipes">Recipes</a>
                <a href="index.php#pricing">Pricing</a>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <a href="#">Help Center</a>
                <a href="#">Contact Us</a>
                <a href="#">FAQ</a>
                <a href="#">Live Chat</a>
            </div>
            <div class="footer-section">
                <h4>Company</h4>
                <a href="index.php#about">About Us</a>
                <a href="#">Careers</a>
                <a href="#">Blog</a>
                <a href="#">Press</a>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
                <a href="#">Refund Policy</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 EasyPrep. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </footer>

    <script src="scripts/login.js"></script>
</body>
</html>