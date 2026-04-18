<?php
/**
 * Admin Help & Documentation Center
 * System administration guide for platform administrators
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated and is an admin
if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = User::current();
if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
    redirect('../dashboard.php');
}

$page_title = "Admin Help Center - System Guide";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold mb-4">Administrator Help Center</h1>
            <p class="text-xl text-slate-300 max-w-3xl">
                Complete system administration guide for managing the Edutrack LMS platform, users, and content.
            </p>
            
            <!-- Quick Search -->
            <div class="mt-8 max-w-2xl">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           id="helpSearch"
                           placeholder="Search admin topics, features, or procedures..." 
                           class="w-full pl-12 pr-4 py-4 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-white/30">
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border sticky top-6">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Contents</h3>
                    </div>
                    <nav class="p-2 space-y-1 overflow-y-auto max-h-[calc(100vh-200px)]">
                        <a href="#getting-started" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-power-off w-6 text-red-500"></i>
                            Getting Started
                        </a>
                        <a href="#user-management" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-users-cog w-6 text-blue-500"></i>
                            User Management
                        </a>
                        <a href="#course-management" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-graduation-cap w-6 text-green-500"></i>
                            Course Management
                        </a>
                        <a href="#financials" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-dollar-sign w-6 text-green-600"></i>
                            Financial Management
                        </a>
                        <a href="#system-settings" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-cogs w-6 text-gray-600"></i>
                            System Settings
                        </a>
                        <a href="#enrollments" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-user-plus w-6 text-purple-500"></i>
                            Enrollment Management
                        </a>
                        <a href="#reports" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-chart-bar w-6 text-orange-500"></i>
                            Reports & Analytics
                        </a>
                        <a href="#security" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-shield-alt w-6 text-red-600"></i>
                            Security & Access
                        </a>
                        <a href="#backup" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-database w-6 text-indigo-500"></i>
                            Backup & Maintenance
                        </a>
                        <a href="#troubleshooting" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-50 text-gray-700 hover:text-slate-700 transition">
                            <i class="fas fa-wrench w-6 text-gray-500"></i>
                            Troubleshooting
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-12">
                
                <!-- Getting Started -->
                <section id="getting-started" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-power-off text-red-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Getting Started as Administrator</h2>
                    </div>
                    
                    <div class="prose max-w-none text-gray-600">
                        <p class="text-lg">As a system administrator, you have full control over the Edutrack LMS platform. This guide will help you understand your responsibilities and how to manage the system effectively.</p>
                        
                        <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Administrator Responsibilities</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-2"><i class="fas fa-users text-blue-500 mr-2"></i>User Management</h4>
                                <ul class="text-sm space-y-1">
                                    <li>Create and manage user accounts</li>
                                    <li>Assign roles (Student, Instructor, Admin)</li>
                                    <li>Reset passwords and unlock accounts</li>
                                    <li>Monitor user activity</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-2"><i class="fas fa-graduation-cap text-green-500 mr-2"></i>Content Oversight</h4>
                                <ul class="text-sm space-y-1">
                                    <li>Approve and publish courses</li>
                                    <li>Manage course categories</li>
                                    <li>Monitor content quality</li>
                                    <li>Archive outdated courses</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-2"><i class="fas fa-dollar-sign text-green-600 mr-2"></i>Financial Control</h4>
                                <ul class="text-sm space-y-1">
                                    <li>Process payments and refunds</li>
                                    <li>View financial reports</li>
                                    <li>Manage instructor payouts</li>
                                    <li>Set up payment plans</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-2"><i class="fas fa-cogs text-gray-600 mr-2"></i>System Configuration</h4>
                                <ul class="text-sm space-y-1">
                                    <li>Configure system settings</li>
                                    <li>Manage email templates</li>
                                    <li>Set up integrations</li>
                                    <li>Perform system maintenance</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg mt-6">
                            <p class="text-amber-800"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Important:</strong> Always create a backup before making major system changes. Test changes in a staging environment if available.</p>
                        </div>
                    </div>
                </section>

                <!-- User Management -->
                <section id="user-management" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users-cog text-blue-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="border rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Understanding User Roles</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left p-3 font-semibold text-gray-700">Role</th>
                                            <th class="text-left p-3 font-semibold text-gray-700">Permissions</th>
                                            <th class="text-left p-3 font-semibold text-gray-700">Typical Users</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr>
                                            <td class="p-3"><span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Super Admin</span></td>
                                            <td class="p-3">Full system access, can manage other admins</td>
                                            <td class="p-3">System owners, technical directors</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3"><span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">Admin</span></td>
                                            <td class="p-3">Manage users, courses, finances, settings</td>
                                            <td class="p-3">Operations managers, support leads</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Instructor</span></td>
                                            <td class="p-3">Create courses, grade work, view their students</td>
                                            <td class="p-3">Teachers, trainers, content creators</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Student</span></td>
                                            <td class="p-3">View courses, submit work, track progress</td>
                                            <td class="p-3">Learners, course participants</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-user-plus text-green-500 mr-2"></i>
                                    Creating Users
                                </h3>
                                <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
                                    <li>Go to <strong>Users → User Management</strong></li>
                                    <li>Click "Add User" button</li>
                                    <li>Fill in required information (name, email, role)</li>
                                    <li>Set temporary password (user will be prompted to change)</li>
                                    <li>Click "Create User"</li>
                                    <li>User receives welcome email with login details</li>
                                </ol>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-user-lock text-orange-500 mr-2"></i>
                                    Managing Access
                                </h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li><strong>Deactivate:</strong> Suspend user account temporarily</li>
                                    <li><strong>Reset Password:</strong> Generate new temporary password</li>
                                    <li><strong>Change Role:</strong> Upgrade/downgrade user permissions</li>
                                    <li><strong>Delete:</strong> Permanently remove account (use carefully)</li>
                                    <li><strong>Impersonate:</strong> View system as that user for support</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                            <h4 class="font-semibold text-blue-800 mb-2">Bulk User Operations</h4>
                            <p class="text-blue-700 text-sm">For adding many users at once (e.g., new semester intake):</p>
                            <ol class="text-blue-700 text-sm mt-2 space-y-1 list-decimal list-inside">
                                <li>Prepare CSV file with columns: first_name, last_name, email, role, phone</li>
                                <li>Go to <strong>Users → Bulk Import</strong></li>
                                <li>Upload CSV and review preview</li>
                                <li>System sends welcome emails to all new users</li>
                            </ol>
                        </div>
                    </div>
                </section>

                <!-- Course Management -->
                <section id="course-management" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-green-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Course Management</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="border rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Course Lifecycle</h3>
                            <div class="flex flex-col md:flex-row gap-4 text-center">
                                <div class="flex-1 bg-gray-50 rounded-lg p-4">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-pencil-alt text-gray-600"></i>
                                    </div>
                                    <h4 class="font-medium text-gray-800">Draft</h4>
                                    <p class="text-xs text-gray-600 mt-1">Instructor creating content, not visible to students</p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-arrow-right text-gray-400 hidden md:block"></i>
                                    <i class="fas fa-arrow-down text-gray-400 md:hidden"></i>
                                </div>
                                <div class="flex-1 bg-yellow-50 rounded-lg p-4">
                                    <div class="w-10 h-10 bg-yellow-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-eye text-yellow-700"></i>
                                    </div>
                                    <h4 class="font-medium text-gray-800">Under Review</h4>
                                    <p class="text-xs text-gray-600 mt-1">Pending admin approval before publication</p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-arrow-right text-gray-400 hidden md:block"></i>
                                    <i class="fas fa-arrow-down text-gray-400 md:hidden"></i>
                                </div>
                                <div class="flex-1 bg-green-50 rounded-lg p-4">
                                    <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-check text-green-700"></i>
                                    </div>
                                    <h4 class="font-medium text-gray-800">Published</h4>
                                    <p class="text-xs text-gray-600 mt-1">Live and available for enrollment</p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-arrow-right text-gray-400 hidden md:block"></i>
                                    <i class="fas fa-arrow-down text-gray-400 md:hidden"></i>
                                </div>
                                <div class="flex-1 bg-gray-100 rounded-lg p-4">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-archive text-gray-600"></i>
                                    </div>
                                    <h4 class="font-medium text-gray-800">Archived</h4>
                                    <p class="text-xs text-gray-600 mt-1">No longer accepting new enrollments</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Course Approval Workflow</h3>
                                <ol class="space-y-3 text-sm text-gray-600 list-decimal list-inside">
                                    <li>Instructor submits course for review</li>
                                    <li>Admin receives notification</li>
                                    <li>Review course content, structure, and materials</li>
                                    <li>Check for:
                                        <ul class="list-disc list-inside ml-4 mt-1 text-xs">
                                            <li>Complete information (description, objectives)</li>
                                            <li>Working video links and downloads</li>
                                            <li>Appropriate content quality</li>
                                            <li>Correct pricing</li>
                                        </ul>
                                    </li>
                                    <li>Approve or request changes with feedback</li>
                                    <li>Course goes live upon approval</li>
                                </ol>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Featured Courses</h3>
                                <p class="text-sm text-gray-600 mb-3">Featured courses appear on the homepage and get more visibility.</p>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li><i class="fas fa-star text-yellow-500 mr-2"></i>Maximum 6-8 featured courses recommended</li>
                                    <li><i class="fas fa-sync text-blue-500 mr-2"></i>Rotate featured courses monthly</li>
                                    <li><i class="fas fa-chart-line text-green-500 mr-2"></i>Feature high-quality, popular courses</li>
                                    <li><i class="fas fa-balance-scale text-purple-500 mr-2"></i>Balance categories (don't feature only IT courses)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-semibold text-green-800 mb-2">Category Management</h4>
                            <p class="text-green-700 text-sm">Organize courses into categories for easier discovery:</p>
                            <ul class="text-green-700 text-sm mt-2 space-y-1">
                                <li>• Create categories from <strong>Courses → Categories</strong></li>
                                <li>• Use clear, broad categories (e.g., "Programming", "Business", "Design")</li>
                                <li>• Don't create too many categories (5-10 is ideal)</li>
                                <li>• Each course can belong to one category</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Financial Management -->
                <section id="financials" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Financial Management</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <h4 class="font-semibold text-green-800 mb-2">Revenue</h4>
                                <p class="text-sm text-green-700">Track all course sales, registration fees, and other income sources.</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <h4 class="font-semibold text-blue-800 mb-2">Payouts</h4>
                                <p class="text-sm text-blue-700">Process instructor payments based on revenue sharing agreements.</p>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                                <h4 class="font-semibold text-orange-800 mb-2">Payment Plans</h4>
                                <p class="text-sm text-orange-700">Manage installment payments and track outstanding balances.</p>
                            </div>
                        </div>

                        <div class="border rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Sharing</h3>
                            <p class="text-gray-600 mb-4">Typical revenue split between platform and instructors:</p>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="text-left p-2">Party</th>
                                            <th class="text-left p-2">Typical Share</th>
                                            <th class="text-left p-2">Responsibilities</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="p-2 font-medium">Platform</td>
                                            <td class="p-2">30-50%</td>
                                            <td class="p-2">Hosting, support, marketing, payment processing</td>
                                        </tr>
                                        <tr>
                                            <td class="p-2 font-medium">Instructor</td>
                                            <td class="p-2">50-70%</td>
                                            <td class="p-2">Content creation, student support, updates</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="border rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Processing Refunds</h3>
                            <ol class="space-y-2 text-gray-600 list-decimal list-inside">
                                <li>Verify refund request reason and eligibility</li>
                                <li>Check if student accessed significant content (>30% usually disqualifies)</li>
                                <li>Process refund in payment gateway</li>
                                <li>Update enrollment status to "Cancelled"</li>
                                <li>Notify student and instructor</li>
                                <li>Document reason for future policy review</li>
                            </ol>
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-4">
                                <p class="text-amber-800 text-sm"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Policy Note:</strong> Establish clear refund policy (typically 7-14 days from enrollment if less than 30% completed).</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- System Settings -->
                <section id="system-settings" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cogs text-gray-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">System Settings</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-envelope text-blue-500 mr-2"></i>
                                Email Configuration
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li><strong>SMTP Settings:</strong> Configure mail server for sending notifications</li>
                                <li><strong>Templates:</strong> Customize welcome, completion, and reminder emails</li>
                                <li><strong>Sender Name:</strong> Set from name (e.g., "Edutrack Learning")</li>
                                <li><strong>Test Emails:</strong> Always send test before going live</li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-paint-brush text-purple-500 mr-2"></i>
                                Branding Settings
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li><strong>Logo:</strong> Upload institution logo (SVG or PNG)</li>
                                <li><strong>Colors:</strong> Set primary and secondary brand colors</li>
                                <li><strong>Favicon:</strong> Browser tab icon</li>
                                <li><strong>Footer:</strong> Contact info, social links, copyright</li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-credit-card text-green-500 mr-2"></i>
                                Payment Settings
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li><strong>Currency:</strong> Set default currency (ZMW)</li>
                                <li><strong>Payment Gateway:</strong> Configure Stripe, PayPal, etc.</li>
                                <li><strong>Registration Fee:</strong> Set one-time student registration fee</li>
                                <li><strong>Tax:</strong> Configure VAT/tax rates if applicable</li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-clock text-orange-500 mr-2"></i>
                                Learning Settings
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li><strong>Certificate Expiry:</strong> Set if certificates expire</li>
                                <li><strong>Quiz Defaults:</strong> Default passing score, attempts</li>
                                <li><strong>Progress Tracking:</strong> Video completion percentage</li>
                                <li><strong>Time Zone:</strong> Africa/Lusaka (CAT, UTC+2)</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Enrollment Management -->
                <section id="enrollments" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Enrollment Management</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Manual Enrollment</h3>
                                <p class="text-sm text-gray-600 mb-3">Enroll students manually (useful for corporate training or scholarships):</p>
                                <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
                                    <li>Go to <strong>Enrollments → Add Enrollment</strong></li>
                                    <li>Select student from dropdown</li>
                                    <li>Select course</li>
                                    <li>Set enrollment type (Free, Paid, Scholarship)</li>
                                    <li>Confirm enrollment</li>
                                </ol>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Bulk Enrollment</h3>
                                <p class="text-sm text-gray-600 mb-3">Enroll multiple students at once:</p>
                                <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
                                    <li>Prepare CSV with student emails</li>
                                    <li>Go to <strong>Enrollments → Bulk Enroll</strong></li>
                                    <li>Select target course</li>
                                    <li>Upload CSV file</li>
                                    <li>Review and confirm enrollments</li>
                                </ol>
                            </div>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h4 class="font-semibold text-purple-800 mb-2">Enrollment Statuses</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div class="bg-white rounded p-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">Enrolled</span>
                                    <p class="text-gray-600 mt-1">Just started</p>
                                </div>
                                <div class="bg-white rounded p-2">
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">In Progress</span>
                                    <p class="text-gray-600 mt-1">Actively learning</p>
                                </div>
                                <div class="bg-white rounded p-2">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Completed</span>
                                    <p class="text-gray-600 mt-1">Finished course</p>
                                </div>
                                <div class="bg-white rounded p-2">
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Dropped</span>
                                    <p class="text-gray-600 mt-1">Left course</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Reports & Analytics -->
                <section id="reports" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-bar text-orange-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Reports & Analytics</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Key Metrics to Monitor</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li><i class="fas fa-users text-blue-500 mr-2"></i><strong>Active Users:</strong> Daily/weekly active students</li>
                                    <li><i class="fas fa-graduation-cap text-green-500 mr-2"></i><strong>Course Completions:</strong> Monthly completion rate</li>
                                    <li><i class="fas fa-dollar-sign text-green-600 mr-2"></i><strong>Revenue:</strong> Monthly recurring revenue</li>
                                    <li><i class="fas fa-chart-line text-purple-500 mr-2"></i><strong>Engagement:</strong> Average lessons per student</li>
                                    <li><i class="fas fa-percentage text-orange-500 mr-2"></i><strong>Retention:</strong> Students completing vs dropping</li>
                                </ul>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Available Reports</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li><strong>Financial Report:</strong> Revenue, refunds, payouts</li>
                                    <li><strong>Enrollment Report:</strong> New enrollments by course/date</li>
                                    <li><strong>Activity Report:</strong> Student login and lesson views</li>
                                    <li><strong>Completion Report:</strong> Course completion statistics</li>
                                    <li><strong>Instructor Report:</strong> Performance by instructor</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg">
                            <h4 class="font-semibold text-orange-800 mb-2">Exporting Data</h4>
                            <p class="text-orange-700 text-sm">All reports can be exported as CSV or PDF for further analysis or record-keeping. Use date filters to generate reports for specific periods.</p>
                        </div>
                    </div>
                </section>

                <!-- Security -->
                <section id="security" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Security & Access Control</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3">Security Best Practices</h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>Enforce strong passwords (min 8 chars, mixed case, numbers)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>Enable two-factor authentication for admin accounts</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>Regularly review user access logs</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>Remove access for departed staff immediately</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>Use HTTPS for all communications</span>
                                </li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3">Data Protection</h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-database text-blue-500 mt-1"></i>
                                    <span>Regular automated backups (daily recommended)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-user-shield text-purple-500 mt-1"></i>
                                    <span>Comply with data privacy regulations</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-eye-slash text-gray-500 mt-1"></i>
                                    <span>Mask sensitive data in logs and exports</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-trash-alt text-red-500 mt-1"></i>
                                    <span>Secure data deletion when required</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-6">
                        <h4 class="font-semibold text-red-800 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Security Incident Response</h4>
                        <p class="text-red-700 text-sm">If you suspect a security breach:</p>
                        <ol class="text-red-700 text-sm mt-2 space-y-1 list-decimal list-inside">
                            <li>Immediately change all admin passwords</li>
                            <li>Review recent login activity for suspicious access</li>
                            <li>Contact your hosting provider and IT security team</li>
                            <li>Document the incident and actions taken</li>
                            <li>Notify affected users if data was compromised</li>
                        </ol>
                    </div>
                </section>

                <!-- Backup & Maintenance -->
                <section id="backup" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-database text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Backup & Maintenance</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Backup Strategy</h3>
                                <div class="space-y-3">
                                    <div class="bg-green-50 rounded-lg p-3">
                                        <h4 class="font-medium text-green-800 text-sm">Database Backup</h4>
                                        <p class="text-xs text-green-700 mt-1">Daily automated backups of all user data, courses, and enrollments</p>
                                    </div>
                                    <div class="bg-blue-50 rounded-lg p-3">
                                        <h4 class="font-medium text-blue-800 text-sm">File Backup</h4>
                                        <p class="text-xs text-blue-700 mt-1">Weekly backup of uploaded files (videos, documents, images)</p>
                                    </div>
                                    <div class="bg-purple-50 rounded-lg p-3">
                                        <h4 class="font-medium text-purple-800 text-sm">Off-site Storage</h4>
                                        <p class="text-xs text-purple-700 mt-1">Store copies in different physical location or cloud service</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3">Maintenance Tasks</h3>
                                <div class="space-y-3 text-sm text-gray-600">
                                    <div class="flex items-start gap-2">
                                        <span class="text-blue-500 font-bold">Daily:</span>
                                        <span>Check system status, review error logs</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="text-green-500 font-bold">Weekly:</span>
                                        <span>Clean temporary files, verify backups</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="text-orange-500 font-bold">Monthly:</span>
                                        <span>Update system, review disk space</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="text-purple-500 font-bold">Quarterly:</span>
                                        <span>Security audit, performance optimization</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                            <h4 class="font-semibold text-indigo-800 mb-2">Restoring from Backup</h4>
                            <p class="text-indigo-700 text-sm">If system failure occurs:</p>
                            <ol class="text-indigo-700 text-sm mt-2 space-y-1 list-decimal list-inside">
                                <li>Put system in maintenance mode</li>
                                <li>Notify users of temporary unavailability</li>
                                <li>Restore database from most recent backup</li>
                                <li>Restore files if necessary</li>
                                <li>Verify system functionality</li>
                                <li>Bring system back online</li>
                            </ol>
                        </div>
                    </div>
                </section>

                <!-- Troubleshooting -->
                <section id="troubleshooting" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-wrench text-gray-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Troubleshooting</h2>
                    </div>

                    <div class="space-y-4">
                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Users can't log in</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p><strong>Check:</strong></p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>User account is active (not suspended)</li>
                                    <li>Correct email and password being used</li>
                                    <li>Caps Lock is off</li>
                                    <li>User is using the correct login page (not admin page)</li>
                                </ul>
                                <p class="mt-2"><strong>Solution:</strong> Reset user's password and send reset link. Check system logs for lockout status.</p>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Course not appearing in catalog</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p><strong>Check:</strong></p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>Course status is "Published" (not Draft)</li>
                                    <li>Course has at least one published module</li>
                                    <li>Course start date has passed (if set)</li>
                                    <li>Course is assigned to a category</li>
                                </ul>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Payments not processing</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p><strong>Check:</strong></p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>Payment gateway credentials are valid</li>
                                    <li>API keys haven't expired</li>
                                    <li>Payment gateway service is operational</li>
                                    <li>SSL certificate is valid</li>
                                </ul>
                                <p class="mt-2"><strong>Action:</strong> Test with small amount in sandbox mode. Contact payment provider if issues persist.</p>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">System running slow</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p><strong>Check:</strong></p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>Server CPU and memory usage</li>
                                    <li>Database query performance</li>
                                    <li>Disk space availability</li>
                                    <li>Number of concurrent users</li>
                                </ul>
                                <p class="mt-2"><strong>Solutions:</strong> Clear cache, optimize database, upgrade server resources if needed.</p>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Emails not being sent</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p><strong>Check:</strong></p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>SMTP settings are configured correctly</li>
                                    <li>Email credentials are valid</li>
                                    <li>Email service provider isn't blocking</li>
                                    <li>Test email in system settings works</li>
                                </ul>
                                <p class="mt-2"><strong>Note:</strong> Check spam/junk folders. Whitelist your domain with email provider.</p>
                            </div>
                        </details>
                    </div>
                </section>

                <!-- Need More Help -->
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl p-8 text-center text-white">
                    <h2 class="text-2xl font-bold mb-3">Need Technical Support?</h2>
                    <p class="text-slate-300 mb-6 max-w-2xl mx-auto">
                        For complex issues or system customization, contact the technical team.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="mailto:<?= SITE_EMAIL ?>" class="inline-flex items-center px-6 py-3 bg-white text-slate-800 font-semibold rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-envelope mr-2"></i>Technical Support
                        </a>
                        <a href="<?= url('help.php') ?>" class="inline-flex items-center px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition">
                            <i class="fas fa-book mr-2"></i>Developer Docs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
const searchInput = document.getElementById('helpSearch');
const sections = document.querySelectorAll('section');

searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    
    sections.forEach(section => {
        const text = section.textContent.toLowerCase();
        const headings = section.querySelectorAll('h2, h3, h4');
        let hasMatch = false;
        
        if (text.includes(query)) {
            hasMatch = true;
        }
        
        headings.forEach(heading => {
            if (heading.textContent.toLowerCase().includes(query)) {
                hasMatch = true;
                heading.style.backgroundColor = query ? '#fef3c7' : '';
            } else {
                heading.style.backgroundColor = '';
            }
        });
        
        section.style.display = (hasMatch || query === '') ? 'block' : 'none';
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

<?php require_once '../../src/templates/footer.php'; ?>
