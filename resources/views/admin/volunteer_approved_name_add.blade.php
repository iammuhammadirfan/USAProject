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
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
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

<div class="container ">
    <h1 class="text-center mb-4">Approved Names</h1>

    <!-- Clear Button -->
    <div class="float-right">
        <button class="btn btn-outline-primary" onclick="window.location.href='{{ url('/admin/volunteer-users-home') }}'"
        ><i class="fa fa-eye" aria-hidden="true"></i> View All Volunteers Users</button>
    </div>
    <br>
    <br>
    <!-- Search Section -->
    <div class="bg-light p-4 rounded mb-3">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" placeholder="Enter first name">
            </div>
            <div class="form-group col-md-6">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" placeholder="Enter last name">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="caseNumber" class="form-label">Case Number</label>
                <input type="text" id="caseNumber" class="form-control" placeholder="Enter case number">
            </div>

        </div>

        <div class="row align-items-center">
            <div class="col-md-8 d-flex gap-3">
                <button class="btn btn-primary fw-bold" onclick="checkCase()">Check</button>
                <div class="status-display" id="statusDisplay" style="background-color: #e9ecef; color: #333;"></div>
            </div>
        </div>
        <div class="row align-items-center mt-5">
            <div class="col-md-6 d-flex gap-3">
                <!-- clear Case Button -->
                <button class="btn btn-warning fw-bold" onclick="clearAll()">Clear</button>

            </div>
            <div class="col-md-6 d-flex gap-3">
                <button class="btn btn-success" onclick="addCaseNumber()" disabled>Add Case Number</button>

            </div>
        </div>

        <div class=" p-4 rounded mb-3">
            <div class="mt-3" id="deleteCaseWrapper" style="display: none;">
                <button class="btn btn-danger" onclick="deleteSelectedCase()">Delete Selected Case</button>
            </div>

            <div class="case-grid" id="caseGrid"></div>
        </div>
    </div>
    <div style="float: right;">
        <button class="btn btn-secondary" onclick="submitAllData()">Submit Volunteer User</button>
    </div>
</div>



