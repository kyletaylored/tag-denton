<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tag Denton - Generate Links</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#">Tag Denton</a>
        </div>
    </nav>

    <div class="container shadow p-4 mt-5 bg-white rounded">
        <header class="text-center mb-4">
            <h1 class="text-success">Generate Tag Denton Links</h1>
            <p>Create quick-access links for Denton's landmarks.</p>
        </header>

        <div class="content text-center">
            <p>Paste one or more URLs (one per line) to generate Tag Denton links.</p>
            <div class="mb-3">
                <textarea id="urls" class="form-control" placeholder="Paste URLs here, one per line" rows="5"></textarea>
            </div>
            <button onclick="generateLinks()" class="btn btn-success mb-3">Generate Links</button>
            <div id="multiResult" class="table-responsive"></div>
        </div>
    </div>

    <footer class="text-center mt-5 text-muted">
        <p>&copy; 2024 Tag Denton. All rights reserved.</p>
    </footer>

    <script>
        async function generateLinks() {
            const urls = document.getElementById("urls").value.trim().split('\n');
            let resultTable = `
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Tag Denton Link</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            for (const url of urls) {
                try {
                    const response = await fetch('/proxy', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ url: url.trim() })
                    });

                    const data = await response.json();

                    if (data.key) {
                        const redirectUrl = `${window.location.origin}/redirect/${data.key}`;
                        resultTable += `
                            <tr>
                                <td><a href="${redirectUrl}" target="_blank">${redirectUrl}</a></td>
                            </tr>
                        `;
                    } else {
                        resultTable += `
                            <tr>
                                <td class="text-danger">Failed to generate link for: ${url}</td>
                            </tr>
                        `;
                    }
                } catch (error) {
                    resultTable += `
                        <tr>
                            <td class="text-danger">Error processing: ${url}</td>
                        </tr>
                    `;
                }
            }

            resultTable += '</tbody></table>';
            document.getElementById("multiResult").innerHTML = resultTable;
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
