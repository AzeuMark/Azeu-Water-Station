<?php
/**
 * Azeu Water Station - Landing Page
 * Main public entry point with Home, Contact Us, and About Us sections
 */
session_start();

require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'config/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirects = [
        'customer' => 'customer/dashboard.php',
        'rider' => 'rider/dashboard.php',
        'staff' => 'staff/dashboard.php',
        'admin' => 'admin/dashboard.php',
        'super_admin' => 'admin/dashboard.php'
    ];
    $role = $_SESSION['role'] ?? 'customer';
    header('Location: ' . ($redirects[$role] ?? 'login.php'));
    exit;
}

$station_name = get_setting('station_name') ?? 'Azeu Water Station';
$station_address = get_setting('station_address') ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($station_name); ?> - Pure Clean Water Delivered</title>
    <meta name="description" content="<?php echo htmlspecialchars($station_name); ?> — Your trusted water refilling station. Order clean, safe drinking water delivered right to your doorstep.">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

    <!-- ========== NAVIGATION ========== -->
    <nav class="landing-nav" id="landing-nav">
        <div class="nav-container">
            <a href="#home" class="nav-brand">
                <div class="nav-brand-icon">
                    <img src="images/system/logo-1.png" alt="<?php echo htmlspecialchars($station_name); ?> Logo">
                </div>
                <span class="nav-brand-text"><?php echo htmlspecialchars($station_name); ?></span>
            </a>
            
            <ul class="nav-links" id="nav-links">
                <li><a href="#home" class="active">Home</a></li>
                <li><a href="#contact">Contact Us</a></li>
                <li><a href="#about">About Us</a></li>
                <li>
                    <button class="theme-toggle nav-theme-btn" title="Toggle theme" aria-label="Toggle theme">
                        <span class="material-icons">dark_mode</span>
                    </button>
                </li>
                <li><a href="login.php" class="nav-login-btn"><span class="material-icons">login</span> Login</a></li>
            </ul>
            
            <div class="nav-right-mobile">
                <button class="theme-toggle nav-theme-btn" id="nav-theme-mobile" title="Toggle theme" aria-label="Toggle theme">
                    <span class="material-icons">dark_mode</span>
                </button>
                <button class="nav-hamburger" id="nav-hamburger" aria-label="Toggle menu">
                    <span class="material-icons">menu</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- ========== HERO SECTION ========== -->
    <section class="hero-section" id="home">
        <div class="hero-ripple"></div>
        <div class="hero-ripple"></div>
        <div class="hero-ripple"></div>
        
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <span class="material-icons">verified</span>
                    Trusted Water Refilling Station
                </div>
                <h1 class="hero-title">
                    Pure, Clean Water<br>
                    <span class="highlight">Delivered to Your Doorstep</span>
                </h1>
                <p class="hero-description">
                    Experience hassle-free water delivery from <?php echo htmlspecialchars($station_name); ?>. 
                    Order online, track in real-time, and enjoy safe, purified water 
                    for your home and business.
                </p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn-hero-primary">
                        <span class="material-icons">person_add</span>
                        Get Started
                    </a>
                    <a href="login.php" class="btn-hero-secondary">
                        <span class="material-icons">login</span>
                        Sign In
                    </a>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="hero-visual-card">
                    <div class="hero-icon-large">
                        <img src="images/system/logo-1.png" alt="<?php echo htmlspecialchars($station_name); ?>" class="hero-logo-img">
                    </div>
                    <h2 style="color: white; font-size: 1.25rem; margin-bottom: 8px;">Order Water Anytime</h2>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin: 0;">Fast delivery &bull; Pickup available &bull; Track your order</p>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <span class="hero-stat-number">24/7</span>
                            <span class="hero-stat-label">Online Ordering</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">Fast</span>
                            <span class="hero-stat-label">Delivery</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">100%</span>
                            <span class="hero-stat-label">Safe & Clean</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">Easy</span>
                            <span class="hero-stat-label">Order Tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== FEATURES SECTION ========== -->
    <section class="features-section" id="features">
        <div class="section-container">
            <div class="section-header fade-in-up">
                <span class="section-badge">Why Choose Us</span>
                <h2 class="section-title">Everything You Need in One Place</h2>
                <p class="section-subtitle">
                    From ordering to delivery, we've built a seamless experience 
                    so you can focus on what matters most.
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon blue">
                        <span class="material-icons">local_shipping</span>
                    </div>
                    <h3>Home Delivery</h3>
                    <p>Get purified water delivered straight to your doorstep by our dedicated riders.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon green">
                        <span class="material-icons">shopping_cart</span>
                    </div>
                    <h3>Easy Ordering</h3>
                    <p>Place orders in just a few clicks with our intuitive and user-friendly interface.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon orange">
                        <span class="material-icons">track_changes</span>
                    </div>
                    <h3>Real-Time Tracking</h3>
                    <p>Monitor your order status from confirmation to delivery in real time.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon purple">
                        <span class="material-icons">verified_user</span>
                    </div>
                    <h3>Quality Assured</h3>
                    <p>Every drop is purified and quality-tested to ensure safe drinking water.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== HOW IT WORKS ========== -->
    <section class="how-it-works-section">
        <div class="section-container">
            <div class="section-header fade-in-up">
                <span class="section-badge">How It Works</span>
                <h2 class="section-title">Get Water in 4 Simple Steps</h2>
                <p class="section-subtitle">
                    Our streamlined process makes ordering water as easy as 1-2-3-4.
                </p>
            </div>
            
            <div class="steps-grid">
                <div class="step-card fade-in-up">
                    <div class="step-number">
                        <span class="material-icons">person_add</span>
                    </div>
                    <span class="step-label">Step 1</span>
                    <h3>Create Account</h3>
                    <p>Register for free and set up your delivery addresses.</p>
                </div>
                
                <div class="step-card fade-in-up">
                    <div class="step-number">
                        <span class="material-icons">add_shopping_cart</span>
                    </div>
                    <span class="step-label">Step 2</span>
                    <h3>Place Order</h3>
                    <p>Choose your products and select delivery or pickup.</p>
                </div>
                
                <div class="step-card fade-in-up">
                    <div class="step-number">
                        <span class="material-icons">directions_bike</span>
                    </div>
                    <span class="step-label">Step 3</span>
                    <h3>We Deliver</h3>
                    <p>Our rider picks up your order and heads to your location.</p>
                </div>
                
                <div class="step-card fade-in-up">
                    <div class="step-number">
                        <span class="material-icons">check_circle</span>
                    </div>
                    <span class="step-label">Step 4</span>
                    <h3>Enjoy!</h3>
                    <p>Receive your water and confirm delivery right from the app.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== SERVICES SECTION ========== -->
    <section class="services-section">
        <div class="section-container">
            <div class="section-header fade-in-up">
                <span class="section-badge">Our Services</span>
                <h2 class="section-title">What We Offer</h2>
                <p class="section-subtitle">
                    Comprehensive water refilling services designed to meet your every need.
                </p>
            </div>
            
            <div class="services-grid">
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <span class="material-icons">water_drop</span>
                    </div>
                    <div class="service-content">
                        <h3>Water Refilling</h3>
                        <p>Purified and mineral water available in various container sizes — from slim gallons to 5-gallon containers.</p>
                    </div>
                </div>
                
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <span class="material-icons">delivery_dining</span>
                    </div>
                    <div class="service-content">
                        <h3>Delivery Service</h3>
                        <p>Fast and reliable door-to-door delivery with real-time tracking and cash-on-delivery payment.</p>
                    </div>
                </div>
                
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <span class="material-icons">storefront</span>
                    </div>
                    <div class="service-content">
                        <h3>Walk-in Pickup</h3>
                        <p>Prefer to pick up? Place your order online and pick it up at our station at your convenience.</p>
                    </div>
                </div>
                
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <span class="material-icons">receipt_long</span>
                    </div>
                    <div class="service-content">
                        <h3>Digital Receipts</h3>
                        <p>Get digital receipts with QR codes for every transaction. Download as PDF or image anytime.</p>
                    </div>
                </div>
                
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <span class="material-icons">notifications_active</span>
                    </div>
                    <div class="service-content">
                        <h3>Order Notifications</h3>
                        <p>Stay informed at every step with real-time notifications about your order status.</p>
                    </div>
                </div>
                
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <span class="material-icons">manage_accounts</span>
                    </div>
                    <div class="service-content">
                        <h3>Account Management</h3>
                        <p>Manage your profile, addresses, and order history all in one secure dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== CONTACT US SECTION ========== -->
    <section class="contact-section" id="contact">
        <div class="section-container">
            <div class="section-header fade-in-up">
                <span class="section-badge">Get In Touch</span>
                <h2 class="section-title">Contact Us</h2>
                <p class="section-subtitle">
                    Have questions or need assistance? We're here to help. 
                    Reach out through any of the channels below.
                </p>
            </div>
            
            <!-- Contact Info Cards -->
            <div class="contact-cards-row fade-in-up">
                <div class="contact-card">
                    <div class="contact-card-icon blue">
                        <span class="material-icons">location_on</span>
                    </div>
                    <div class="contact-card-content">
                        <h4>Station Address</h4>
                        <p><?php echo $station_address ? nl2br(htmlspecialchars($station_address)) : 'Address not set'; ?></p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-card-icon green">
                        <span class="material-icons">phone</span>
                    </div>
                    <div class="contact-card-content">
                        <h4>Phone Number</h4>
                        <p>+63 912 345 6789<br>+63 (02) 8123 4567</p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-card-icon orange">
                        <span class="material-icons">email</span>
                    </div>
                    <div class="contact-card-content">
                        <h4>Email Address</h4>
                        <p>info@azeuwater.com<br>support@azeuwater.com</p>
                    </div>
                </div>

                <div class="contact-card">
                    <div class="contact-card-icon purple">
                        <span class="material-icons">chat</span>
                    </div>
                    <div class="contact-card-content">
                        <h4>Social Media</h4>
                        <p>Follow us on Facebook and Instagram<br>@AzeuWaterStation</p>
                    </div>
                </div>
            </div>

            <!-- Business Hours -->
            <div class="hours-card fade-in-up">
                <h3>
                    <span class="material-icons">schedule</span>
                    Business Hours
                </h3>
                <div class="hours-grid">
                    <div class="hours-item">
                        <span class="hours-day">Monday</span>
                        <span class="hours-time">7:00 AM — 7:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="hours-day">Tuesday</span>
                        <span class="hours-time">7:00 AM — 7:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="hours-day">Wednesday</span>
                        <span class="hours-time">7:00 AM — 7:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="hours-day">Thursday</span>
                        <span class="hours-time">7:00 AM — 7:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="hours-day">Friday</span>
                        <span class="hours-time">7:00 AM — 7:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="hours-day">Saturday</span>
                        <span class="hours-time">8:00 AM — 5:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="hours-day">Sunday</span>
                        <span class="hours-time closed">Closed</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== ABOUT US SECTION ========== -->
    <section class="about-section" id="about">
        <div class="section-container">
            <div class="section-header fade-in-up">
                <span class="section-badge">Who We Are</span>
                <h2 class="section-title">About Us</h2>
                <p class="section-subtitle">
                    Committed to delivering safe, clean, and affordable water to every Filipino household.
                </p>
            </div>
            
            <!-- About Content -->
            <div class="about-content fade-in-up">
                <div class="about-text">
                    <h3>Our Story</h3>
                    <p>
                        <?php echo htmlspecialchars($station_name); ?> was founded with a simple but powerful mission: to provide 
                        every community with access to safe, clean, and affordable drinking water. What started as a small 
                        neighborhood refilling station has grown into a modern, technology-driven water delivery service.
                    </p>
                    <p>
                        We combine traditional Filipino values of quality and service with modern technology — 
                        featuring online ordering, real-time delivery tracking, digital receipts, and a fully 
                        managed delivery fleet to bring purified water right to your doorstep.
                    </p>
                </div>
                <div class="about-values">
                    <div class="about-value">
                        <div class="about-value-icon">
                            <span class="material-icons">science</span>
                        </div>
                        <div>
                            <h4>Multi-Stage Purification</h4>
                            <p>Our water goes through rigorous filtration and purification processes to meet the highest safety standards.</p>
                        </div>
                    </div>
                    <div class="about-value">
                        <div class="about-value-icon">
                            <span class="material-icons">speed</span>
                        </div>
                        <div>
                            <h4>Fast & Reliable</h4>
                            <p>Our dedicated rider network ensures your water is delivered promptly and efficiently.</p>
                        </div>
                    </div>
                    <div class="about-value">
                        <div class="about-value-icon">
                            <span class="material-icons">devices</span>
                        </div>
                        <div>
                            <h4>Technology-Driven</h4>
                            <p>Manage everything online — from placing orders to tracking deliveries and viewing receipts.</p>
                        </div>
                    </div>
                    <div class="about-value">
                        <div class="about-value-icon">
                            <span class="material-icons">favorite</span>
                        </div>
                        <div>
                            <h4>Community First</h4>
                            <p>We're committed to serving our community with affordable pricing and exceptional customer care.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mission / Vision -->
            <div class="mission-vision fade-in-up">
                <div class="mv-card">
                    <div class="mv-card-icon blue">
                        <span class="material-icons">flag</span>
                    </div>
                    <h3>Our Mission</h3>
                    <p>To provide safe, clean, and affordable purified water to every household and business in our community through reliable delivery and excellent customer service.</p>
                </div>
                <div class="mv-card">
                    <div class="mv-card-icon teal">
                        <span class="material-icons">visibility</span>
                    </div>
                    <h3>Our Vision</h3>
                    <p>To become the leading water refilling station in the Philippines, recognized for innovation, quality, and a genuine commitment to public health and well-being.</p>
                </div>
            </div>
            
            <!-- Team -->
            <div class="team-section fade-in-up">
                <h3>Meet the Team</h3>
                <div class="team-grid">
                    <div class="team-card">
                        <div class="team-avatar creator">UV</div>
                        <span class="team-role">Founder & Lead Developer</span>
                        <h4>Uelmark G. Valdehueza</h4>
                        <p class="team-aka">"Azeu"</p>
                        <p>Visionary behind <?php echo htmlspecialchars($station_name); ?>. Designed and built the entire system from the ground up to modernize water delivery services.</p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar co-creator">VB</div>
                        <span class="team-role co">Co-Founder & Technical Advisor</span>
                        <h4>Vernelle Jhay Balatayo</h4>
                        <p class="team-aka">"Nexile"</p>
                        <p>Provided technical architecture guidance and helped bring the full-stack system to life with professional-grade code and best practices.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== CTA SECTION ========== -->
    <section class="cta-section">
        <div class="cta-content fade-in-up">
            <h2>Ready to Order Clean Water?</h2>
            <p>Join hundreds of customers who trust <?php echo htmlspecialchars($station_name); ?> for their daily water needs. Sign up today and place your first order!</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn-hero-primary">
                    <span class="material-icons">person_add</span>
                    Create Free Account
                </a>
                <a href="login.php" class="btn-hero-secondary">
                    <span class="material-icons">login</span>
                    Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer class="landing-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-brand">
                        <div class="footer-brand-icon">
                            <img src="images/system/logo-1.png" alt="<?php echo htmlspecialchars($station_name); ?> Logo">
                        </div>
                        <span class="footer-brand-text"><?php echo htmlspecialchars($station_name); ?></span>
                    </div>
                    <p>Your trusted source for safe, clean, and purified drinking water. Serving our community with quality products and reliable delivery since day one.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home"><span class="material-icons">chevron_right</span> Home</a></li>
                        <li><a href="#features"><span class="material-icons">chevron_right</span> Features</a></li>
                        <li><a href="#contact"><span class="material-icons">chevron_right</span> Contact Us</a></li>
                        <li><a href="#about"><span class="material-icons">chevron_right</span> About Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="login.php"><span class="material-icons">chevron_right</span> Sign In</a></li>
                        <li><a href="register.php"><span class="material-icons">chevron_right</span> Register</a></li>
                        <li><a href="forgot_password.php"><span class="material-icons">chevron_right</span> Forgot Password</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($station_name); ?>. All rights reserved.</p>
                <p>Crafted with <span style="color: var(--danger);">&hearts;</span> by Azeu &amp; Nexile</p>
            </div>
        </div>
    </footer>

    <!-- Global JS (theme engine) -->
    <script src="assets/js/global.js"></script>
    
    <!-- Landing Page JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const nav = document.getElementById('landing-nav');
        const hamburger = document.getElementById('nav-hamburger');
        const navLinks = document.getElementById('nav-links');
        const links = navLinks.querySelectorAll('a:not(.nav-login-btn)');
        
        // Sticky navbar on scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
            
            // Active section highlighting
            const sections = document.querySelectorAll('section[id]');
            let current = '';
            sections.forEach(function(section) {
                const top = section.offsetTop - 120;
                if (window.scrollY >= top) {
                    current = section.getAttribute('id');
                }
            });
            
            links.forEach(function(link) {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
        
        // Mobile hamburger toggle
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('open');
            var icon = hamburger.querySelector('.material-icons');
            icon.textContent = navLinks.classList.contains('open') ? 'close' : 'menu';
        });
        
        // Close mobile menu on link click
        navLinks.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navLinks.classList.remove('open');
                hamburger.querySelector('.material-icons').textContent = 'menu';
            });
        });
        
        // Scroll-triggered fade-in animations
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        
        document.querySelectorAll('.fade-in-up').forEach(function(el) {
            observer.observe(el);
        });
    });
    </script>
</body>
</html>
