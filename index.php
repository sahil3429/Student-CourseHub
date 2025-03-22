<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Course Hub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>University Course Hub</h1>
            </div>
            <div class="nav-links">
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#programmes">Programmes</a></li>
                    <li><a href="#staff">Staff</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="adminlogin.php" class="admin-btn">Admin Login</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section id="home" class="hero">
            <div class="hero-content">
                <h1>Discover Your Future</h1>
                <p>Explore our wide range of undergraduate and postgraduate programmes</p>
                <div class="hero-buttons">
                    <a href="#programmes" class="primary-btn">Browse Programmes</a>
                    <a href="#contact" class="secondary-btn">Contact Us</a>
                </div>
            </div>
        </section>

        <!-- Filter Section -->
        <section class="filter-section">
            <div class="filter-container">
                <h2>Find Your Perfect Programme</h2>
                <div class="filters">
                    <div class="filter-group">
                        <label for="level-filter">Level:</label>
                        <select id="level-filter">
                            <option value="all">All Levels</option>
                            <option value="1">Undergraduate</option>
                            <option value="2">Postgraduate</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="search-input">Search:</label>
                        <input type="text" id="search-input" placeholder="e.g., Computer Science, Cyber Security">
                    </div>
                    <button id="apply-filters" class="primary-btn">Apply Filters</button>
                </div>
            </div>
        </section>

        <!-- Programmes Section -->
        <section id="programmes" class="programmes">
            <h2>Our Programmes</h2>
            <div class="programmes-grid">
                <!-- Programme cards will be dynamically loaded here -->
                <!-- Example Programme Card -->
                <div class="programme-card" data-id="1" data-level="1">
                    <div class="card-image">
                        <img src="/api/placeholder/400/300" alt="Computer Science Programme">
                    </div>
                    <div class="card-content">
                        <h3>BSc Computer Science</h3>
                        <p class="level-tag">Undergraduate</p>
                        <p class="programme-description">A broad computer science degree covering programming, AI, cybersecurity, and software engineering.</p>
                        <p class="programme-leader">Programme Leader: Dr. Alice Johnson</p>
                        <div class="card-actions">
                            <a href="#" class="view-details-btn" data-id="1">View Details</a>
                            <a href="#" class="interest-btn" data-id="1">Register Interest</a>
                        </div>
                    </div>
                </div>
                <!-- More programme cards will be added dynamically -->
            </div>
        </section>

        <!-- Programme Details Modal -->
        <div id="programme-details-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div id="programme-details-container">
                    <!-- Programme details will be loaded here -->
                    <div class="programme-header">
                        <h2 id="modal-programme-title">BSc Computer Science</h2>
                        <p id="modal-programme-level">Undergraduate</p>
                    </div>
                    <div class="programme-image">
                        <img id="modal-programme-image" src="/api/placeholder/800/400" alt="Programme Image">
                    </div>
                    <div class="programme-description">
                        <h3>Description</h3>
                        <p id="modal-programme-description">A broad computer science degree covering programming, AI, cybersecurity, and software engineering.</p>
                    </div>
                    <div class="programme-leader">
                        <h3>Programme Leader</h3>
                        <p id="modal-programme-leader">Dr. Alice Johnson</p>
                    </div>
                    <div class="programme-modules">
                        <h3>Modules</h3>
                        <div class="module-year-tabs">
                            <button class="year-tab active" data-year="1">Year 1</button>
                            <button class="year-tab" data-year="2">Year 2</button>
                            <button class="year-tab" data-year="3">Year 3</button>
                        </div>
                        <div class="year-modules" id="year-1-modules">
                            <!-- Year 1 modules will be loaded here -->
                            <div class="module-card">
                                <h4>Introduction to Programming</h4>
                                <p>Covers the fundamentals of programming using Python and Java.</p>
                                <p class="module-leader">Module Leader: Dr. Alice Johnson</p>
                            </div>
                            <!-- More module cards will be loaded dynamically -->
                        </div>
                        <div class="year-modules" id="year-2-modules" style="display: none;">
                            <!-- Year 2 modules will be loaded here -->
                        </div>
                        <div class="year-modules" id="year-3-modules" style="display: none;">
                            <!-- Year 3 modules will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button id="register-interest-btn" class="primary-btn">Register Interest</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Section -->
        <section id="staff" class="staff">
            <h2>Our Academic Staff</h2>
            <div class="staff-grid">
                <!-- Staff cards will be dynamically loaded here -->
                <!-- Example Staff Card -->
                <div class="staff-card" data-id="1">
                    <div class="staff-image">
                        <img src="/api/placeholder/200/200" alt="Dr. Alice Johnson">
                    </div>
                    <div class="staff-content">
                        <h3>Dr. Alice Johnson</h3>
                        <p class="staff-role">Programme Leader - BSc Computer Science</p>
                        <p class="staff-modules">Modules: Introduction to Programming, Computer Networks</p>
                    </div>
                </div>
                <!-- More staff cards will be added dynamically -->
            </div>
        </section>

        <!-- Register Interest Modal -->
        <div id="register-interest-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="register-interest-form">
                    <h2>Register Your Interest</h2>
                    <p id="interest-programme-name">BSc Computer Science</p>
                    <form id="interest-form">
                        <input type="hidden" id="programme-id" value="">
                        <div class="form-group">
                            <label for="student-name">Your Name:</label>
                            <input type="text" id="student-name" required>
                        </div>
                        <div class="form-group">
                            <label for="student-email">Email Address:</label>
                            <input type="email" id="student-email" required>
                        </div>
                        <div class="form-group">
                            <label for="student-comments">Comments (Optional):</label>
                            <textarea id="student-comments"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary-btn">Submit</button>
                            <button type="button" class="secondary-btn cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Admin Login Modal -->
        <div id="admin-login-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="admin-login-form">
                    <h2>Admin Login</h2>
                    <form id="admin-login-form">
                        <div class="form-group">
                            <label for="admin-username">Username:</label>
                            <input type="text" id="admin-username" required>
                        </div>
                        <div class="form-group">
                            <label for="admin-password">Password:</label>
                            <input type="password" id="admin-password" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary-btn">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Success Message Modal -->
        <div id="success-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="success-message">
                    <h2>Thank You!</h2>
                    <p>Your interest has been registered successfully.</p>
                    <p>You will receive updates about this programme via email.</p>
                    <button class="primary-btn close-success-modal">Close</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Admin Dashboard (hidden by default) -->
    <div id="admin-dashboard" class="admin-section" style="display: none;">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <button id="admin-logout" class="logout-btn">Logout</button>
        </header>

        <nav class="admin-nav">
            <ul>
                <li><a href="#" class="admin-nav-link active" data-section="programmes-management">Programmes</a></li>
                <li><a href="#" class="admin-nav-link" data-section="modules-management">Modules</a></li>
                <li><a href="#" class="admin-nav-link" data-section="staff-management">Staff</a></li>
                <li><a href="#" class="admin-nav-link" data-section="student-management">Interested Students</a></li>
            </ul>
        </nav>

        <div class="admin-content">
            <!-- Programmes Management Section -->
            <section id="programmes-management" class="admin-section-content">
                <h2>Programmes Management</h2>
                <div class="admin-actions">
                    <button id="add-programme" class="primary-btn">Add New Programme</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="programmes-table">
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
                            <!-- Example row -->
                            <tr>
                                <td>1</td>
                                <td>BSc Computer Science</td>
                                <td>Undergraduate</td>
                                <td>Dr. Alice Johnson</td>
                                <td>Yes</td>
                                <td>
                                    <button class="edit-btn" data-id="1">Edit</button>
                                    <button class="toggle-publish-btn" data-id="1">Unpublish</button>
                                    <button class="delete-btn" data-id="1">Delete</button>
                                </td>
                            </tr>
                            <!-- More rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Modules Management Section -->
            <section id="modules-management" class="admin-section-content" style="display: none;">
                <h2>Modules Management</h2>
                <div class="admin-actions">
                    <button id="add-module" class="primary-btn">Add New Module</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="modules-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Module Leader</th>
                                <th>Programmes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example row -->
                            <tr>
                                <td>1</td>
                                <td>Introduction to Programming</td>
                                <td>Dr. Alice Johnson</td>
                                <td>BSc Computer Science, BSc Software Engineering</td>
                                <td>
                                    <button class="edit-btn" data-id="1">Edit</button>
                                    <button class="delete-btn" data-id="1">Delete</button>
                                </td>
                            </tr>
                            <!-- More rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Staff Management Section -->
            <section id="staff-management" class="admin-section-content" style="display: none;">
                <h2>Staff Management</h2>
                <div class="admin-actions">
                    <button id="add-staff" class="primary-btn">Add New Staff</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="staff-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Programmes Led</th>
                                <th>Modules Led</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example row -->
                            <tr>
                                <td>1</td>
                                <td>Dr. Alice Johnson</td>
                                <td>BSc Computer Science</td>
                                <td>Introduction to Programming, Computer Networks</td>
                                <td>
                                    <button class="edit-btn" data-id="1">Edit</button>
                                    <button class="delete-btn" data-id="1">Delete</button>
                                </td>
                            </tr>
                            <!-- More rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Student Management Section -->
            <section id="student-management" class="admin-section-content" style="display: none;">
                <h2>Interested Students</h2>
                <div class="admin-actions">
                    <button id="export-students" class="primary-btn">Export Mailing List</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="students-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Programme</th>
                                <th>Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example row -->
                            <tr>
                                <td>1</td>
                                <td>John Doe</td>
                                <td>john.doe@example.com</td>
                                <td>BSc Computer Science</td>
                                <td>2025-01-15</td>
                                <td>
                                    <button class="delete-btn" data-id="1">Remove</button>
                                </td>
                            </tr>
                            <!-- More rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Programme Form Modal -->
        <div id="programme-form-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="programme-form">
                    <h2 id="programme-form-title">Add New Programme</h2>
                    <form id="programme-form">
                        <input type="hidden" id="programme-form-id" value="">
                        <div class="form-group">
                            <label for="programme-name">Programme Name:</label>
                            <input type="text" id="programme-name" required>
                        </div>
                        <div class="form-group">
                            <label for="programme-level">Level:</label>
                            <select id="programme-level" required>
                                <option value="">Select Level</option>
                                <option value="1">Undergraduate</option>
                                <option value="2">Postgraduate</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="programme-leader">Programme Leader:</label>
                            <select id="programme-leader" required>
                                <option value="">Select Leader</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="programme-description">Description:</label>
                            <textarea id="programme-description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="programme-image">Image URL:</label>
                            <input type="text" id="programme-image">
                        </div>
                        <div class="form-group">
                            <label>Publish Status:</label>
                            <div class="radio-group">
                                <input type="radio" id="published-yes" name="published" value="1" checked>
                                <label for="published-yes">Published</label>
                                <input type="radio" id="published-no" name="published" value="0">
                                <label for="published-no">Unpublished</label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary-btn">Save</button>
                            <button type="button" class="secondary-btn cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Module Form Modal -->
        <div id="module-form-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="module-form">
                    <h2 id="module-form-title">Add New Module</h2>
                    <form id="module-form">
                        <input type="hidden" id="module-form-id" value="">
                        <div class="form-group">
                            <label for="module-name">Module Name:</label>
                            <input type="text" id="module-name" required>
                        </div>
                        <div class="form-group">
                            <label for="module-leader">Module Leader:</label>
                            <select id="module-leader" required>
                                <option value="">Select Leader</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="module-description">Description:</label>
                            <textarea id="module-description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="module-image">Image URL:</label>
                            <input type="text" id="module-image">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary-btn">Save</button>
                            <button type="button" class="secondary-btn cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Staff Form Modal -->
        <div id="staff-form-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="staff-form">
                    <h2 id="staff-form-title">Add New Staff</h2>
                    <form id="staff-form">
                        <input type="hidden" id="staff-form-id" value="">
                        <div class="form-group">
                            <label for="staff-name">Name:</label>
                            <input type="text" id="staff-name" required>
                        </div>
                        <div class="form-group">
                            <label for="staff-title">Title:</label>
                            <input type="text" id="staff-title" placeholder="e.g., Professor, Dr., etc." required>
                        </div>
                        <div class="form-group">
                            <label for="staff-department">Department:</label>
                            <input type="text" id="staff-department" required>
                        </div>
                        <div class="form-group">
                            <label for="staff-bio">Biography:</label>
                            <textarea id="staff-bio"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="staff-image">Image URL:</label>
                            <input type="text" id="staff-image">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary-btn">Save</button>
                            <button type="button" class="secondary-btn cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Programme Modules Modal -->
        <div id="programme-modules-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="programme-modules-management">
                    <h2>Manage Programme Modules</h2>
                    <p id="programme-modules-name">BSc Computer Science</p>
                    <div class="year-tabs">
                        <button class="year-tab active" data-year="1">Year 1</button>
                        <button class="year-tab" data-year="2">Year 2</button>
                        <button class="year-tab" data-year="3">Year 3</button>
                    </div>
                    <div class="year-modules-container">
                        <div class="year-modules-management" id="year-1-modules-management">
                            <h3>Year 1 Modules</h3>
                            <button class="add-year-module-btn" data-year="1">Add Module</button>
                            <div class="assigned-modules">
                                <!-- Example module -->
                                <div class="assigned-module">
                                    <span>Introduction to Programming</span>
                                    <button class="remove-module-btn" data-module-id="1">Remove</button>
                                </div>
                                <!-- More modules will be added dynamically -->
                            </div>
                        </div>
                        <div class="year-modules-management" id="year-2-modules-management" style="display: none;">
                            <h3>Year 2 Modules</h3>
                            <button class="add-year-module-btn" data-year="2">Add Module</button>
                            <div class="assigned-modules">
                                <!-- Modules will be loaded dynamically -->
                            </div>
                        </div>
                        <div class="year-modules-management" id="year-3-modules-management" style="display: none;">
                            <h3>Year 3 Modules</h3>
                            <button class="add-year-module-btn" data-year="3">Add Module</button>
                            <div class="assigned-modules">
                                <!-- Modules will be loaded dynamically -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="primary-btn close-modules-modal">Done</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Module to Programme Modal -->
        <div id="add-module-programme-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="add-module-form">
                    <h2>Add Module to Programme</h2>
                    <p id="add-module-programme-name">BSc Computer Science - Year 1</p>
                    <form id="add-module-form">
                        <input type="hidden" id="add-module-programme-id" value="">
                        <input type="hidden" id="add-module-year" value="">
                        <div class="form-group">
                            <label for="module-select">Select Module:</label>
                            <select id="module-select" required>
                                <option value="">Select Module</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary-btn">Add Module</button>
                            <button type="button" class="secondary-btn cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirmation-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="confirmation-message">
                    <h2>Confirm Action</h2>
                    <p id="confirmation-text">Are you sure you want to delete this item?</p>
                    <div class="modal-actions">
                        <button id="confirm-action" class="primary-btn">Confirm</button>
                        <button id="cancel-action" class="secondary-btn">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Course Hub</h3>
                <p>Helping you find the perfect course for your future</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#programmes">Programmes</a></li>
                    <li><a href="#staff">Staff</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: admissions@university.ac.uk</p>
                <p>Phone: +44 (0)123 456 7890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 University Course Hub. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts will be added here -->
</body>
</html>