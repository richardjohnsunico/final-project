
<?php
session_start();

// Initialize 10 sample employees
if (!isset($_SESSION['employees'])) {
    $sampleEmployees = [
        ['John', 'Smith', 'john@example.com'],
        ['Maria', 'Santos', 'maria@example.com'],
        ['Peter', 'Johnson', 'peter@example.com'],
        ['Anna', 'Reyes', 'anna@example.com'],
        ['David', 'Brown', 'david@example.com'],
        ['Sarah', 'Garcia', 'sarah@example.com'],
        ['James', 'Wilson', 'james@example.com'],
        ['Lisa', 'Cruz', 'lisa@example.com'],
        ['Michael', 'Taylor', 'michael@example.com'],
        ['Grace', 'Miller', 'grace@example.com']
    ];

    $_SESSION['employees'] = [];

    foreach ($sampleEmployees as $i => $person) {
        $_SESSION['employees'][] = [
            'id' => $i + 1,
            'name' => $person[0],
            'lastname' => $person[1],
            'email' => $person[2],
            'password' => password_hash('12345', PASSWORD_DEFAULT)
        ];
    }
}

$employees = &$_SESSION['employees'];
$message = '';

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function validEmployee($name, $lastname, $email) {
    return trim($name) !== ''
        && trim($lastname) !== ''
        && filter_var($email, FILTER_VALIDATE_EMAIL);
}

$form = [
    'id' => '',
    'name' => '',
    'lastname' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // CREATE: Add a new employee
    if ($action === 'create') {
        if (
            !validEmployee($name, $lastname, $email) ||
            trim($password) === ''
        ) {
            $message = 'Enter a valid name, lastname, email, and password.';
        } else {
            $duplicate = false;

            foreach ($employees as $employee) {
                if (
                    ($id > 0 && $employee['id'] === $id) ||
                    strtolower($employee['email']) === strtolower($email)
                ) {
                    $duplicate = true;
                    break;
                }
            }

            if ($duplicate) {
                $message = 'ID or email already exists.';
            } else {
                $newId = $id > 0
                    ? $id
                    : (empty($employees)
                        ? 1
                        : max(array_column($employees, 'id')) + 1);

                $employees[] = [
                    'id' => $newId,
                    'name' => $name,
                    'lastname' => $lastname,
                    'email' => $email,
                    'password' => password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    )
                ];

                $message = 'Employee created successfully!';
            }
        }
    }

    // UPDATE: Save changes to an existing employee
    elseif ($action === 'update') {
        if (
            $id < 1 ||
            !validEmployee($name, $lastname, $email)
        ) {
            $message = 'Select an employee and enter valid details.';
        } else {
            $found = false;
            $duplicateEmail = false;

            foreach ($employees as $employee) {
                if (
                    $employee['id'] !== $id &&
                    strtolower($employee['email']) === strtolower($email)
                ) {
                    $duplicateEmail = true;
                    break;
                }
            }

            if ($duplicateEmail) {
                $message = 'That email is already used by another employee.';
            } else {
                foreach ($employees as &$employee) {
                    if ($employee['id'] === $id) {
                        $employee['name'] = $name;
                        $employee['lastname'] = $lastname;
                        $employee['email'] = $email;

                        // Change password only if a new one is entered
                        if (trim($password) !== '') {
                            $employee['password'] = password_hash(
                                $password,
                                PASSWORD_DEFAULT
                            );
                        }

                        $found = true;
                        break;
                    }
                }
                unset($employee);

                $message = $found
                    ? 'Employee updated successfully!'
                    : 'Employee not found.';
            }
        }
    }

    // DELETE: Remove an employee
    elseif ($action === 'delete') {
        $before = count($employees);

        $employees = array_values(array_filter(
            $employees,
            fn($employee) => $employee['id'] !== $id
        ));

        $_SESSION['employees'] = $employees;
        $employees = &$_SESSION['employees'];

        $message = count($employees) < $before
            ? 'Employee deleted successfully!'
            : 'Select an employee from the table first.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Management System</title>

<style>
* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    font-size: 13px;
    margin: 15px;
    color: #111;
    background: white;
}

