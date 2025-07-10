@extends('admin.layout')
            @section('content')
            @section('view_volunteer_group_signups_styles')
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

        /* Auto-adjust for sidebar */
        body.sidebar-open {
            margin-left: 250px; /* Default sidebar width */
        }

        body.sidebar-collapsed {
            margin-left: 60px; /* Collapsed sidebar width */
        }

        /* Detect sidebar presence automatically */
        body:has(.sidebar) {
            margin-left: 250px;
        }

        body:has(.sidebar.collapsed) {
            margin-left: 60px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            transition: all 0.3s ease;
        }

        /* Sidebar adjustments */
        .sidebar-open .container {
            max-width: calc(100vw - 300px); /* Adjust for sidebar + margins */
        }

        .sidebar-collapsed .container {
            max-width: calc(100vw - 110px); /* Adjust for collapsed sidebar + margins */
        }

        @media (min-width: 1200px) {
            .container, .container-lg, .container-md, .container-sm, .container-xl {
                max-width: 1400px;
            }
            
            .sidebar-open .container {
                max-width: calc(100vw - 300px);
            }
            
            .sidebar-collapsed .container {
                max-width: calc(100vw - 110px);
            }
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

        .entries-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .clear-btn {
            background-color: #ffc107;
            color: #333;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .clear-btn:hover {
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

        input[type="text"] {
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            width: 200px;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #007bff;
        }

        .check-btn {
            background-color: #ffc107;
            color: #333;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            height: fit-content;
        }

        .check-btn:hover {
            background-color: #e0a800;
        }

        .status-display {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            height: fit-content;
        }

        .add-case-btn {
            background-color: #ffc107;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .add-case-btn:hover {
            background-color: #e0a800;
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

        @media (max-width: 1199px) {
            .container {
                max-width: 100%;
                margin: 0 10px;
                padding: 20px;
            }
            
            /* Sidebar adjustments for medium screens */
            .sidebar-open .container {
                max-width: calc(100vw - 270px);
                margin: 0 10px 0 0;
            }
            
            .sidebar-collapsed .container {
                max-width: calc(100vw - 80px);
                margin: 0 10px 0 0;
            }
        }

        @media (max-width: 768px) {
            .container {
                margin: 0 5px;
                padding: 15px;
            }
            
            /* Hide sidebar adjustments on mobile - sidebar should be overlay */
            body.sidebar-open,
            body.sidebar-collapsed {
                margin-left: 0;
            }
            
            .sidebar-open .container,
            .sidebar-collapsed .container {
                max-width: 100%;
                margin: 0 5px;
            }
            
            .search-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .input-group input {
                width: 100%;
            }
            
            .case-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .controls {
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin: 0;
                padding: 10px;
                border-radius: 0;
            }
            
            .case-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            h1 {
                font-size: 24px;
            }
            
            .search-section {
                gap: 10px;
            }
            
            input[type="text"] {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Approved Names</h1>
        
        <div class="controls">
            <div class="entries-control">
                <select id="entriesSelect">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entries per page</span>
            </div>
            <button class="clear-btn" onclick="clearAll()">Clear</button>
        </div>

        <div class="search-section">
            <div class="input-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" placeholder="Enter first name">
            </div>
            
            <div class="input-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" placeholder="Enter last name">
            </div>
            
            <div class="input-group">
                <label for="caseNumber">Case Number</label>
                <input type="text" id="caseNumber" placeholder="Enter case number">
            </div>
            
            <button class="check-btn" onclick="checkCase()">Check</button>
            
            <div class="status-display" id="statusDisplay">
                Victor Wembanyana
            </div>
        </div>

        <button class="add-case-btn" onclick="addCaseNumber()">Add Case Number</button>

        <div class="case-grid" id="caseGrid">
            <!-- Grid items will be generated by JavaScript -->
        </div>
    </div>

    <script>
        let caseNumbers = [];
        let selectedCases = [];

        // Initialize the grid with 60 items (6 columns × 10 rows)
        function initializeGrid() {
            const grid = document.getElementById('caseGrid');
            grid.innerHTML = '';
            
            for (let i = 0; i < 60; i++) {
                const caseItem = document.createElement('div');
                caseItem.className = 'case-item';
                caseItem.textContent = caseNumbers[i] || 'C123456';
                caseItem.addEventListener('click', () => toggleCase(i));
                grid.appendChild(caseItem);
            }
        }

        // Add a new case number
        function addCaseNumber() {
            const caseNumberInput = document.getElementById('caseNumber');
            const caseNumber = caseNumberInput.value.trim();
            
            if (caseNumber) {
                // Find the first empty slot or add to the end
                let added = false;
                for (let i = 0; i < 60; i++) {
                    if (!caseNumbers[i] || caseNumbers[i] === 'C123456') {
                        caseNumbers[i] = caseNumber;
                        added = true;
                        break;
                    }
                }
                
                if (!added && caseNumbers.length < 60) {
                    caseNumbers.push(caseNumber);
                }
                
                updateGrid();
                caseNumberInput.value = '';
            } else {
                alert('Please enter a case number');
            }
        }

        // Update the grid display
        function updateGrid() {
            const grid = document.getElementById('caseGrid');
            const items = grid.children;
            
            for (let i = 0; i < items.length; i++) {
                items[i].textContent = caseNumbers[i] || 'C123456';
                if (selectedCases.includes(i)) {
                    items[i].classList.add('selected');
                } else {
                    items[i].classList.remove('selected');
                }
            }
        }

        // Toggle case selection
        function toggleCase(index) {
            const caseItem = document.querySelector(`.case-grid .case-item:nth-child(${index + 1})`);
            
            if (selectedCases.includes(index)) {
                selectedCases = selectedCases.filter(i => i !== index);
                caseItem.classList.remove('selected');
            } else {
                selectedCases.push(index);
                caseItem.classList.add('selected');
            }
        }

        // Check case function
        function checkCase() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const caseNumber = document.getElementById('caseNumber').value.trim();
            const statusDisplay = document.getElementById('statusDisplay');
            
            if (firstName || lastName || caseNumber) {
                // Simulate checking process
                statusDisplay.textContent = 'Checking...';
                statusDisplay.style.backgroundColor = '#ffc107';
                statusDisplay.style.color = '#333';
                
                setTimeout(() => {
                    if (firstName && lastName) {
                        statusDisplay.textContent = `${firstName} ${lastName}`;
                    } else if (caseNumber) {
                        statusDisplay.textContent = `Case: ${caseNumber}`;
                    } else {
                        statusDisplay.textContent = 'Incomplete Information';
                    }
                    statusDisplay.style.backgroundColor = '#28a745';
                    statusDisplay.style.color = 'white';
                }, 1000);
            } else {
                alert('Please enter at least one field to check');
            }
        }

        // Clear all fields
        function clearAll() {
            document.getElementById('firstName').value = '';
            document.getElementById('lastName').value = '';
            document.getElementById('caseNumber').value = '';
            document.getElementById('statusDisplay').textContent = 'Victor Wembanyana';
            selectedCases = [];
            updateGrid();
        }

        // Handle entries per page change
        document.getElementById('entriesSelect').addEventListener('change', function() {
            const entriesCount = parseInt(this.value);
            // This would typically affect pagination, but for this demo we'll keep the grid static
            console.log(`Changed to ${entriesCount} entries per page`);
        });

        // Sidebar detection and adjustment
        function detectSidebar() {
            const sidebar = document.querySelector('.sidebar, .side-nav, .sidenav, [class*="sidebar"], [class*="side-nav"]');
            const body = document.body;
            
            if (sidebar) {
                const sidebarWidth = sidebar.offsetWidth;
                const isCollapsed = sidebar.classList.contains('collapsed') || 
                                   sidebar.classList.contains('closed') || 
                                   sidebarWidth < 100;
                
                if (isCollapsed) {
                    body.classList.add('sidebar-collapsed');
                    body.classList.remove('sidebar-open');
                } else {
                    body.classList.add('sidebar-open');
                    body.classList.remove('sidebar-collapsed');
                }
            } else {
                body.classList.remove('sidebar-open', 'sidebar-collapsed');
            }
        }

        // Auto-detect sidebar changes
        function observeSidebarChanges() {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && 
                        (mutation.attributeName === 'class' || mutation.attributeName === 'style')) {
                        detectSidebar();
                    }
                });
            });
            
            // Observe the entire document for sidebar changes
            observer.observe(document.body, {
                attributes: true,
                childList: true,
                subtree: true,
                attributeFilter: ['class', 'style']
            });
        }

        // Initialize sidebar detection on page load
        window.addEventListener('load', function() {
            initializeGrid();
            detectSidebar();
            observeSidebarChanges();
        });

        // Re-detect sidebar on resize
        window.addEventListener('resize', detectSidebar);
    </script>
</body>
</html>
            

            @endsection