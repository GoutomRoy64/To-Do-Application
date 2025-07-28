<?php
    session_start();
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.html");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* --- Styles for Edit Mode --- */
        #tasks li .view-mode,
        #tasks li .edit-mode {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .task-actions .btn, .edit-actions .btn {
            margin-left: 5px;
        }

        .edit-input {
            flex-grow: 1;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="h4 mb-0">My To-Do List for <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
                <!-- Updated Logout Link -->
                <a href="auth.php?action=logout" class="btn btn-danger btn-sm">Logout</a>
            </div>
            <div class="card-body">
                <form id="task-form">
                    <div class="input-group mb-3">
                        <input type="text" id="task-input" class="form-control" placeholder="Add a new task..." required>
                        <button class="btn btn-primary" type="submit">Add Task</button>
                    </div>
                </form>
            </div>
            <ul id="tasks" class="list-group list-group-flush">
                <!-- Tasks will be loaded here -->
            </ul>
        </div>
    </div>

    <script>
        // This JavaScript does not need to be changed from the previous version.
        // It already correctly calls api.php for all task-related actions.
        document.addEventListener('DOMContentLoaded', function() {
            const taskForm = document.getElementById('task-form');
            const taskInput = document.getElementById('task-input');
            const taskList = document.getElementById('tasks');

            const getTasks = async () => {
                try {
                    const response = await fetch('api.php');
                    if (response.status === 401) {
                        window.location.href = 'login.html';
                        return;
                    }
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const tasks = await response.json();
                    taskList.innerHTML = '';
                    if(tasks.error) {
                        console.error(tasks.error);
                        return;
                    }
                    tasks.forEach(task => appendTaskToList(task));
                } catch (error) {
                    console.error("Could not fetch tasks:", error);
                }
            };

            const appendTaskToList = (task) => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.id = task.id;
                if (parseInt(task.is_completed)) {
                    li.classList.add('task-completed');
                }
                li.innerHTML = `
                    <div class="view-mode">
                        <span class="task-text">${task.task_description}</span>
                        <div class="task-actions">
                            <button class="btn btn-info btn-sm edit-btn" title="Edit">✎</button>
                            <button class="btn btn-success btn-sm complete-btn" title="Complete">✓</button>
                            <button class="btn btn-danger btn-sm delete-btn" title="Delete">✗</button>
                        </div>
                    </div>
                    <div class="edit-mode" style="display: none;">
                        <input type="text" class="form-control edit-input" value="${task.task_description}">
                        <div class="edit-actions">
                            <button class="btn btn-primary btn-sm save-btn">Save</button>
                            <button class="btn btn-secondary btn-sm cancel-btn">Cancel</button>
                        </div>
                    </div>
                `;
                taskList.appendChild(li);
            };

            taskForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                const taskText = taskInput.value.trim();
                if (taskText === '') return;
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('task', taskText);
                try {
                    const response = await fetch('api.php', { method: 'POST', body: formData });
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const result = await response.json();
                    if (result.status === 'success') {
                        appendTaskToList(result.task);
                        taskInput.value = '';
                    } else {
                        console.error("Failed to add task:", result.message);
                    }
                } catch (error) {
                    console.error("Error submitting task:", error);
                }
            });

            taskList.addEventListener('click', async function(event) {
                const target = event.target;
                const li = target.closest('li');
                if (!li) return;

                const viewMode = li.querySelector('.view-mode');
                const editMode = li.querySelector('.edit-mode');
                const taskId = li.dataset.id;
                const formData = new FormData();
                
                if (target.classList.contains('edit-btn')) {
                    viewMode.style.display = 'none';
                    editMode.style.display = 'flex';
                    editMode.querySelector('.edit-input').focus();
                }

                if (target.classList.contains('cancel-btn')) {
                    const originalText = viewMode.querySelector('.task-text').textContent;
                    editMode.querySelector('.edit-input').value = originalText;
                    viewMode.style.display = 'flex';
                    editMode.style.display = 'none';
                }

                if (target.classList.contains('save-btn')) {
                    const newText = editMode.querySelector('.edit-input').value.trim();
                    if (newText === '') return;

                    formData.append('action', 'rename');
                    formData.append('id', taskId);
                    formData.append('task_description', newText);
                    
                    try {
                        const response = await fetch('api.php', { method: 'POST', body: formData });
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        const result = await response.json();
                        if (result.status === 'success') {
                            viewMode.querySelector('.task-text').textContent = newText;
                            viewMode.style.display = 'flex';
                            editMode.style.display = 'none';
                        }
                    } catch (error) {
                        console.error("Error renaming task:", error);
                    }
                }

                if (target.classList.contains('complete-btn')) {
                    formData.append('action', 'update');
                    formData.append('id', taskId);
                    try {
                        const response = await fetch('api.php', { method: 'POST', body: formData });
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        const result = await response.json();
                        if (result.status === 'success') {
                            li.classList.toggle('task-completed');
                        }
                    } catch (error) {
                        console.error("Error updating task:", error);
                    }
                }

                if (target.classList.contains('delete-btn')) {
                    formData.append('action', 'delete');
                    formData.append('id', taskId);
                    try {
                        const response = await fetch('api.php', { method: 'POST', body: formData });
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        const result = await response.json();
                        if (result.status === 'success') {
                            li.remove();
                        }
                    } catch (error) {
                        console.error("Error deleting task:", error);
                    }
                }
            });
            getTasks();
        });
    </script>
</body>
</html>
