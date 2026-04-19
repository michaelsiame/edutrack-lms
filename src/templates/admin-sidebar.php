<!-- Admin Sidebar Navigation -->
<aside class="w-64 bg-gray-900 text-white flex-shrink-0">
    <div class="p-6">
        <a href="<?= url('admin/index.php') ?>" class="flex items-center space-x-2">
            <i class="fas fa-graduation-cap text-2xl text-primary-400"></i>
            <span class="text-xl font-bold">Edutrack Admin</span>
        </a>
    </div>

    <nav class="mt-6">
        <!-- Dashboard -->
        <a href="<?= url('admin/index.php') ?>"
           class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['page']) ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span class="ml-3">Dashboard</span>
        </a>

        <!-- Courses -->
        <div class="mt-2">
            <a href="<?= url('admin/index.php?page=courses') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'courses' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-book w-5"></i>
                <span class="ml-3">Courses</span>
            </a>
        </div>

        <!-- Users -->
        <div class="mt-2">
            <a href="<?= url('admin/index.php?page=users') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'users' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-users w-5"></i>
                <span class="ml-3">Users</span>
            </a>
        </div>

        <!-- Enrollments -->
        <div class="mt-2">
            <a href="<?= url('admin/index.php?page=enrollments') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'enrollments' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-user-graduate w-5"></i>
                <span class="ml-3">Enrollments</span>
            </a>
        </div>

        <!-- Announcements -->
        <div class="mt-2">
            <a href="<?= url('admin/index.php?page=announcements') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'announcements' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-bullhorn w-5"></i>
                <span class="ml-3">Announcements</span>
            </a>
        </div>

        <!-- Categories - Hidden until implemented -->
        <!-- <div class="mt-2">
            <a href="<?= url('admin/index.php?page=categories') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'categories' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-folder w-5"></i>
                <span class="ml-3">Categories</span>
            </a>
        </div> -->

        <!-- Payments -->
        <div class="mt-2">
            <a href="<?= url('admin/index.php?page=financials') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= in_array($_GET['page'] ?? '', ['financials', 'payments']) ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-money-bill-wave w-5"></i>
                <span class="ml-3">Payments</span>
            </a>
        </div>

        <!-- Reviews - Hidden until implemented -->
        <!-- <div class="mt-2">
            <a href="<?= url('admin/index.php?page=reviews') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'reviews' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-star w-5"></i>
                <span class="ml-3">Reviews</span>
            </a>
        </div> -->

        <!-- Certificates - Hidden until implemented -->
        <!-- <div class="mt-2">
            <a href="<?= url('admin/index.php?page=certificates') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'certificates' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-certificate w-5"></i>
                <span class="ml-3">Certificates</span>
            </a>
        </div> -->

        <!-- Reports - Hidden until implemented -->
        <!-- <div class="mt-2">
            <a href="<?= url('admin/index.php?page=reports') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'reports' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-chart-bar w-5"></i>
                <span class="ml-3">Reports</span>
            </a>
        </div> -->

        <!-- Settings -->
        <div class="mt-2">
            <a href="<?= url('admin/index.php?page=settings') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'settings' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-cog w-5"></i>
                <span class="ml-3">Settings</span>
            </a>
        </div>

        <!-- Company Profile -->
        <div class="mt-2">
            <a href="<?= url('admin/index.php?page=company-profile') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition <?= ($_GET['page'] ?? '') === 'company-profile' ? 'bg-gray-800 border-l-4 border-primary-500' : '' ?>">
                <i class="fas fa-building w-5"></i>
                <span class="ml-3">Company Profile</span>
            </a>
        </div>

        <div class="border-t border-gray-800 mt-6 pt-6">
            <!-- Back to Site -->
            <a href="<?= url('index.php') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition text-gray-400">
                <i class="fas fa-arrow-left w-5"></i>
                <span class="ml-3">Back to Site</span>
            </a>

            <!-- Logout -->
            <a href="<?= url('logout.php') ?>"
               class="flex items-center px-6 py-3 hover:bg-gray-800 transition text-red-400">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="ml-3">Logout</span>
            </a>
        </div>
    </nav>
</aside>
