<div class="container">
    <div class="loading">
        <div class="loading-spinner"></div>
    </div>
    
    <h1 class="mb-4">JSON Processor</h1>
    
    <form id="jsonForm">
        <div class="form-group mb-3">
            <label for="url1">API URL 1</label>
            <input type="url" class="form-control" id="url1" name="url1" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="url2">API URL 2</label>
            <input type="url" class="form-control" id="url2" name="url2" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Get Data</button>
    </form>
    
    <div id="improvements" class="mt-4 card" style="display:none;">
        <div class="card-header bg-info text-white">
            <h3 class="mb-0">Available Improvements</h3>
        </div>
        <div class="card-body">
            <div id="improvementsList"></div>
            <button id="applyImprovements" class="btn btn-success mt-3">Apply Selected Improvements</button>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">JSON 1</h3>
                </div>
                <div class="card-body">
                    <pre id="json1" class="json-display"></pre>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">JSON 2</h3>
                </div>
                <div class="card-body">
                    <pre id="json2" class="json-display"></pre>
                </div>
            </div>
        </div>
    </div>
</div>