h3 {
    font-size: 16px;
    font-weight: normal;
}

.container {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.form-panel {
    width: 245px;
    flex-shrink: 0;
    background: #f0f0f0;
    padding: 18px 15px;
    min-height: 300px;
}

.field {
    display: grid;
    grid-template-columns: 85px 1fr;
    align-items: center;
    margin-bottom: 18px;
    gap: 6px;
}

.field input {
    width: 100%;
    min-width: 0;
    height: 24px;
    border: 1px solid #999;
    padding: 4px;
    font-size: 12px;
}

.buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

button {
    padding: 6px 4px;
    cursor: pointer;
    border: 1px solid #aaa;
    background: #e8e8e8;
    font-size: 12px;
}

button:hover {
    background: #d5e6fa;
}

.table-panel {
    flex: 1;
    min-width: 0;
    height: 330px;
    overflow: auto;
    background: #aaa;
    border: 1px solid #555;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #aaa;
    font-size: 12px;
}

th, td {
    border: 1px solid #888;
    text-align: left;
    padding: 10px 7px;
    white-space: nowrap;
}

th {
    position: sticky;
    top: 0;
    background: #ddd;
}

tbody tr {
    cursor: pointer;
}

tbody tr:hover {
    background: #d5e6fa;
}

tbody tr.selected {
    background: #b6d4f5;
    outline: 1px solid #2878c7;
}

.message {
    margin: 10px 0;
    color: #176c32;
}

@media (max-width: 700px) {
    .container {
        flex-direction: column;
    }

    .form-panel,
    .table-panel {
        width: 100%;
    }
}
</style>
</head>

<body>

<h3>Employee Management System</h3>

<?php if ($message !== ''): ?>
    <p class="message"><?= clean($message) ?></p>
<?php endif; ?>

<div class="container">

    <!-- Employee Form -->
    <form class="form-panel" method="POST" id="employeeForm">

        <div class="field">
            <label for="id">ID</label>
            <input type="number" id="id" name="id"
                   min="1" placeholder="Auto">
        </div>

        <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="field">
            <label for="lastname">Lastname</label>
            <input type="text" id="lastname" name="lastname" required>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   autocomplete="new-password"
                   placeholder="New password">
        </div>

        <div class="buttons">
            <button type="submit" name="action" value="create">
                Create
            </button>

            <button type="submit" name="action" value="update"
                    onclick="return confirm('Save changes to this employee?')">
                Update
            </button>

            <button type="submit" name="action" value="delete"
                    formnovalidate
                    onclick="return confirm('Delete this employee?')">
                Delete
            </button>
        </div>

    </form>

    <!-- Employee Table -->
    <div class="table-panel">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Lastname</th>
                    <th>Email</th>
                    <th>Password</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr onclick="selectEmployee(this)"
                        data-id="<?= clean($employee['id']) ?>"
                        data-name="<?= clean($employee['name']) ?>"
                        data-lastname="<?= clean($employee['lastname']) ?>"
                        data-email="<?= clean($employee['email']) ?>">

                        <td><?= clean($employee['id']) ?></td>
                        <td><?= clean($employee['name']) ?></td>
                        <td><?= clean($employee['lastname']) ?></td>
                        <td><?= clean($employee['email']) ?></td>
                        <td>••••••••</td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="5">No employee records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function selectEmployee(row) {
    document.querySelectorAll('tbody tr').forEach(function (tr) {
        tr.classList.remove('selected');
    });

    row.classList.add('selected');

    ['id', 'name', 'lastname', 'email'].forEach(function (field) {
        document.getElementById(field).value = row.dataset[field];
    });

    // Passwords are hashed and cannot be retrieved.
    // Leave blank to keep the current password.
    document.getElementById('password').value = '';
}
</script>

</body>
</html>
