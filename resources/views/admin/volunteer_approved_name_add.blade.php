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
</head>

<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Approved Names</h1>

        <!-- Clear Button -->
        <div class="mb-3">
            <button class="btn btn-warning fw-bold" onclick="clearAll()">Clear</button>
        </div>

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
                <div class="col-md-6 d-flex gap-3">
                    <button class="btn btn-primary fw-bold" onclick="checkCase()">Check</button>
                    <div class="status-display" id="statusDisplay" style="background-color: #e9ecef; color: #333;">Victor Wembanyana</div>
                </div>
            </div>
            <div class="row align-items-center mt-5">
                <div class="col-md-6 d-flex gap-3">
                       <!-- Add Case Button -->
                    <button class="btn btn-success " onclick="addCaseNumber()">Add Case Number</button>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Case Grid (keep styling or replace separately) -->
    <div class="case-grid" id="caseGrid"></div>
    </div>

    <script>
        const caseNumbers = new Array(60).fill('C123456');
        let selectedCases = [];

        function initializeGrid() {
            const grid = document.getElementById('caseGrid');
            grid.innerHTML = '';
            for (let i = 0; i < 60; i++) {
                requestAnimationFrame(() => {
                    const caseItem = document.createElement('div');
                    caseItem.className = 'case-item';
                    caseItem.textContent = caseNumbers[i];
                    caseItem.addEventListener('click', () => toggleCase(i));
                    grid.appendChild(caseItem);
                });
            }
        }

        function updateGrid() {
            const items = document.querySelectorAll('.case-grid .case-item');
            items.forEach((item, index) => {
                item.textContent = caseNumbers[index] || 'C123456';
                item.classList.toggle('selected', selectedCases.includes(index));
            });
        }

        function addCaseNumber() {
            const caseNumber = document.getElementById('caseNumber').value.trim();
            if (!caseNumber) return alert('Please enter a case number');

            let added = false;
            for (let i = 0; i < 60; i++) {
                if (!caseNumbers[i] || caseNumbers[i] === 'C123456') {
                    caseNumbers[i] = caseNumber;
                    added = true;
                    break;
                }
            }
            if (!added && caseNumbers.length < 60) caseNumbers.push(caseNumber);

            updateGrid();
            document.getElementById('caseNumber').value = '';
        }

        function toggleCase(index) {
            const caseItem = document.querySelector(`.case-grid .case-item:nth-child(${index + 1})`);
            const selected = selectedCases.includes(index);
            selectedCases = selected ? selectedCases.filter(i => i !== index) : [...selectedCases, index];
            caseItem.classList.toggle('selected', !selected);
        }

        function checkCase() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const caseNumber = document.getElementById('caseNumber').value.trim();
            const statusDisplay = document.getElementById('statusDisplay');

            if (!(firstName || lastName || caseNumber)) {
                alert('Please enter at least one field to check');
                return;
            }

            statusDisplay.textContent = 'Checking...';
            statusDisplay.style.backgroundColor = '#ffc107';
            statusDisplay.style.color = '#333';

            setTimeout(() => {
                statusDisplay.textContent = firstName && lastName ? `${firstName} ${lastName}` :
                    caseNumber ? `Case: ${caseNumber}` :
                    'Incomplete Information';
                statusDisplay.style.backgroundColor = '#28a745';
                statusDisplay.style.color = 'white';
            }, 600);
        }

        function clearAll() {
            document.getElementById('firstName').value = '';
            document.getElementById('lastName').value = '';
            document.getElementById('caseNumber').value = '';
            document.getElementById('statusDisplay').textContent = 'Victor Wembanyana';
            selectedCases = [];
            updateGrid();
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