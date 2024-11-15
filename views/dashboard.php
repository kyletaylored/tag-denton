<ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="generate-tab" data-bs-toggle="tab" data-bs-target="#generate" type="button" role="tab" aria-controls="generate" aria-selected="true">
            Generate Links
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="view-links-tab" data-bs-toggle="tab" data-bs-target="#view-links" type="button" role="tab" aria-controls="view-links" aria-selected="false">
            View All Links
        </button>
    </li>
</ul>

<div class="tab-content mt-3">
    <!-- Generate Links Tab -->
    <div class="tab-pane fade show active" id="generate" role="tabpanel" aria-labelledby="generate-tab">
        <div class="content">
            <p>Paste one or more URLs (one per line) to generate Tag Denton links.</p>
            <textarea id="urls" class="form-control mb-3" rows="5" placeholder="Paste URLs here, one per line"></textarea>
            <button id="generateLinks" class="btn btn-success mb-3">Generate Links</button>
            <div id="loading" class="text-center" style="display: none;">Processing... Please wait.</div>
            <div id="multiResult" class="table-responsive">
                <table id="linksTable" class="table table-bordered table-hover" style="display: none;">
                    <thead>
                        <tr>
                            <th>Original URL</th>
                            <th>Tag Denton Link</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View All Links Tab -->
    <div class="tab-pane fade" id="view-links" role="tabpanel" aria-labelledby="view-links-tab">
        <div class="content">
            <h3>All Generated Links</h3>
            <div id="viewLoading" class="text-center" style="display: none;">Loading links...</div>
            <div id="viewLinksTableWrapper" class="table-responsive" style="display: none;">
                <table id="viewLinksTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Original URL</th>
                            <th>Tag Denton Link</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    const generateLinksButton = document.getElementById("generateLinks");
    const loadingIndicator = document.getElementById("loading");
    const linksTable = $("#linksTable").DataTable();

    const viewLinksTable = $("#viewLinksTable").DataTable();
    const viewLoadingIndicator = document.getElementById("viewLoading");
    const viewLinksTableWrapper = document.getElementById("viewLinksTableWrapper");

    generateLinksButton.addEventListener("click", () => {
        const urls = document.getElementById("urls").value.trim().split("\n").filter(Boolean);

        if (urls.length === 0) {
            alert("Please enter at least one URL.");
            return;
        }

        loadingIndicator.style.display = "block";

        const fetchPromises = urls.map((url) => {
            return fetch("/proxy", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ url })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.key) {
                        const redirectUrl = `${window.location.origin}/redirect/${data.key}`;
                        linksTable.row.add([url, `<a href="${redirectUrl}" target="_blank">${redirectUrl}</a>`]).draw(false);
                    } else {
                        linksTable.row.add([url, `<span class="text-danger">Error generating link</span>`]).draw(false);
                    }
                })
                .catch(() => {
                    linksTable.row.add([url, `<span class="text-danger">Error processing link</span>`]).draw(false);
                });
        });

        Promise.all(fetchPromises).then(() => {
            loadingIndicator.style.display = "none";
            $("#linksTable").show();
        });
    });

    $("#view-links-tab").on("shown.bs.tab", function () {
        if (viewLinksTableWrapper.style.display === "none") {
            viewLoadingIndicator.style.display = "block";

            fetch("/admin/links")
                .then(response => response.json())
                .then(data => {
                    viewLinksTable.clear();

                    data.forEach(link => {
                        const redirectUrl = `${window.location.origin}/redirect/${link.key}`;
                        viewLinksTable.row.add([link.url, `<a href="${redirectUrl}" target="_blank">${redirectUrl}</a>`]).draw(false);
                    });

                    viewLoadingIndicator.style.display = "none";
                    viewLinksTableWrapper.style.display = "block";
                })
                .catch(() => {
                    viewLoadingIndicator.innerText = "Error loading links.";
                });
        }
    });
});
</script>
