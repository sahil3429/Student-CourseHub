<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Course Hub - University Programmes</title>
    <link rel="stylesheet" href="style.css">
    <!-- CSS can be added later -->
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="logo-container">
            <h1>University Course Hub</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="#programmes">Programmes</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="/stafflogin.php">Staff</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Find Your Perfect Programme</h2>
            <p>Explore our undergraduate and postgraduate programmes and take the next step in your academic journey.</p>
            <div class="search-container">
                <input type="text" id="programme-search" placeholder="Search programmes...">
                <button id="search-button">Search</button>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="filters">
        <div class="filter-container">
            <h3>Filter Programmes</h3>
            <div class="filter-options">
                <div class="filter-group">
                    <label for="level-filter">Level:</label>
                    <select id="level-filter">
                        <option value="all">All Levels</option>
                        <option value="1">Undergraduate</option>
                        <option value="2">Postgraduate</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- Programmes List Section -->
    <section id="programmes" class="programmes">
        <h2>Available Programmes</h2>
        <div class="programmes-grid">
            <!-- Programme cards will be dynamically loaded here -->
            <!-- Example Programme Card -->
            <div class="programme-card">
                <div class="programme-image">
                    <img src="/api/placeholder/300/200" alt="BSc Computer Science">
                </div>
                <div class="programme-details">
                    <h3>BSc Computer Science</h3>
                    <p class="programme-level">Undergraduate</p>
                    <p class="programme-leader">Programme Leader: Dr. Alice Johnson</p>
                    <p class="programme-description">A broad computer science degree covering programming, AI, cybersecurity, and software engineering.</p>
                    <a href="programme-details.html?id=1" class="view-details">View Details</a>
                </div>
            </div>
            <!-- More programme cards would be here -->
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="#programmes">Programmes</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: admissions@university.ac.uk</p>
                <p>Phone: +44 (0)123 456 7890</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">Facebook</a>
                    <a href="#" aria-label="Twitter">Twitter</a>
                    <a href="#" aria-label="Instagram">Instagram</a>
                    <a href="#" aria-label="LinkedIn">LinkedIn</a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 University Course Hub. All rights reserved.</p>
        </div>
    </footer>

    <!-- Programme Details Page Template -->
    <!-- This would be a separate HTML file but included here for reference -->
    <template id="programme-details-template">
        <section class="programme-header">
            <div class="programme-banner">
                <img src="/api/placeholder/1200/400" alt="Programme Banner">
            </div>
            <div class="programme-header-content">
                <h1>BSc Computer Science</h1>
                <p class="programme-level">Undergraduate</p>
                <div class="programme-leader">
                    <h3>Programme Leader</h3>
                    <div class="staff-profile">
                        <img src="/api/placeholder/100/100" alt="Dr. Alice Johnson">
                        <div class="staff-details">
                            <h4>Dr. Alice Johnson</h4>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="programme-description">
            <h2>About This Programme</h2>
            <p>A broad computer science degree covering programming, AI, cybersecurity, and software engineering.</p>
        </section>

        <section class="programme-structure">
            <h2>Programme Structure</h2>
            
            <div class="year-container">
                <h3>Year 1</h3>
                <div class="modules-grid">
                    <!-- Module cards for Year 1 -->
                    <div class="module-card">
                        <h4>Introduction to Programming</h4>
                        <p class="module-leader">Module Leader: Dr. Alice Johnson</p>
                        <p>Covers the fundamentals of programming using Python and Java.</p>
                    </div>
                    <!-- More module cards -->
                </div>
            </div>
            
            <div class="year-container">
                <h3>Year 2</h3>
                <div class="modules-grid">
                    <!-- Module cards for Year 2 -->
                </div>
            </div>
            
            <div class="year-container">
                <h3>Year 3</h3>
                <div class="modules-grid">
                    <!-- Module cards for Year 3 -->
                </div>
            </div>
        </section>

        <section class="register-interest">
            <h2>Register Your Interest</h2>
            <form id="interest-form">
                <div class="form-group">
                    <label for="student-name">Full Name:</label>
                    <input type="text" id="student-name" name="student-name" required>
                </div>
                <div class="form-group">
                    <label for="student-email">Email:</label>
                    <input type="email" id="student-email" name="student-email" required>
                </div>
                <input type="hidden" id="programme-id" name="programme-id" value="1">
                <button type="submit">Register Interest</button>
            </form>
        </section>
    </template>

    <!-- Admin Login Page Template -->
    <!-- This would be a separate HTML file but included here for reference -->
    <template id="admin-login-template">
        <div class="admin-login-container">
            <h2>Admin Login</h2>
            <form id="admin-login-form">
                <div class="form-group">
                    <label for="admin-username">Username:</label>
                    <input type="text" id="admin-username" name="admin-username" required>
                </div>
                <div class="form-group">
                    <label for="admin-password">Password:</label>
                    <input type="password" id="admin-password" name="admin-password" required>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </template>

    <!-- Admin Dashboard Template -->
    <!-- This would be a separate HTML file but included here for reference -->
    <template id="admin-dashboard-template">
        <div class="admin-dashboard">
            <header class="admin-header">
                <h1>Admin Dashboard</h1>
                <nav class="admin-nav">
                    <ul>
                        <li><a href="#programmes-management">Programmes</a></li>
                        <li><a href="#modules-management">Modules</a></li>
                        <li><a href="#staff-management">Staff</a></li>
                        <li><a href="#students-management">Students</a></li>
                        <li><a href="index.html">View Website</a></li>
                        <li><a href="#" id="logout-link">Logout</a></li>
                    </ul>
                </nav>
            </header>

            <section id="programmes-management" class="admin-section">
                <h2>Programmes Management</h2>
                <div class="admin-actions">
                    <button id="add-programme">Add New Programme</button>
                    <div class="search-container">
                        <input type="text" id="admin-programme-search" placeholder="Search programmes...">
                        <button id="admin-search-button">Search</button>
                    </div>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Programme Leader</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Programme rows will be dynamically loaded here -->
                        <tr>
                            <td>1</td>
                            <td>BSc Computer Science</td>
                            <td>Undergraduate</td>
                            <td>Dr. Alice Johnson</td>
                            <td>Yes</td>
                            <td>
                                <button class="edit-btn">Edit</button>
                                <button class="delete-btn">Delete</button>
                                <button class="publish-btn">Unpublish</button>
                            </td>
                        </tr>
                        <!-- More programme rows -->
                    </tbody>
                </table>
            </section>

            <section id="modules-management" class="admin-section">
                <h2>Modules Management</h2>
                <div class="admin-actions">
                    <button id="add-module">Add New Module</button>
                    <div class="search-container">
                        <input type="text" id="admin-module-search" placeholder="Search modules...">
                        <button id="admin-module-search-button">Search</button>
                    </div>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Module Leader</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Module rows will be dynamically loaded here -->
                    </tbody>
                </table>
            </section>

            <section id="students-management" class="admin-section">
                <h2>Interested Students</h2>
                <div class="admin-actions">
                    <button id="export-students">Export Mailing List</button>
                    <div class="filter-container">
                        <label for="programme-filter">Filter by Programme:</label>
                        <select id="programme-filter">
                            <option value="all">All Programmes</option>
                            <!-- Programme options will be dynamically loaded here -->
                        </select>
                    </div>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Programme</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Student rows will be dynamically loaded here -->
                    </tbody>
                </table>
            </section>

            <!-- Add/Edit Programme Form Template -->
            <div id="programme-form-container" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Add/Edit Programme</h2>
                    <form id="programme-form">
                        <input type="hidden" id="edit-programme-id" name="programme-id">
                        <div class="form-group">
                            <label for="programme-name">Programme Name:</label>
                            <input type="text" id="programme-name" name="programme-name" required>
                        </div>
                        <div class="form-group">
                            <label for="programme-level">Level:</label>
                            <select id="programme-level" name="programme-level" required>
                                <option value="">Select Level</option>
                                <option value="1">Undergraduate</option>
                                <option value="2">Postgraduate</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="programme-leader">Programme Leader:</label>
                            <select id="programme-leader" name="programme-leader" required>
                                <option value="">Select Programme Leader</option>
                                <!-- Staff options will be dynamically loaded here -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="programme-description">Description:</label>
                            <textarea id="programme-description" name="programme-description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="programme-image">Image URL:</label>
                            <input type="text" id="programme-image" name="programme-image">
                        </div>
                        <div class="form-group">
                            <label for="programme-published">Published:</label>
                            <input type="checkbox" id="programme-published" name="programme-published">
                        </div>
                        <button type="submit">Save Programme</button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</body>
</html>