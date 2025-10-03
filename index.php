<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to BRSMS - Barangay Resource Sharing Management System</title>

    <!-- Favicon (browser tab logo) -->
    <link rel="icon" type="image/png" href="uploads/BRSMS.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
.navbar-brand   {
                    display: flex;
                    align-items: center;
                    font-weight: bold;
                    color: #6a11cb;
                    font-size: 1.25rem;
                    text-decoration: none;
                }

                .navbar-brand img {
                    height: 40px; /* adjust as needed */
                    margin-right: 8px;
                }

                .brand-text {
                    display: flex;
                    flex-direction: column;
                    line-height: 1.2;
                }

                .navbar-brand .version {
                    font-size: 0.75rem;
                    font-weight: normal;
                    margin-left: 4px;
                    color: #666;
                }

                .env-label {
                    font-size: 0.7rem;
                    font-weight: bold;
                    color: #14033dff; /* red to highlight TEST SERVER */
                    text-transform: uppercase;
                }

        :root {
            /* Purple Gauge Theme Colors */
            --primary-purple: #673ab7; /* Deep Purple */
            --secondary-purple: #9c27b0; /* Medium Purple */
            --light-purple: #ede7f6; /* Very Light Purple */
            --accent-teal: #00bcd4; /* Teal for accent, complements purple */
            
            /* Existing Neutral Colors */
            --dark-text: #212529;
            --light-text: #6c757d;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            
            /* Shadows and Animations */
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --hover-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); /* Enhanced hover shadow */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-text);
            overflow-x: hidden;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased; /* Smoother font rendering */
        }

        /* Header and Navigation */
        .top-bar {
            background-color: var(--primary-purple);
            color: var(--white);
            padding: 8px 0;
            font-size: 0.9rem;
            transition: background-color 0.3s ease; /* Smooth transition */
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .official-seal {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .official-seal img {
            height: 30px;
            animation: rotateIn 0.8s ease-out; /* Added animation */
        }

        .government-links a {
            color: var(--white);
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.85rem;
            transition: color 0.3s ease, text-decoration 0.3s ease; /* Smooth transition */
        }

        .government-links a:hover {
            text-decoration: underline;
            color: var(--accent-teal); /* Highlight on hover */
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow);
            padding: 15px 0;
            position: sticky; /* Sticky navigation */
            top: 0;
            z-index: 1000;
            transition: padding 0.3s ease, box-shadow 0.3s ease; /* Smooth transition for sticky effect */
        }

        .navbar.scrolled {
            padding: 10px 0;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12); /* Deeper shadow when scrolled */
        }

        .navbar-container {
            display: flex;
            /* Changed from flex-end to space-between */
            justify-content: space-between; /* Distributes items with space between them */
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--primary-purple);
            font-weight: 700;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .navbar-brand img {
            height: 50px;
            margin-right: 12px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.05); /* Subtle scale on logo hover */
        }

        .nav-menu {
            display: flex; /* Keep this for desktop */
            list-style: none;
            gap: 30px;
            margin: 0;
        }

        .nav-link {
            color: var(--dark-text);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 5px 0;
            transition: color 0.3s ease;
        }

        .nav-link:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-purple);
            transition: width 0.3s ease;
        }

        .nav-link:hover:after {
            width: 100%;
        }

        .nav-link:hover {
            color: var(--primary-purple);
        }

        .system-access-btn {
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(103, 58, 183, 0.3); /* Adjusted shadow color */
            overflow: hidden; /* For button hover effect */
            position: relative;
            z-index: 1;
        }

        .system-access-btn:before { /* Hover background effect */
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-purple), var(--primary-purple));
            transition: left 0.3s ease;
            z-index: -1;
        }

        .system-access-btn:hover:before {
            left: 0;
        }

        .system-access-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(103, 58, 183, 0.4); /* Adjusted shadow color */
            color: white; /* Ensure text color remains white */
        }

        .hamburger {
            display: none;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-purple);
            transition: transform 0.3s ease;
        }

        .hamburger.active {
            transform: rotate(90deg); /* Rotate hamburger icon when active */
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(103, 58, 183, 0.85), rgba(103, 58, 183, 0.95)), url('https://images.unsplash.com/photo-1577720643272-265f0936742c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 80px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 1000px;
            margin: 0 auto;
            opacity: 0; /* Initial state for animation */
            transform: translateY(20px); /* Initial state for animation */
            animation: fadeInSlideUp 1s ease-out forwards; /* Animation on load */
            animation-delay: 0.3s;
        }

        .hero-badge {
            display: inline-block;
            background-color: var(--accent-teal);
            color: var(--dark-text);
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 500;
            margin-bottom: 20px;
            font-size: 0.9rem;
            animation: scaleIn 0.6s ease-out forwards; /* Added animation */
            animation-delay: 0.6s;
            opacity: 0;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.9;
        }

        .hero-cta {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .hero-cta .system-access-btn {
            animation: fadeIn 0.8s ease-out forwards; /* Added animation */
            animation-delay: 1s;
            opacity: 0;
        }

        /* Features Section */
        .features-section {
            padding: 80px 20px;
            background-color: var(--white);
        }

        .section-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 60px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .section-header.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .section-badge {
            display: inline-block;
            background-color: var(--light-purple);
            color: var(--primary-purple);
            padding: 6px 16px;
            border-radius: 4px;
            font-weight: 500;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-purple);
            margin-bottom: 20px;
        }

        .section-subtitle {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--white);
            border-radius: 8px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border-top: 4px solid var(--primary-purple);
            height: 100%;
            opacity: 0; /* Initial state for animation */
            transform: translateY(20px); /* Initial state for animation */
        }

        .feature-card.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-card:hover {
            transform: translateY(-8px); /* Increased lift on hover */
            box-shadow: var(--hover-shadow); /* Enhanced hover shadow */
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: var(--light-purple);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--primary-purple);
            font-size: 24px;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1); /* Icon subtle scale on card hover */
            background-color: var(--primary-purple); /* Icon background change on hover */
            color: var(--white);
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-purple);
        }

        .feature-description {
            color: var(--light-text);
            margin-bottom: 20px;
        }

        .feature-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .badge {
            background-color: var(--light-purple);
            color: var(--primary-purple);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .feature-card:hover .badge {
            background-color: var(--primary-purple);
            color: var(--white);
        }

        /* Stats Section */
        .stats-section {
            background-color: var(--primary-purple);
            color: var(--white);
            padding: 60px 20px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            opacity: 0; /* Initial state for animation */
            transform: translateY(20px); /* Initial state for animation */
        }

        .stat-item.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--accent-teal);
            transition: transform 0.3s ease;
        }

        .stat-item:hover .stat-number {
            transform: scale(1.05); /* Subtle scale on number hover */
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* How It Works Section */
        .process-section {
            padding: 80px 20px;
            background-color: var(--light-gray);
        }

        .section-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 60px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .section-header.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .section-badge {
            display: inline-block;
            background-color: var(--light-purple);
            color: var(--primary-purple);
            padding: 6px 16px;
            border-radius: 4px;
            font-weight: 500;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-purple);
            margin-bottom: 20px;
        }

        .section-subtitle {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .process-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .process-step {
            background: var(--white);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow);
            position: relative;
            transition: all 0.3s ease;
            opacity: 0; /* Initial state for animation */
            transform: translateY(20px); /* Initial state for animation */
        }

        .process-step.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .process-step:hover {
            transform: translateY(-8px); /* Increased lift on hover */
            box-shadow: var(--hover-shadow); /* Enhanced hover shadow */
        }

        .step-number {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background-color: var(--primary-purple);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .process-step:hover .step-number {
            background-color: var(--accent-teal); /* Number background change on hover */
            transform: translateX(-50%) scale(1.1);
        }

        .step-icon {
            font-size: 2.5rem;
            color: var(--primary-purple);
            margin-bottom: 20px;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .process-step:hover .step-icon {
            color: var(--secondary-purple); /* Icon color change on hover */
            transform: scale(1.1);
        }

        .step-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-purple);
        }

        .step-description {
            color: var(--light-text);
        }

        /* Footer */
        .footer {
            background-color: var(--primary-purple);
            color: var(--white);
            padding: 60px 20px 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo {
            display: flex;
            flex-direction: column;
        }

        .footer-logo img {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .footer-logo img:hover {
            transform: rotate(10deg); /* Subtle rotation on logo hover */
        }

        .footer-logo p {
            opacity: 0.8;
            margin-top: 15px;
            max-width: 300px;
        }

        .footer-links h4 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-links h4:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--accent-teal);
            transition: width 0.3s ease;
        }

        .footer-links h4:hover:after {
            width: 60px; /* Expand underline on hover */
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent-teal);
            padding-left: 8px; /* Increased padding for more noticeable slide */
        }

        .footer-contact p {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .footer-contact i {
            margin-right: 10px;
            color: var(--accent-teal);
            transition: transform 0.3s ease;
        }

        .footer-contact p:hover i {
            transform: translateX(5px); /* Icon slide on hover */
        }

        .copyright {
            text-align: center;
            padding-top: 40px;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Keyframe Animations */
        @keyframes fadeInSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes rotateIn {
            from {
                opacity: 0;
                transform: rotate(-90deg);
            }
            to {
                opacity: 1;
                transform: rotate(0deg);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.3rem;
            }
            
            .nav-menu {
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .top-bar-content {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .government-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            
            .government-links a {
                margin: 0 5px;
            }
            
            .navbar-container {
                flex-direction: row; /* Keep items in a row for mobile header */
                justify-content: space-between; /* Space between logo and hamburger */
                align-items: center;
                /* Remove gap here as it's handled by space-between */
                gap: 0; /* Reset gap for mobile container */
            }
            
            .nav-menu {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                display: none; /* Hidden by default on mobile */
                width: 100%;
                padding-top: 10px;
                /* Position the mobile menu absolutely below the navbar */
                position: absolute;
                top: 100%; /* Position below the navbar */
                left: 0;
                background: var(--white); /* Match navbar background */
                box-shadow: var(--shadow); /* Add shadow for separation */
                z-index: 999; /* Ensure it's above other content */
                padding-bottom: 20px; /* Add some padding at the bottom */
            }
            
            .nav-menu.active {
                display: flex;
            }

            .nav-menu li {
                width: 100%;
                text-align: center;
            }

            .nav-menu .nav-link, .nav-menu .system-access-btn {
                display: block; /* Make links block level for full width tap area */
                padding: 10px 0;
            }
            
            .hamburger {
                display: block;
                /* Remove absolute positioning here as it's now part of flex layout */
                position: static; /* Reset to static */
                top: auto;
                right: auto;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .hero-cta {
                flex-direction: column;
                align-items: center;
            }
            
            .section-title {
                font-size: 1.8rem;
            }

            .footer-content {
                grid-template-columns: 1fr; /* Stack footer columns on small screens */
                text-align: center;
            }

            .footer-links h4:after {
                left: 50%;
                transform: translateX(-50%); /* Center underline for stacked columns */
            }

            .footer-logo {
                align-items: center;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .feature-card, .process-step {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Government Bar -->
    <!-- Removed the "Republic of the Philippines" section as it implies official government status -->
    <!-- <div class="top-bar">
        <div class="top-bar-content">
            <div class="official-seal">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNmZmZmZmYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWNhcD0icm91bmQiPjxwYXRoIGQ9Ik0xMiAyTDIgMTBoMTB2MTB6Ij48L3BhdGg+PHBhdGggZD0iTTEyIDJsMTAgOGgxMHYxMnoiPjwvaWF0aD48L3N2Zz4=" alt="Government Seal">
                <span>Republic of the Philippines</span>
            </div>
            <div class="government-links">
                <a href="#">Office of the President</a>
                <a href="#">Official Gazette</a>
                <a href="#">Government Portal</a>
            </div>
        </div>
    </div> -->

    <!-- Navigation -->
    <nav class="navbar" id="main-navbar">
        <div class="navbar-container">
            <!-- Moved navbar-brand to the beginning to align left -->
            <a class="navbar-brand" href="#">
                <img src="uploads/BRSMS.jpg" alt="BRSMS Logo">
                <div class="brand-text">
                    BRSMS <span class="version">v1.0</span>
                    <div class="env-label">TEST SERVER</div>
                </div>
            </a>

            <!-- The hamburger button and nav-menu will naturally align to the right
                 due to 'space-between' on the navbar-container -->
            <button class="hamburger" id="hamburger-menu" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu" id="nav-menu">
                <!-- Removed "Access the System" from topbar -->
                <!-- Add your navigation links here if needed, e.g.: -->
                <!-- <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#how-it-works" class="nav-link">How It Works</a></li>
                <li><a href="brsms/login.php" class="system-access-btn">Access System</a></li> -->
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <!-- Removed "Official Government System" badge -->
            <!-- <span class="hero-badge">Official Government System</span> -->
            <h1 class="hero-title">Barangay Resource Sharing Management System</h1>
            <p class="hero-subtitle">
                A centralized platform for efficient resource management and coordination across barangays. 
                Streamline operations, track inventory, and empower your community with our system.
            </p>
            <div class="hero-cta">
                <a href="pages/login.php" class="system-access-btn">
                    <i class="fas fa-sign-in-alt me-2"></i> Login</i>
                </a>
                <a href="#features" class="system-access-btn" style="background: transparent; border: 2px solid white;">
                    <i class="fas fa-info-circle me-2"></i> Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="section-header">
            <span class="section-badge">Key Features</span>
            <h2 class="section-title">Streamlining Barangay Resource Management</h2>
            <p class="section-subtitle">Our comprehensive system provides all the tools needed for efficient resource coordination and management</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="feature-title">Inventory Management</h3>
                <p class="feature-description">
                    Track and manage barangay resources efficiently with our comprehensive inventory system. Maintain accurate records of all assets and supplies.
                </p>
                <div class="feature-badges">
                    <span class="badge">Asset Tracking</span>
                    <span class="badge">Stock Management</span>
                    <span class="badge">Reporting</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="feature-title">Inter-Barangay Resource Network</h3>
                <p class="feature-description">
                    Enable efficient sharing of resources between barangays. Coordinate equipment, supplies, and personnel across communities.
                </p>
                <div class="feature-badges">
                    <span class="badge">Equipment Sharing</span>
                    <span class="badge">Resource Coordination</span>
                    <span class="badge">Emergency Response</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="feature-title">Reporting & Analytics</h3>
                <p class="feature-description">
                    Generate insightful reports to make data-driven decisions. Monitor resource utilization and identify areas for improvement.
                </p>
                <div class="feature-badges">
                    <span class="badge">Data Visualization</span>
                    <span class="badge">Performance Metrics</span>
                    <span class="badge">Export Capabilities</span>
                </div>
            </div>
        </div>
    </section>


    <!-- How It Works Section -->
    <section class="process-section">
        <div class="section-header">
            <span class="section-badge">Process</span>
            <h2 class="section-title">How The System Works</h2>
            <p class="section-subtitle">Simple steps to efficiently manage and share resources across barangays</p>
        </div>
        <div class="process-steps">
            <div class="process-step">
                <div class="step-number">1</div>
                <div class="step-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="step-title">Register Resources</h3>
                <p class="step-description">Catalog all available resources in your barangay inventory with detailed information</p>
            </div>
            <div class="process-step">
                <div class="step-number">2</div>
                <div class="step-icon">
                    <i class="fas fa-network-wired"></i>
                </div>
                <h3 class="step-title">Coordinate Sharing</h3>
                <p class="step-description">Request or offer resources to other barangays through the centralized network</p>
            </div>
            <div class="process-step">
                <div class="step-number">3</div>
                <div class="step-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="step-title">Track & Analyze</h3>
                <p class="step-description">Monitor resource utilization and generate reports for better decision making</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        
        <div class="copyright">
            &copy; 2023 Barangay Resource Sharing Management System (BRSMS). All rights reserved.<br>
            Designed for local barangay communities.
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile menu toggle
        document.getElementById('hamburger-menu').addEventListener('click', function() {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('active');
            this.classList.toggle('active'); // Toggle hamburger icon animation
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });

                // Close mobile menu after clicking a link
                const navMenu = document.getElementById('nav-menu');
                const hamburger = document.getElementById('hamburger-menu');
                if (navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    hamburger.classList.remove('active');
                }
            });
        });

        // Sticky Navbar on Scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('main-navbar');
            if (window.scrollY > 50) { // Adjust scroll threshold as needed
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Intersection Observer for Section Animations
        document.addEventListener('DOMContentLoaded', function() {
            const animateOnScrollElements = document.querySelectorAll('.section-header, .feature-card, .process-step, .stat-item');
            
            const observerOptions = {
                root: null, // viewport
                rootMargin: '0px',
                threshold: 0.1 // Trigger when 10% of the element is visible
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                        // For stat numbers, start counting animation
                        if (entry.target.classList.contains('stat-item')) {
                            animateNumber(entry.target.querySelector('.stat-number'));
                        }
                        observer.unobserve(entry.target); // Stop observing once animated
                    }
                });
            }, observerOptions);
            
            animateOnScrollElements.forEach(element => {
                observer.observe(element);
            });

            // Number counting animation for stats
            function animateNumber(element) {
                const target = parseInt(element.getAttribute('data-target'));
                let current = 0;
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 10); // Calculate increment based on 10ms interval

                const timer = setInterval(() => {
                    current += increment;
                    if (current < target) {
                        element.textContent = Math.floor(current) + (element.getAttribute('data-target') === '98' ? '%' : (element.getAttribute('data-target') === '24' ? '/7' : ''));
                    } else {
                        element.textContent = target + (element.getAttribute('data-target') === '98' ? '%' : (element.getAttribute('data-target') === '24' ? '/7' : ''));
                        clearInterval(timer);
                    }
                }, 10);
            }
        });
    </script>
</body>
</html>
