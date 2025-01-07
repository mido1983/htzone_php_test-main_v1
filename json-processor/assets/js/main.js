$(document).ready(function() {
    let currentData = null;
    
    $('#jsonForm').on('submit', function(e) {
        e.preventDefault();
        
        const url1 = $('#url1').val();
        const url2 = $('#url2').val();
        
        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: {
                url1: url1,
                url2: url2,
                improve: true
            },
            success: function(response) {
                currentData = response;
                
                $('#json1').text(JSON.stringify(response.data1, null, 2));
                $('#json2').text(JSON.stringify(response.data2, null, 2));
                
                // Show improvement suggestions
                if (response.suggestions && response.suggestions.length > 0) {
                    const $suggestions = $('<div class="alert alert-info">');
                    response.suggestions.forEach(suggestion => {
                        $suggestions.append(`<p>${suggestion.message}</p>`);
                    });
                    $('#improvements').before($suggestions);
                }
                
                $('#improvements').show();
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });
    
    $('#applyImprovements').on('click', function() {
        if (!currentData) return;
        
        const improvements = [];
        if ($('#checkDuplicates').is(':checked')) improvements.push('duplicates');
        if ($('#checkFormat').is(':checked')) improvements.push('format');
        
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
                currentData = response;
                $('#json1').text(JSON.stringify(response.data1, null, 2));
                $('#json2').text(JSON.stringify(response.data2, null, 2));
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });
});