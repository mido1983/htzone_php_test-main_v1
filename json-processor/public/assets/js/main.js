$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Form submission
    $('#jsonForm').on('submit', function(e) {
        e.preventDefault();
        
        const url1 = $('#url1').val();
        const url2 = $('#url2').val();
        
        // Show loading indicator
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
                    // Display JSON data
                    $('#json1').text(JSON.stringify(response.data.original.data1, null, 2));
                    $('#json2').text(JSON.stringify(response.data.original.data2, null, 2));
                    
                    // Show improvements section if suggestions exist
                    if (response.data.analysis.data1.issues.length > 0 || 
                        response.data.analysis.data2.issues.length > 0) {
                        $('#improvements').show();
                        
                        // Update improvement checkboxes based on suggestions
                        updateImprovementOptions(response.data.analysis);
                    }
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('Error processing request: ' + error);
            },
            complete: function() {
                $('.loading').hide();
            }
        });
    });
    
    // Apply improvements
    $('#applyImprovements').on('click', function() {
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
                    $('#json1').text(JSON.stringify(response.data.improvements.improved_data.data1, null, 2));
                    $('#json2').text(JSON.stringify(response.data.improvements.improved_data.data2, null, 2));
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('Error applying improvements: ' + error);
            },
            complete: function() {
                $('.loading').hide();
            }
        });
    });
    
    function showError(message) {
        const errorDiv = $('<div>')
            .addClass('error-message')
            .text(message);
        $('#jsonForm').after(errorDiv);
    }
    
    function updateImprovementOptions(analysis) {
        const $improvementsList = $('#improvementsList').empty();
        
        if (analysis.data1.issues.length > 0 || analysis.data2.issues.length > 0) {
            const issues = [...new Set([...analysis.data1.issues, ...analysis.data2.issues])];
            
            issues.forEach(issue => {
                const $option = $('<div>').addClass('improvement-option form-check');
                $option.append(`
                    <input class="form-check-input" type="checkbox" name="improvements[]" value="${issue.type}" id="check_${issue.type}">
                    <label class="form-check-label" for="check_${issue.type}">
                        ${issue.message}
                    </label>
                `);
                $improvementsList.append($option);
            });
        }
    }
}); 