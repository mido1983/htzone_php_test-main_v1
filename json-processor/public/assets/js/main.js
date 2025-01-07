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
        
        // Group suggestions by type
        const groupedSuggestions = suggestions.reduce((acc, suggestion) => {
            if (!acc[suggestion.type]) {
                acc[suggestion.type] = [];
            }
            acc[suggestion.type].push(suggestion);
            return acc;
        }, {});
        
        // Create improvement options
        Object.entries(groupedSuggestions).forEach(([type, items]) => {
            const $group = $('<div>').addClass('improvement-group mb-3');
            
            // Create header based on type
            const header = getImprovementHeader(type);
            $group.append(`<h4 class="mb-2">${header}</h4>`);
            
            // Add individual suggestions
            items.forEach(suggestion => {
                const $option = $('<div>').addClass('improvement-option form-check');
                $option.append(`
                    <input class="form-check-input" type="checkbox" 
                           name="improvements[]" value="${suggestion.type}" 
                           id="check_${suggestion.type}_${suggestion.dataset || ''}">
                    <label class="form-check-label" for="check_${suggestion.type}_${suggestion.dataset || ''}">
                        ${getImprovementMessage(suggestion)}
                    </label>
                `);
                $group.append($option);
            });
            
            $improvementsList.append($group);
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