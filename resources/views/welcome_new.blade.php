<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Efiewura - Modern Property Rental Management Platform</title>
        <meta name="description" content="Efiewura is Ghana's premier property rental management platform. Connect landlords with tenants, manage properties, and streamline rental processes with ease.">
        <meta name="keywords" content="property rental, Ghana, landlord, tenant, property management, real estate">
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        
        <!-- Styles -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /* Custom Styles for Efiewura Landing Page */
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                    line-height: 1.6;
                    color: #1f2937;
                    overflow-x: hidden;
                }

                .gradient-bg {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }

                .hero-pattern {
                    background-color: #667eea;
                    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
                }

                .glass-card {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                }

                .feature-card {
                    transition: all 0.3s ease;
                    border: 1px solid #e5e7eb;
                }

                .feature-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                    border-color: #667eea;
                }

                .btn-primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 12px 32px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: 600;
                    display: inline-block;
                    transition: all 0.3s ease;
                    border: none;
                    cursor: pointer;
                }

                .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
                }

                .btn-secondary {
                    background: white;
                    color: #667eea;
                    padding: 12px 32px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: 600;
                    display: inline-block;
                    transition: all 0.3s ease;
                    border: 2px solid #667eea;
                }

                .btn-secondary:hover {
                    background: #667eea;
                    color: white;
                }

                .stats-counter {
                    font-size: 2.5rem;
                    font-weight: 700;
                    color: #667eea;
                }

                .navbar {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
                }

                .hero-image {
                    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23667eea' width='400' height='300'/%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M100 100h50v50h-50zM200 100h50v50h-50zM300 100h50v50h-50zM100 200h50v50h-50zM200 200h50v50h-50zM300 200h50v50h-50z'/%3E%3C/g%3E%3C/svg%3E");
                    background-size: cover;
                    background-position: center;
                }

                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 0 20px;
                }

                .grid {
                    display: grid;
                }

                .grid-cols-1 {
                    grid-template-columns: repeat(1, minmax(0, 1fr));
                }

                .grid-cols-2 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .gap-4 {
                    gap: 1rem;
                }

                .gap-8 {
                    gap: 2rem;
                }

                .gap-12 {
                    gap: 3rem;
                }

                .text-center {
                    text-align: center;
                }

                .py-20 {
                    padding-top: 5rem;
                    padding-bottom: 5rem;
                }

                .py-12 {
                    padding-top: 3rem;
                    padding-bottom: 3rem;
                }

                .mb-6 {
                    margin-bottom: 1.5rem;
                }

                .mb-4 {
                    margin-bottom: 1rem;
                }

                .mb-8 {
                    margin-bottom: 2rem;
                }

                .mb-16 {
                    margin-bottom: 4rem;
                }

                .mt-12 {
                    margin-top: 3rem;
                }

                .pt-16 {
                    padding-top: 4rem;
                }

                .px-4 {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }

                .p-8 {
                    padding: 2rem;
                }

                .w-16 {
                    width: 4rem;
                }

                .h-16 {
                    width: 4rem;
                    height: 4rem;
                }

                .h-20 {
                    height: 5rem;
                }

                .h-80 {
                    height: 20rem;
                }

                .w-20 {
                    width: 5rem;
                }

                .w-full {
                    width: 100%;
                }

                .min-h-screen {
                    min-height: 100vh;
                }

                .flex {
                    display: flex;
                }

                .items-center {
                    align-items: center;
                }

                .justify-between {
                    justify-content: space-between;
                }

                .justify-center {
                    justify-content: center;
                }

                .flex-col {
                    flex-direction: column;
                }

                .space-x-4 > * + * {
                    margin-left: 1rem;
                }

                .mx-auto {
                    margin-left: auto;
                    margin-right: auto;
                }

                .rounded-full {
                    border-radius: 9999px;
                }

                .rounded-xl {
                    border-radius: 0.75rem;
                }

                .rounded-2xl {
                    border-radius: 1rem;
                }

                .fixed {
                    position: fixed;
                }

                .top-0 {
                    top: 0;
                }

                .z-50 {
                    z-index: 50;
                }

                .h-16 {
                    height: 4rem;
                }

                .bg-gray-50 {
                    background-color: #f9fafb;
                }

                .bg-white {
                    background-color: #ffffff;
                }

                .bg-gray-900 {
                    background-color: #111827;
                }

                .bg-blue-100 {
                    background-color: #dbeafe;
                }

                .bg-green-100 {
                    background-color: #dcfce7;
                }

                .bg-purple-100 {
                    background-color: #f3e8ff;
                }

                .bg-yellow-100 {
                    background-color: #fef3c7;
                }

                .bg-red-100 {
                    background-color: #fee2e2;
                }

                .bg-indigo-100 {
                    background-color: #e0e7ff;
                }

                .bg-blue-500 {
                    background-color: #3b82f6;
                }

                .bg-green-500 {
                    background-color: #10b981;
                }

                .bg-purple-500 {
                    background-color: #8b5cf6;
                }

                .text-white {
                    color: #ffffff;
                }

                .text-gray-600 {
                    color: #4b5563;
                }

                .text-gray-700 {
                    color: #374151;
                }

                .text-gray-800 {
                    color: #1f2937;
                }

                .text-gray-400 {
                    color: #9ca3af;
                }

                .text-blue-100 {
                    color: #dbeafe;
                }

                .text-blue-600 {
                    color: #2563eb;
                }

                .text-yellow-300 {
                    color: #fcd34d;
                }

                .text-xl {
                    font-size: 1.25rem;
                }

                .text-2xl {
                    font-size: 1.5rem;
                }

                .text-4xl {
                    font-size: 2.25rem;
                }

                .text-3xl {
                    font-size: 1.875rem;
                }

                .text-lg {
                    font-size: 1.125rem;
                }

                .font-bold {
                    font-weight: 700;
                }

                .font-semibold {
                    font-weight: 600;
                }

                .font-medium {
                    font-weight: 500;
                }

                .leading-tight {
                    line-height: 1.25;
                }

                .leading-relaxed {
                    line-height: 1.625;
                }

                .border-t {
                    border-top-width: 1px;
                }

                .border-gray-800 {
                    border-color: #1f2937;
                }

                .hover\:text-blue-600:hover {
                    color: #2563eb;
                }

                .hover\:text-white:hover {
                    color: #ffffff;
                }

                .transition {
                    transition-property: all;
                    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                    transition-duration: 150ms;
                }

                .max-w-3xl {
                    max-width: 48rem;
                }

                .max-w-4xl {
                    max-width: 56rem;
                }

                .max-w-7xl {
                    max-width: 80rem;
                }

                .sm\:flex-row {
                    flex-direction: row;
                }

                .sm\:px-6 {
                    padding-left: 1.5rem;
                    padding-right: 1.5rem;
                }

                .lg\:px-8 {
                    padding-left: 2rem;
                    padding-right: 2rem;
                }

                .lg\:grid-cols-2 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .lg\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .md\:grid-cols-2 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .md\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .md\:grid-cols-4 {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }

                .md\:text-6xl {
                    font-size: 3.75rem;
                }

                .md\:block {
                    display: block;
                }

                .md\:flex {
                    display: flex;
                }

                .hidden {
                    display: none;
                }

                @media (min-width: 640px) {
                    .sm\:flex-row {
                        flex-direction: row;
                    }
                    .sm\:px-6 {
                        padding-left: 1.5rem;
                        padding-right: 1.5rem;
                    }
                }

                @media (min-width: 768px) {
                    .md\:block {
                        display: block;
                    }
                    .md\:flex {
                        display: flex;
                    }
                    .md\:grid-cols-2 {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                    .md\:grid-cols-3 {
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                    }
                    .md\:grid-cols-4 {
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                    }
                    .md\:text-6xl {
                        font-size: 3.75rem;
                    }
                }

                @media (min-width: 1024px) {
                    .lg\:grid-cols-2 {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                    .lg\:grid-cols-3 {
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                    }
                    .lg\:px-8 {
                        padding-left: 2rem;
                        padding-right: 2rem;
                    }
                }

                @media (max-width: 768px) {
                    .hero-text {
                        text-align: center;
                    }
                    
                    .feature-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .stats-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }

                .animate-fade-in {
                    animation: fadeIn 1s ease-in-out;
                }

                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(30px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                .animate-slide-in-left {
                    animation: slideInLeft 1s ease-out;
                }

                @keyframes slideInLeft {
                    from { opacity: 0; transform: translateX(-50px); }
                    to { opacity: 1; transform: translateX(0); }
                }

                .animate-slide-in-right {
                    animation: slideInRight 1s ease-out;
                }

                @keyframes slideInRight {
                    from { opacity: 0; transform: translateX(50px); }
                    to { opacity: 1; transform: translateX(0); }
                }
            </style>
        @endif
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar fixed w-full top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h2 class="text-2xl font-bold text-gray-800">
                                🏠 <span class="gradient-text">Efiewura</span>
                            </h2>
                        </div>
                    </div>
                    
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="#features" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Features</a>
                            <a href="#how-it-works" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">How it Works</a>
                            <a href="#pricing" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Pricing</a>
                            <a href="#contact" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Contact</a>
                        </div>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-secondary">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-medium">Login</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-pattern min-h-screen flex items-center pt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="hero-text animate-slide-in-left">
                        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                            Modern Property Rental Management for
                            <span class="text-yellow-300">Ghana</span>
                        </h1>
                        <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                            Connect landlords with tenants seamlessly. Manage properties, handle bookings, and streamline your rental business with our comprehensive platform.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="#" class="btn-primary text-center">Start Free Trial</a>
                            <a href="#features" class="btn-secondary text-center">Learn More</a>
                        </div>
                        
                        <div class="mt-12 grid grid-cols-3 gap-8">
                            <div class="text-center">
                                <div class="stats-counter text-yellow-300">500+</div>
                                <p class="text-blue-100">Properties</p>
                            </div>
                            <div class="text-center">
                                <div class="stats-counter text-yellow-300">1200+</div>
                                <p class="text-blue-100">Happy Users</p>
                            </div>
                            <div class="text-center">
                                <div class="stats-counter text-yellow-300">99.9%</div>
                                <p class="text-blue-100">Uptime</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="animate-slide-in-right">
                        <div class="glass-card rounded-2xl p-8">
                            <div class="hero-image rounded-xl h-80 mb-6"></div>
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Ready to get started?</h3>
                                <p class="text-gray-600 mb-4">Join thousands of property owners and tenants</p>
                                <a href="#" class="btn-primary w-full text-center">Create Account</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 animate-fade-in">
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">Why Choose Efiewura?</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Our platform provides everything you need to manage properties efficiently and connect with the right people.
                    </p>
                </div>
                
                <div class="feature-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="feature-card bg-white rounded-xl p-8 text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-2xl">🏢</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Property Management</h3>
                        <p class="text-gray-600">
                            Easily list, manage, and showcase your properties with high-quality images and detailed descriptions.
                        </p>
                    </div>
                    
                    <div class="feature-card bg-white rounded-xl p-8 text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-2xl">👥</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Tenant Screening</h3>
                        <p class="text-gray-600">
                            Advanced tenant verification system to help you find reliable tenants quickly and safely.
                        </p>
                    </div>
                    
                    <div class="feature-card bg-white rounded-xl p-8 text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-2xl">💳</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Secure Payments</h3>
                        <p class="text-gray-600">
                            Handle rent payments securely with multiple payment options including mobile money and bank transfers.
                        </p>
                    </div>
                    
                    <div class="feature-card bg-white rounded-xl p-8 text-center">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-2xl">📱</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Mobile Friendly</h3>
                        <p class="text-gray-600">
                            Access your dashboard and manage properties on the go with our responsive mobile design.
                        </p>
                    </div>
                    
                    <div class="feature-card bg-white rounded-xl p-8 text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-2xl">📊</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Analytics Dashboard</h3>
                        <p class="text-gray-600">
                            Track your property performance with detailed analytics and reporting tools.
                        </p>
                    </div>
                    
                    <div class="feature-card bg-white rounded-xl p-8 text-center">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-2xl">🔒</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Security & Privacy</h3>
                        <p class="text-gray-600">
                            Your data is protected with enterprise-grade security and two-factor authentication.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">How It Works</h2>
                    <p class="text-xl text-gray-600">Simple steps to get started with Efiewura</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-3xl text-white font-bold">1</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Create Account</h3>
                        <p class="text-gray-600">Sign up as a landlord or tenant and complete your profile with necessary information.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-3xl text-white font-bold">2</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">List or Search</h3>
                        <p class="text-gray-600">Landlords list properties, tenants search for their perfect home using advanced filters.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-3xl text-white font-bold">3</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Connect & Rent</h3>
                        <p class="text-gray-600">Connect directly, schedule viewings, and complete secure transactions through our platform.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 gradient-bg">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold text-white mb-6">
                    Ready to Transform Your Property Business?
                </h2>
                <p class="text-xl text-blue-100 mb-8">
                    Join thousands of property owners and tenants who trust Efiewura for their rental needs.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="btn-primary bg-white text-blue-600 hover:bg-gray-100">Start Free Trial</a>
                    <a href="#" class="btn-secondary border-white text-white hover:bg-white hover:text-blue-600">Contact Sales</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-2xl font-bold mb-4">🏠 Efiewura</h3>
                        <p class="text-gray-400 mb-4">
                            Modern property rental management platform for Ghana. Connecting landlords and tenants seamlessly.
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white">📘</a>
                            <a href="#" class="text-gray-400 hover:text-white">🐦</a>
                            <a href="#" class="text-gray-400 hover:text-white">📷</a>
                            <a href="#" class="text-gray-400 hover:text-white">💼</a>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Product</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white">Features</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Pricing</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">API</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Documentation</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Support</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white">Help Center</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Community</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Status</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Company</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white">About</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Blog</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Careers</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Privacy</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                    <p class="text-gray-400">
                        © {{ date('Y') }} Efiewura. All rights reserved. Made with ❤️ in Ghana.
                    </p>
                </div>
            </div>
        </footer>

        <script>
            // Simple smooth scrolling for anchor links
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

            // Add navbar background on scroll
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 100) {
                    navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                } else {
                    navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                }
            });
        </script>
    </body>
</html>
