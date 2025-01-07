<div class="container">
    <div class="loading">
        <div class="loading-spinner"></div>
    </div>
    
    <h1 class="mb-4">JSON Processor</h1>
    
    <form id="jsonForm">
        <div class="form-group">
            <label for="url1">API URL 1</label>
            <input type="url" class="form-control" id="url1" name="url1" required>
        </div>
        
        <div class="form-group">
            <label for="url2">API URL 2</label>
            <input type="url" class="form-control" id="url2" name="url2" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Get Data</button>
    </form>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <h3>JSON 1</h3>
            <pre id="json1" class="json-display"></pre>
        </div>
        <div class="col-md-6">
            <h3>JSON 2</h3>
            <pre id="json2" class="json-display"></pre>
        </div>
    </div>
    
    <div id="improvements" class="mt-4" style="display:none;">
        <h3>Available Improvements</h3>
        <div id="improvementsList"></div>
        <button id="applyImprovements" class="btn btn-success mt-3">Apply Selected Improvements</button>
    </div>
</div>