<script>
    const caseNumbers = [];
    let selectedCases = [];
    let isCaseNumberValid = false;

    document.getElementById('caseNumber').addEventListener('input', () => {
        const input = document.getElementById('caseNumber').value.trim();
        isCaseNumberValid = false;
        updateAddButtonState();
        document.getElementById('statusDisplay').textContent = '';
        document.getElementById('statusDisplay').style.backgroundColor = '#e9ecef';
        document.getElementById('statusDisplay').style.color = '#333';
    });

    function updateAddButtonState() {
        const btn = document.querySelector('.btn-success');
        const input = document.getElementById('caseNumber').value.trim();
        btn.disabled = !isCaseNumberValid || caseNumbers.includes(input);
    }

    function initializeGrid() {
        updateGrid();
    }

    function updateGrid() {
        const grid = document.getElementById('caseGrid');
        grid.innerHTML = ''; // Clear old grid

        caseNumbers.forEach((caseNum, index) => {
            const caseItem = document.createElement('div');
            caseItem.className = 'case-item';
            caseItem.textContent = caseNum;
            caseItem.addEventListener('click', () => toggleCase(index));
            caseItem.classList.toggle('selected', selectedCases.includes(index));
            grid.appendChild(caseItem);
        });
    }

    function addCaseNumber() {
        const input = document.getElementById('caseNumber');
        const caseNumber = input.value.trim();
        if (!caseNumber || caseNumbers.includes(caseNumber)) return;

        if (caseNumbers.length >= 50) {
            alert('You can only add up to 50 cases.');
            return;
        }

        caseNumbers.push(caseNumber);
        input.value = '';
        isCaseNumberValid = false;
        updateAddButtonState();
        updateGrid();
        document.getElementById('statusDisplay').textContent = '';
        document.getElementById('statusDisplay').style.backgroundColor = '#e9ecef';
        document.getElementById('statusDisplay').style.color = '#333';
    }

    function toggleCase(index) {
        const selected = selectedCases.includes(index);
        selectedCases = selected ?
            selectedCases.filter(i => i !== index) : [...selectedCases, index];
        document.getElementById('deleteCaseWrapper').style.display = selectedCases.length > 0 ? 'block' : 'none';

        updateGrid();
    }

    function checkCase() {
        const caseNumber = document.getElementById('caseNumber').value.trim();
        const statusDisplay = document.getElementById('statusDisplay');

        if (!caseNumber) {
            alert('Please enter a case number');
            return;
        }

        statusDisplay.textContent = 'Checking...';
        statusDisplay.style.backgroundColor = '#ffc107';
        statusDisplay.style.color = '#333';

        // Replace this with your backend endpoint
        const url = '/verify-volunteer-case-number';

        $.ajax({
            url: url,
            method: 'GET',
            data: {
                caseNumber: caseNumber
            },
            success: function(response) {
                if (response.status === 201) {
                    const {
                        first_name,
                        last_name
                    } = response.data;
                    statusDisplay.textContent = `${first_name} ${last_name}`;
                    statusDisplay.style.backgroundColor = '#28a745';
                    statusDisplay.style.color = 'white';
                    isCaseNumberValid = true;

                } else {
                    statusDisplay.textContent = response.message || 'Invalid Case Number';
                    statusDisplay.style.backgroundColor = '#dc3545'; // red
                    statusDisplay.style.color = 'white';
                    isCaseNumberValid = false;

                }
                updateAddButtonState();

            },
            error: function() {
                statusDisplay.textContent = 'Server error';
                statusDisplay.style.backgroundColor = '#dc3545';
                statusDisplay.style.color = 'white';
                isCaseNumberValid = false;
                updateAddButtonState();
            }
        });
    }

    function deleteSelectedCase() {
        if (selectedCases.length === 0) return;

        const confirmed = confirm("Are you sure you want to delete this case number?");
        if (!confirmed) return;

        // Remove the selected case number
        selectedCases.sort((a, b) => b - a).forEach(index => {
            caseNumbers.splice(index, 1);
        });

        selectedCases = [];
        document.getElementById('deleteCaseWrapper').style.display = 'none';
        updateGrid();
    }



    function clearAll() {
        document.getElementById('firstName').value = '';
        document.getElementById('lastName').value = '';
        document.getElementById('caseNumber').value = '';
        document.getElementById('statusDisplay').textContent = '';
        selectedCases = [];
        updateGrid();
    }

    // Submit all data to Laravel backend
    function submitAllData() {
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();

        if (!firstName || !lastName) {
            alert("First Name and Last Name are required.");
            return;
        }

        if (caseNumbers.length === 0) {
            alert("Please add at least one case number.");
            return;
        }

        const payload = {
            first_name: firstName,
            last_name: lastName,
            case_numbers: caseNumbers,
            user_id: null
        };

        $.ajax({
            url: '/admin/add-volunteer-users',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 200) {
                    alert(response.message);
                    clearAll();
                    caseNumbers.length = 0;
                    updateGrid();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr) {
                alert("Server error: " + xhr.responseText);
            }
        });
    }

    function detectSidebar() {
        const sidebar = document.querySelector('.sidebar, .side-nav, .sidenav, [class*="sidebar"], [class*="side-nav"]');
        const body = document.body;
        if (!sidebar) {
            body.classList.remove('sidebar-open', 'sidebar-collapsed');
            return;
        }
        const isCollapsed = sidebar.classList.contains('collapsed') || sidebar.offsetWidth < 100;
        body.classList.toggle('sidebar-collapsed', isCollapsed);
        body.classList.toggle('sidebar-open', !isCollapsed);
    }

    function observeSidebarChanges() {
        const sidebar = document.querySelector('.sidebar, .side-nav, .sidenav, [class*="sidebar"], [class*="side-nav"]');
        if (!sidebar) return;
        const observer = new MutationObserver(() => detectSidebar());
        observer.observe(sidebar, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }

    window.addEventListener('load', () => {
        initializeGrid();
        setTimeout(() => {
            detectSidebar();
            observeSidebarChanges();
        }, 100);
    });

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(detectSidebar, 250);
    });
</script>

@endsection