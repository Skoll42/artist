require('pc-bootstrap4-datetimepicker/src/js/bootstrap-datetimepicker.js');

/*
 * Switch list view - grid view
*/
$('.list-grid-switcher').on('click', function (e) {

    let buttonGridClassName = "icon-grid-list";
    let buttonListClassName = "icon-item-list";

    let resultListClassName = "result-list";
    let resultGridClassName = "result-grid";

    let currentClass = $(this).find('i');

    if(currentClass && currentClass.hasClass( buttonGridClassName )){  //change to list
        currentClass.removeClass(buttonGridClassName).addClass(buttonListClassName);
        $('.result-box').removeClass(resultGridClassName).addClass(resultListClassName).find('.result-item').removeClass('col-md-6').addClass('col-md-12');
        updateQueryStringParam('type', 'list');
    } else if (currentClass && currentClass.hasClass( buttonListClassName)) { //change to grid
        currentClass.removeClass(buttonListClassName).addClass(buttonGridClassName);
        $('.result-box').removeClass(resultListClassName).addClass(resultGridClassName).find('.result-item').removeClass('col-md-12').addClass('col-md-6');
        updateQueryStringParam('type', 'grid');
    }
});

// Explicitly save/update a url parameter using HTML5's replaceState().
function updateQueryStringParam(param, value) {
    let baseUrl = [location.protocol, '//', location.host, location.pathname].join('');
    let urlQueryString = document.location.search;
    let newParam = param + '=' + value,
        params = '?' + newParam;

    // If the "search" string exists, then build params from it
    if (urlQueryString) {
        let keyRegex = new RegExp('([\?&])' + param + '[^&]*');
        // If param exists already, update it
        if (urlQueryString.match(keyRegex) !== null) {
            params = urlQueryString.replace(keyRegex, "$1" + newParam);
        } else { // Otherwise, add it to end of query string
            params = urlQueryString + '&' + newParam;
        }
    }
    window.history.replaceState({}, "", baseUrl + params);
    $('.search-list-type').val(value);
}
