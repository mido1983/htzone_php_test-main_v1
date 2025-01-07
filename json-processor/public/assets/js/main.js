$(document).ready(function() {
    let currentData = null;
    
    $('#jsonForm').on('submit', function(e) {
        e.preventDefault();
        processJsonData();
    });
    
    $('#applyImprovements').on('click', function() {
        applyImprovements();
    });
    
    function processJsonData() {
        const url1 = $('#url1').val();
        const url2 = $('#url2').val();
        
        // Show loading
        $('.loading').css('display', 'flex');
        
        // Clear previous results
        $('#json1, #json2').empty();
        $('#improvements').hide();
        $('.error-message').remove();
        
        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: {
                url1: url1,
                url2: url2,
                improve: true
            },
            success: function(response) {
                if (response.success) {
                    currentData = response.data;
                    displayResults(response.data);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('Error: ' + error);
            },
            complete: function() {
                $('.loading').hide();
            }
        });
    }
    
    function displayResults(data) {
        // Display JSON data
        $('#json1').text(JSON.stringify(data.original.data1, null, 2));
        $('#json2').text(JSON.stringify(data.original.data2, null, 2));
        
        // Handle improvements section
        if (data.improvements && data.improvements.suggestions.length > 0) {
            displayImprovements(data.improvements.suggestions);
        }
    }
    
    function displayImprovements(suggestions) {
        const $improvementsList = $('#improvementsList').empty();
        
        suggestions.forEach(suggestion => {
            const $card = $('<div>').addClass('card mb-3');
            
            // Card header
            const $header = $('<div>').addClass('card-header bg-info text-white');
            $header.append(`<h5 class="mb-0">${suggestion.message}</h5>`);
            
            // Card body
            const $body = $('<div>').addClass('card-body');
            
            // Add details
            if (suggestion.details) {
                suggestion.details.forEach(detail => {
                    const $detail = $('<div>').addClass('mb-3');
                    $detail.append(`<h6 class="font-weight-bold">${detail.issue}</h6>`);
                    $detail.append(`<p>${detail.description || ''}</p>`);
                    
                    if (detail.recommendation) {
                        $detail.append(`
                            <div class="alert alert-info">
                                <strong>Recommendation:</strong> ${detail.recommendation}
                            </div>
                        `);
                    }
                    
                    // Add example if available
                    if (detail.example || suggestion.example) {
                        const example = detail.example || suggestion.example;
                        $detail.append(`
                            <div class="example-code">
                                <h6>Example:</h6>
                                <pre class="bg-light p-2 rounded"><code>${JSON.stringify(example, null, 2)}</code></pre>
                            </div>
                        `);
                    }
                    
                    $body.append($detail);
                });
            }
            
            // Add checkbox for applying improvement
            const $checkbox = $(`
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" 
                           name="improvements[]" value="${suggestion.type}" 
                           id="check_${suggestion.type}">
                    <label class="form-check-label" for="check_${suggestion.type}">
                        Apply this improvement
                    </label>
                </div>
            `);
            
            $body.append($checkbox);
            $card.append($header, $body);
            $improvementsList.append($card);
        });
        
        $('#improvements').show();
    }
    
    function getImprovementHeader(type) {
        switch (type) {
            case 'duplicates':
                return '🔄 Duplicate Records';
            case 'format':
                return '🔤 Key Format Inconsistencies';
            default:
                return type.charAt(0).toUpperCase() + type.slice(1);
        }
    }
    
    function getImprovementMessage(suggestion) {
        switch (suggestion.type) {
            case 'duplicates':
                return `Found duplicate records in dataset ${suggestion.dataset}`;
            case 'format':
                return `Inconsistent key formats detected (${suggestion.formats.dataset1} vs ${suggestion.formats.dataset2})`;
            default:
                return suggestion.message;
        }
    }
    
    function applyImprovements() {
        if (!currentData) return;
        
        const improvements = [];
        $('input[name="improvements[]"]:checked').each(function() {
            improvements.push($(this).val());
        });
        
        if (improvements.length === 0) {
            showError('Please select at least one improvement option');
            return;
        }
        
        $('.loading').css('display', 'flex');
        
        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: {
                url1: $('#url1').val(),
                url2: $('#url2').val(),
                improve: true,
                improvements: improvements
            },
            success: function(response) {
                if (response.success) {
                    currentData = response.data;
                    $('#json1').text(JSON.stringify(response.data.improvements.improved_data.data1, null, 2));
                    $('#json2').text(JSON.stringify(response.data.improvements.improved_data.data2, null, 2));
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('Error: ' + error);
            },
            complete: function() {
                $('.loading').hide();
            }
        });
    }
    
    function showError(message) {
        const errorDiv = $('<div>')
            .addClass('alert alert-danger mt-3')
            .text(message);
        $('#jsonForm').after(errorDiv);
    }
}); 