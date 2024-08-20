<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquêteur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        .form-container h2 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="file"],
        input[type="text"] {
            display: block;
            margin: 10px 0;
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            padding: 10px 15px;
            border: none;
            background-color: #5cb85c;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }
        .error-message{
            color: red;
            margin-bottom: 15px;
            display: none;
        }

        .logo {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Logo -->
        <img src="one.png" alt="One to One Logo" class="logo" width="200" height="200">

        <h2>Vérification</h2>
        <div id="error-message" class="error-message"></div>
        <form id="form" enctype="multipart/form-data">
            <label for="cin">CIN:</label>
            <input type="text" id="numcin" name="numcin" required> 
            <input type="file" id="cin" name="cin" accept="image/*" required>
            
            <label for="contract">Scan Contract:</label>
            <input type="file" id="contract" name="contract" accept="image/*" required>
            
            <label for="project">Projet:</label>
            <input type="text" id="project" name="project" required>
            
            <label for="validated">La formation est-elle validée?</label>
            <input type="checkbox" id="validated" name="validated">
            <br>
            <button type="submit">Submit</button>
        </form>
    </div>

    <script>
        document.getElementById('form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            const errorMessageDiv = document.getElementById('error-message');

            xhr.open('POST', 'http://localhost/APLL/submit_form.php', true);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        // Redirect to success page
                        window.location.href = "success.html";
                    } else {
                        // Display the error message
                        errorMessageDiv.textContent = response.message;
                        errorMessageDiv.style.display = 'block';
                    }
                } else {
                    errorMessageDiv.textContent = 'An error occurred: ' + xhr.statusText;
                    errorMessageDiv.style.display = 'block';
                }
            };

            xhr.send(formData);
        });
    </script>
</body>
</html>