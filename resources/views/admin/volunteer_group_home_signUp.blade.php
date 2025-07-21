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

    .select-all-btn {
        background-color: #ffc107;
        color: #333;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        margin-right: 10px;
        border: 2px solid #ffc107;
    }

    .select-all-btn:hover {
        background-color: #e0a800;
        border-color: #e0a800;
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

    .case-count-display {
        background-color: #17a2b8;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: bold;
        height: fit-content;
        margin-left: 10px;
    }

    .selected-count-display {
        background-color: #dc3545;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: bold;
        height: fit-content;
        margin-left: 10px;
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
        position: relative;
    }

    .case-item:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
    }

    .case-item.selected {
        background-color: #dc3545;
        color: white;
        border-color: #c82333;
        transform: scale(1.02);
    }

    .case-item .case-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #28a745;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
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

    .loading {
        text-align: center;
        padding: 20px;
        color: #666;
    }

    .controls-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .stats-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
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

        .controls-row {
            flex-direction: column;
            align-items: stretch;
        }

        .stats-row,
        .action-buttons {
            justify-content: center;
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

        <!-- Controls Section -->
        <div class="controls-section" id="controlsSection" style="display: none;">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Case Numbers Selected</label>
                    <input type="text" id="caseNumbersSelected" class="form-control" value="0" readonly style="background-color: #f8f9fa; font-weight: bold; text-align: center;">
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <button class="select-all-btn" onclick="selectAllCases()">Select All</button>
                    <button class="btn btn-danger ms-2" onclick="deleteSelectedCase()">Delete Selected</button>
                </div>
            </div>
        </div>

        <!-- User Info Display -->
        <div class="p-4 rounded mb-3">
            <div class="case-grid" id="caseGrid"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userSelect = document.getElementById('userSelect');
    const caseGrid = document.getElementById('caseGrid');
    const controlsSection = document.getElementById('controlsSection');
    const caseNumbersSelected = document.getElementById('caseNumbersSelected');

    let totalCasesCount = 0;
    let casesData = [];

    userSelect.addEventListener('change', function () {
        const userId = this.value;
        caseGrid.innerHTML = '';
        controlsSection.style.display = 'none';
        totalCasesCount = 0;

        if (!userId) return;

        // Show loading message
        caseGrid.innerHTML = '<div class="loading">Loading cases...</div>';

        // Fetch cases for selected user via AJAX
        fetch(`/admin/volunteer-cases-signup/${userId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                caseGrid.innerHTML = '';
                
                // Handle direct array response (your current format)
                if (Array.isArray(data)) {
                    if (data.length === 0) {
                        caseGrid.innerHTML = `<div class="col-12 text-muted">No cases found for this user.</div>`;
                        return;
                    }

                    casesData = data;
                    totalCasesCount = data.length;
                    renderCases(data);
                    return;
                }
                
                // Handle object response with success property
                if (!data.success) {
                    caseGrid.innerHTML = `<div class="col-12 text-danger">${data.message || 'Error loading cases'}</div>`;
                    return;
                }

                if (data.cases.length === 0) {
                    caseGrid.innerHTML = `<div class="col-12 text-muted">No cases found for this user.</div>`;
                    return;
                }

                casesData = data.cases;
                totalCasesCount = data.cases.length;
                renderCases(data.cases);
            })
            .catch(error => {
                console.error('Error loading cases:', error);
                caseGrid.innerHTML = `<div class="col-12 text-danger">Error loading cases. Please try again.</div>`;
            });
    });

    function renderCases(cases) {
        // Group cases by case number to count occurrences
        const caseNumberCounts = {};
        cases.forEach(caseItem => {
            const caseNum = caseItem.case_number || `Case #${caseItem.id}`;
            caseNumberCounts[caseNum] = (caseNumberCounts[caseNum] || 0) + 1;
        });

        controlsSection.style.display = 'block';
        caseNumbersSelected.value = totalCasesCount;

        cases.forEach((caseItem, index) => {
            const caseNum = caseItem.case_number || `Case #${caseItem.id}`;
            const count = caseNumberCounts[caseNum];
            
            const div = document.createElement('div');
            div.className = 'case-item';
            div.innerHTML = `
                <div><strong>${caseNum}</strong></div>
                <div>${caseItem.first_name || caseItem.name || 'No Name'}</div>
                ${count > 1 ? `<div class="case-count">${count}</div>` : ''}
            `;
            div.dataset.caseId = caseItem.id;
            div.dataset.caseNumber = caseNum;
            div.onclick = function () {
                toggleCaseSelection(this);
            };
            caseGrid.appendChild(div);
        });
    }

    function toggleCaseSelection(element) {
        element.classList.toggle('selected');
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.case-item.selected').length;
        const remainingCount = totalCasesCount - selectedCount;
        caseNumbersSelected.value = remainingCount;
    }

    // Global functions for buttons
    window.selectAllCases = function() {
        const allCases = document.querySelectorAll('.case-item');
        allCases.forEach(caseItem => {
            if (!caseItem.classList.contains('selected')) {
                caseItem.classList.add('selected');
            }
        });
        updateSelectedCount();
    };

    window.clearSelection = function() {
        const selectedCases = document.querySelectorAll('.case-item.selected');
        selectedCases.forEach(caseItem => {
            caseItem.classList.remove('selected');
        });
        caseNumbersSelected.value = totalCasesCount;
    };

    window.deleteSelectedCase = function() {
        const selected = document.querySelectorAll('.case-item.selected');
        if (selected.length === 0) {
            alert('Please select a case to delete.');
            return;
        }

        if (!confirm(`Are you sure you want to delete the ${selected.length} selected case(s)?`)) {
            return;
        }

        const ids = Array.from(selected).map(el => el.dataset.caseId);
        
        // Send delete request
        fetch('/admin/assignUp-case-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ case_numbers: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove deleted cases from UI
                selected.forEach(el => el.remove());
                totalCasesCount -= selected.length;
                caseNumbersSelected.value = totalCasesCount;
                
                // Hide controls if no cases left
                if (totalCasesCount === 0) {
                    controlsSection.style.display = 'none';
                    caseGrid.innerHTML = '<div class="col-12 text-muted">No cases found for this user.</div>';
                }
            } else {
                alert( (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting cases:', error);
        });
    };
});
</script>

@endsection