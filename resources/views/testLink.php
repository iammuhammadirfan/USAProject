<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submission</title>
    <style>
        .progress-container {
            width: 100%;
            background-color: #f1f1f1;
            margin-top: 10px;
            display: none;
        }
        .progress-bar {
            width: 0%;
            height: 20px;
            background-color: #4CAF50;
            text-align: center;
            line-height: 20px;
            color: white;
        }
        .error {
            color: red;
            font-size: 0.8em;
        }
        .success-message {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Submit Your Information</h1>
    
    <form id="dataForm">
        @csrf
        
        <div>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <span class="error" id="nameError"></span>
        </div>
        
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <span class="error" id="emailError"></span>
        </div>
        
        <div>
            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>
            <span class="error" id="messageError"></span>
        </div>
        
        <button type="submit">Submit</button>
        
        <div class="progress-container" id="progressContainer">
            <div id="progressBar" class="progress-bar">0%</div>
        </div>
        
        <div id="responseMessage" class="success-message"></div>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataForm').on('submit', function(e) {
                e.preventDefault();
                
                // Reset errors
                $('.error').text('');
                $('#responseMessage').text('').removeClass('success error');
                
                // Show progress
                $('#progressContainer').show();
                updateProgress(0);
                
                // Get form data
                let formData = $(this).serialize();
                
                // AJAX request
                let xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("submit.form") }}', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                
                // Progress tracking
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        let percent = Math.round((e.loaded / e.total) * 100);
                        updateProgress(percent);
                    }
                });
                
                xhr.onload = function() {
                    $('#progressContainer').hide();
                    
                    let response = JSON.parse(xhr.responseText);
                    
                    if (xhr.status == 422) {
                        // Validation errors
                        $.each(response.errors, function(key, value) {
                            $('#' + key + 'Error').text(value[0]);
                        });
                    } else if (xhr.status == 200 && response.success) {
                        // Success
                        $('#dataForm')[0].reset();
                        $('#responseMessage').text(response.message).addClass('success');
                    } else {
                        // Other errors
                        $('#responseMessage').text(response.message || 'Error submitting form').addClass('error');
                    }
                };
                
                xhr.onerror = function() {
                    $('#progressContainer').hide();
                    $('#responseMessage').text('Network error occurred').addClass('error');
                };
                
                xhr.send(formData);
            });
            
            function updateProgress(percent) {
                $('#progressBar').css('width', percent + '%').text(percent + '%');
            }
        });
    </script>
</body>
</html>