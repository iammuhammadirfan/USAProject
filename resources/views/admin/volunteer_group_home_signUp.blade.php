@extends('admin.layout')
@section('content')
@section('view_volunteer_group_signups_styles')

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Name Selector Grid</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .name-badge {
            margin: 2px;
            padding: 8px 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .name-badge:hover {
            background-color: #218838;
        }
        .name-badge.selected {
            background-color: #dc3545;
        }
        .select-all-btn {
            background-color: #ffc107;
            color: #212529;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .select-all-btn:hover {
            background-color: #e0a800;
        }
        .form-control {
            border-radius: 4px;
            border: 2px solid #ccc;
        }
        .btn-cancel {
            background-color: #ffc107;
            color: #212529;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            font-weight: bold;
        }
        .btn-save {
            background-color: #ffc107;
            color: #212529;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            font-weight: bold;
        }
        .btn-cancel:hover, .btn-save:hover {
            background-color: #e0a800;
        }
        .names-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background-color: #f8f9fa;
        }
        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
@stop

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <!-- Name Selector Dropdown -->
                        <div class="mb-3">
                            <div class="dropdown">
                                <input type="text" 
                                       class="form-control dropdown-toggle" 
                                       id="nameSelector" 
                                       placeholder="Select Name" 
                                       readonly 
                                       data-bs-toggle="dropdown" 
                                       aria-expanded="false">
                                <ul class="dropdown-menu w-100" id="nameDropdown">
                                    <li><a class="dropdown-item" href="#" data-value="all">All Names</a></li>
                                    <li><a class="dropdown-item" href="#" data-value="recent">Recent Names</a></li>
                                    <li><a class="dropdown-item" href="#" data-value="favorites">Favorites</a></li>
                                    <li><a class="dropdown-item" href="#" data-value="alphabetical">Alphabetical</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Case Numbers Selected -->
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Case Numbers Selected</label>
                                    <input type="text" class="form-control" id="caseCount" value="0" readonly>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button class="select-all-btn w-100" id="selectAllBtn">Select All</button>
                                </div>
                            </div>
                        </div>

                        <!-- Loading indicator -->
                        <div class="loading" id="loadingIndicator" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading names...</p>
                        </div>

                        <!-- Names Container -->
                        <div class="names-container" id="namesContainer">
                            <!-- Names will be dynamically loaded here -->
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <button class="btn-cancel w-100" id="cancelBtn">Cancel</button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn-save w-100" id="saveBtn">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample data - in real application, this would come from your API
        const nameData = {
            all: [
                "John Doe", "Mike Smith", "Brad Smith", "Jack Tripper", "Janet Wood", "Chris Snow",
                "Ralf Furley", "Larry Dallas", "Cindy Snow", "Mike Seaver", "Steve Urkel", "Bobby Brown",
                "Ricky Bobby", "Chadwick Bos", "Simone Biles", "Jose Garcia", "Justin Lane", "Greg Smith",
                "Joey Glastone", "Mark Ruffalo", "Sunny Lee", "Esteban Men", "Kathy Hodge", "Robert Bern",
                "Danny Tanner", "Robert Down", "Mike Jackson", "Rick Carlisle", "Steve Yu", "Cristina Fren",
                "Jesse Cochran", "Steph Curry", "Aria Steph", "Sandy Miller", "Marvin Cao", "Linda Wings",
                "DJ Tanner", "Tony Parker", "Carly Jepson", "Cory Hart", "Jordan Lee", "Ryan Lam",
                "Kimmy Gibble", "Dale Jones", "Ari Tate", "Henry Cattle", "Ken Brown", "Wade Wilson",
                "Jackson Fuller", "Holly Price", "Lila Rose", "Josh Strong", "Van Morrison", "Cory Benton",
                "Steve Hale", "Harry Styles", "Johnni Rose", "Marc Reveri", "Elvis Presley", "Jace Talbot"
            ],
            recent: [
                "John Doe", "Mike Smith", "Brad Smith", "Jack Tripper", "Janet Wood", "Chris Snow",
                "Ralf Furley", "Larry Dallas", "Cindy Snow", "Mike Seaver"
            ],
            favorites: [
                "John Doe", "Mike Smith", "Steph Curry", "Harry Styles", "Elvis Presley", "Wade Wilson",
                "Tony Parker", "Greg Smith", "Danny Tanner", "Jesse Cochran"
            ],
            alphabetical: [
                "Aria Steph", "Ari Tate", "Bobby Brown", "Brad Smith", "Carly Jepson", "Chadwick Bos",
                "Chris Snow", "Cindy Snow", "Cory Benton", "Cory Hart", "Cristina Fren", "DJ Tanner",
                "Dale Jones", "Danny Tanner", "Elvis Presley", "Esteban Men", "Greg Smith", "Harry Styles",
                "Henry Cattle", "Holly Price", "Jack Tripper", "Jackson Fuller", "Jace Talbot", "Janet Wood",
                "Jesse Cochran", "Joey Glastone", "John Doe", "Johnni Rose", "Jordan Lee", "Jose Garcia",
                "Josh Strong", "Justin Lane", "Kathy Hodge", "Ken Brown", "Kimmy Gibble", "Larry Dallas",
                "Lila Rose", "Linda Wings", "Marc Reveri", "Mark Ruffalo", "Marvin Cao", "Mike Jackson",
                "Mike Seaver", "Mike Smith", "Ralf Furley", "Rick Carlisle", "Ricky Bobby", "Robert Bern",
                "Robert Down", "Ryan Lam", "Sandy Miller", "Simone Biles", "Steve Hale", "Steve Urkel",
                "Steve Yu", "Steph Curry", "Sunny Lee", "Tony Parker", "Van Morrison", "Wade Wilson"
            ]
        };

        class NameSelector {
            constructor() {
                this.selectedNames = new Set();
                this.currentDataset = 'all';
                this.init();
            }

            init() {
                this.bindEvents();
                this.loadNames('all');
            }

            bindEvents() {
                // Dropdown selection
                document.getElementById('nameDropdown').addEventListener('click', (e) => {
                    if (e.target.classList.contains('dropdown-item')) {
                        e.preventDefault();
                        const value = e.target.dataset.value;
                        document.getElementById('nameSelector').value = e.target.textContent;
                        this.currentDataset = value;
                        this.loadNames(value);
                    }
                });

                // Select All button
                document.getElementById('selectAllBtn').addEventListener('click', () => {
                    this.selectAll();
                });

                // Cancel button
                document.getElementById('cancelBtn').addEventListener('click', () => {
                    this.cancel();
                });

                // Save button
                document.getElementById('saveBtn').addEventListener('click', () => {
                    this.save();
                });
            }

            loadNames(dataset) {
                const container = document.getElementById('namesContainer');
                const loading = document.getElementById('loadingIndicator');
                
                // Show loading
                loading.style.display = 'block';
                container.innerHTML = '';

                // Simulate AJAX call
                setTimeout(() => {
                    const names = nameData[dataset] || nameData.all;
                    this.renderNames(names);
                    loading.style.display = 'none';
                }, 500);
            }

            renderNames(names) {
                const container = document.getElementById('namesContainer');
                const gridSize = this.calculateGridSize(names.length);
                
                let html = '<div class="row">';
                
                names.forEach((name, index) => {
                    const isSelected = this.selectedNames.has(name);
                    const selectedClass = isSelected ? 'selected' : '';
                    
                    html += `
                        <div class="col-md-${gridSize} col-sm-6 col-12 mb-2">
                            <button class="name-badge w-100 ${selectedClass}" 
                                    data-name="${name}" 
                                    onclick="nameSelector.toggleName('${name}')">
                                ${name}
                            </button>
                        </div>
                    `;
                });
                
                html += '</div>';
                container.innerHTML = html;
                this.updateCount();
            }

            calculateGridSize(count) {
                if (count <= 12) return 6;  // 2 columns
                if (count <= 24) return 4;  // 3 columns
                if (count <= 48) return 3;  // 4 columns
                return 2;  // 6 columns for 50+ items
            }

            toggleName(name) {
                if (this.selectedNames.has(name)) {
                    this.selectedNames.delete(name);
                } else {
                    this.selectedNames.add(name);
                }
                this.updateButtonState(name);
                this.updateCount();
            }

            updateButtonState(name) {
                const button = document.querySelector(`[data-name="${name}"]`);
                if (button) {
                    if (this.selectedNames.has(name)) {
                        button.classList.add('selected');
                    } else {
                        button.classList.remove('selected');
                    }
                }
            }

            selectAll() {
                const names = nameData[this.currentDataset] || nameData.all;
                const allSelected = names.every(name => this.selectedNames.has(name));
                
                if (allSelected) {
                    // Deselect all
                    names.forEach(name => this.selectedNames.delete(name));
                    document.getElementById('selectAllBtn').textContent = 'Select All';
                } else {
                    // Select all
                    names.forEach(name => this.selectedNames.add(name));
                    document.getElementById('selectAllBtn').textContent = 'Deselect All';
                }
                
                this.renderNames(names);
            }

            updateCount() {
                document.getElementById('caseCount').value = this.selectedNames.size;
                
                // Update Select All button text
                const names = nameData[this.currentDataset] || nameData.all;
                const allSelected = names.every(name => this.selectedNames.has(name));
                document.getElementById('selectAllBtn').textContent = allSelected ? 'Deselect All' : 'Select All';
            }

            cancel() {
                this.selectedNames.clear();
                this.loadNames(this.currentDataset);
                document.getElementById('nameSelector').value = 'Select Name';
                alert('Selection cancelled');
            }

            save() {
                const selectedArray = Array.from(this.selectedNames);
                console.log('Selected names:', selectedArray);
                
                // Here you would typically send the data to your server
                // Example AJAX call:
                /*
                fetch('/api/save-selection', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        selectedNames: selectedArray,
                        dataset: this.currentDataset
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert('Selection saved successfully!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving selection');
                });
                */
                
                alert(`Saved ${selectedArray.length} selected names:\n${selectedArray.join(', ')}`);
            }
        }

        // Initialize the name selector when the page loads
        let nameSelector;
        document.addEventListener('DOMContentLoaded', function() {
            nameSelector = new NameSelector();
        });
    </script>



