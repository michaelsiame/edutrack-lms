<?php
/**
 * Departments Management Page - Full CRUD
 * Features: Add, Edit, Delete, Toggle Status, Stats Bar, Table View
 * Note: AJAX and POST handlers are processed in index.php and handlers/departments_handler.php
 */

// Fetch all departments with head of department name and course count
$departments = $db->fetchAll("
    SELECT d.*,
           CONCAT(u.first_name, ' ', u.last_name) as head_name,
           (SELECT COUNT(*) FROM courses WHERE department_id = d.id) as course_count
    FROM departments d
    LEFT JOIN users u ON d.head_of_department = u.id
    ORDER BY d.name ASC
");

// Fetch eligible users for head of department dropdown (admins + instructors)
$eligibleHeads = $db->fetchAll("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as full_name
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    WHERE ur.role_id IN (1, 2, 3)
    ORDER BY u.first_name, u.last_name
");

// Stats
$totalDepartments = count($departments);
$activeDepartments = 0;
$inactiveDepartments = 0;
$totalCoursesInDepts = 0;

foreach ($departments as $dept) {
    if ($dept['is_active']) {
        $activeDepartments++;
    } else {
        $inactiveDepartments++;
    }
    $totalCoursesInDepts += (int)$dept['course_count'];
}

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Department Management</h2>
            <p class="text-gray-500 text-sm mt-1">Organize and manage academic departments</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i>
                <span>Add Department</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="<?= in_array($msg, ['cannot_delete', 'duplicate_code', 'csrf_error', 'validation_error']) ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas <?= in_array($msg, ['cannot_delete', 'duplicate_code', 'csrf_error', 'validation_error']) ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
            <?php
            echo match($msg) {
                'added' => 'Department created successfully!',
                'updated' => 'Department updated successfully!',
                'deleted' => 'Department deleted successfully!',
                'status_updated' => 'Department status updated!',
                'cannot_delete' => 'Cannot delete department with associated courses!',
                'duplicate_code' => 'A department with this code already exists!',
                'csrf_error' => 'Security token expired. Please try again.',
                'validation_error' => 'Please fill in all required fields.',
                default => 'Action completed!'
            };
            ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $totalDepartments ?></p>
                    <p class="text-xs text-gray-500">Total Departments</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $activeDepartments ?></p>
                    <p class="text-xs text-gray-500">Active</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $inactiveDepartments ?></p>
                    <p class="text-xs text-gray-500">Inactive</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $totalCoursesInDepts ?></p>
                    <p class="text-xs text-gray-500">Total Courses in Departments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Head</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Courses</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($departments as $dept): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($dept['name']) ?></p>
                                    <?php if (!empty($dept['description'])): ?>
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1"><?= htmlspecialchars($dept['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-mono bg-gray-100 text-gray-700 rounded border"><?= htmlspecialchars($dept['code']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= htmlspecialchars($dept['head_name'] ?? 'Not assigned') ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 text-sm text-gray-600">
                                    <i class="fas fa-book text-gray-400"></i>
                                    <?= (int)$dept['course_count'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($dept['is_active']): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditModal(<?= $dept['id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="department_id" value="<?= $dept['id'] ?>">
                                        <button type="submit" class="p-2 <?= $dept['is_active'] ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' ?> rounded-lg transition-colors" title="<?= $dept['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fas <?= $dept['is_active'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this department? This cannot be undone.')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="department_id" value="<?= $dept['id'] ?>">
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($departments)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-building text-4xl mb-3"></i>
                                    <p class="text-lg font-medium">No departments found</p>
                                    <p class="text-sm">Get started by adding your first department</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div id="departmentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b sticky top-0 bg-white z-10">
            <div class="flex justify-between items-center">
                <h3 id="modalTitle" class="text-xl font-semibold text-gray-800">Add New Department</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form id="departmentForm" method="POST" class="p-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="department_id" id="departmentId" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="deptName" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Computer Science">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="deptCode" required maxlength="10" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase" placeholder="e.g. CS">
                    <p class="text-xs text-gray-500 mt-1">Maximum 10 characters. Must be unique.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="deptDescription" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Brief description of the department"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Head of Department</label>
                    <select name="head_of_department" id="deptHead" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- None --</option>
                        <?php foreach ($eligibleHeads as $head): ?>
                            <option value="<?= $head['id'] ?>"><?= htmlspecialchars($head['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="deptActive" value="1" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="deptActive" class="text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    <span id="submitBtn">Add Department</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Department';
    document.getElementById('formAction').value = 'add';
    document.getElementById('departmentId').value = '';
    document.getElementById('submitBtn').textContent = 'Add Department';
    document.getElementById('departmentForm').reset();
    document.getElementById('deptActive').checked = true;
    document.getElementById('departmentModal').classList.remove('hidden');
}

function openEditModal(deptId) {
    document.getElementById('modalTitle').textContent = 'Edit Department';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('departmentId').value = deptId;
    document.getElementById('submitBtn').textContent = 'Save Changes';

    fetch('?page=departments&ajax=get_department&id=' + deptId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            document.getElementById('deptName').value = data.name || '';
            document.getElementById('deptCode').value = data.code || '';
            document.getElementById('deptDescription').value = data.description || '';
            document.getElementById('deptHead').value = data.head_of_department || '';
            document.getElementById('deptActive').checked = parseInt(data.is_active) === 1;
            document.getElementById('departmentModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load department data');
        });
}

function closeModal() {
    document.getElementById('departmentModal').classList.add('hidden');
}

document.getElementById('departmentModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
