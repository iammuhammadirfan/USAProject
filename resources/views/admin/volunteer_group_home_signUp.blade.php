@extends('admin.layout')
@section('content')
@section('view_volunteer_group_signups_styles')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        padding: 20px;
        transition: margin-left 0.3s ease;
    }

    body.sidebar-open {
        margin-left: 250px;
    }

    body.sidebar-collapsed {
        margin-left: 60px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        transition: all 0.3s ease;
    }

    .sidebar-open .container {
        max-width: calc(100vw - 300px);
    }

    .sidebar-collapsed .container {
        max-width: calc(100vw - 110px);
    }

    h1 {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
        font-size: 28px;
    }

    .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .clear-btn,
    .check-btn,
    .add-case-btn {
        background-color: #ffc107;
        color: #333;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }

    .clear-btn:hover,
    .check-btn:hover,
    .add-case-btn:hover {
        background-color: #e0a800;
    }

    .search-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: end;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    label {
        font-weight: bold;
        color: #333;
    }

    .status-display {
        background-color: #28a745;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: bold;
        height: fit-content;
    }

    .case-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        margin-top: 20px;
    }

    .case-item {
        background-color: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 4px;
        padding: 8px;
        text-align: center;
        font-weight: bold;
        color: #495057;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .case-item:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
    }

    .case-item.selected {
        background-color: #007bff;
        color: white;
        border-color: #0056b3;
    }

    .user-info {
        background-color: #e3f2fd;
        border: 1px solid #2196f3;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .user-info h4 {
        margin-bottom: 10px;
        color: #1976d2;
    }

    @media (max-width: 768px) {
        .case-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .search-section {
            flex-direction: column;
            align-items: stretch;
        }

        .controls {
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .case-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        h1 {
            font-size: 24px;
        }
    }
</style>
@stop

<div class="container">
    <h1 class="text-center mb-4">Volunteer User Case Management</h1>
    <!-- User Selection Section -->
    <div class="bg-light p-4 rounded mb-3">
        <div class="row mb-3">
            <div class="col-md-6">
                 <label for="userSelect" class="form-label">Select User:</label>
        <select id="userSelect" class="form-control">
            <option value="">-- Select a User --</option>
            @foreach($users as $user)
                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
            @endforeach
        </select>
            </div>

        </div>

        <!-- User Info Display -->



        <div class="p-4 rounded mb-3">
            <div class="mt-3" id="deleteCaseWrapper" style="display: none;">
                <button class="btn btn-danger" onclick="deleteSelectedCase()">Delete Selected Case</button>
            </div>

            <div class="case-grid" id="caseGrid"></div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userSelect = document.getElementById('userSelect');
    const caseGrid = document.getElementById('caseGrid');
    const deleteWrapper = document.getElementById('deleteCaseWrapper');

    userSelect.addEventListener('change', function () {
        const userId = this.value;
        caseGrid.innerHTML = '';
        deleteWrapper.style.display = 'none';

        if (!userId) return;

        // Fetch cases via AJAX
        fetch(`/admin/volunteer-group-home-sign-up`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    caseGrid.innerHTML = `<div class="col-12 text-muted">No cases found for this user.</div>`;
                    return;
                }

                deleteWrapper.style.display = 'block';

                data.forEach((caseItem, index) => {
                    const div = document.createElement('div');
                    div.className = 'case-item';
                    div.textContent = caseItem.title || caseItem.name || `Case #${index + 1}`;
                    div.dataset.caseId = caseItem.id; // store ID for potential deletion
                    div.onclick = function () {
                        this.classList.toggle('selected');
                    };
                    caseGrid.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error loading cases:', error);
                caseGrid.innerHTML = `<div class="text-danger">Error loading cases.</div>`;
            });
    });
});

function deleteSelectedCase() {
    const selected = document.querySelectorAll('.case-item.selected');
    if (selected.length === 0) {
        alert('Please select a case to delete.');
        return;
    }

    const ids = Array.from(selected).map(el => el.dataset.caseId);
    // Send delete request (Laravel needs a route for this)
    console.log("Delete these case IDs:", ids);

    // You can replace this with an actual AJAX delete call
    alert('Delete request for: ' + ids.join(', '));
}
</script>

@endsection