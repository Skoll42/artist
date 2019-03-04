Date.prototype.addSearchDays = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
}

let dateSearchRange = new Date();

$('.text-date-search input').daterangepicker({
    autoclose: true,
    todayHighlight: true,
    autoUpdateInput: false,
    todayBtn: "linked",
    theme: "bootstrap4",
    orientation: "bottom",
    minDate: dateSearchRange,
    maxDate: dateSearchRange.addSearchDays(90),
    locale: {
        format: 'DD-MM-YYYY'
    }
}, function(start_date, end_date) {
    this.element.val(start_date.format('DD-MM-YYYY')+' - '+end_date.format('DD-MM-YYYY'));
});

$('#search-date').focus(function(){
    if (window.navigator.msMaxTouchPoints || 'ontouchstart' in document) {
        $('#search-date').blur();
    }
});

$('#search-date').keypress(function(e){
    var keyCode = e.which;

    if ((keyCode >= 33 && keyCode <= 253)
        || (keyCode == 8)
        || (keyCode == 32)) {
        e.preventDefault();
    }
});

$('.search-btn').on('click', function (e) {
    e.preventDefault();
    if(!searchValidate()) {
        return false;
    }

    let _form = $(this).parents('form');
    _form.submit();
});

$('[name="keyWord"]').keyup(function(e){
    let _this = this;
    if(e.keyCode == 13) {
        if(!searchValidate()) {
            return false;
        }
        $(_this).parents('form').submit();
    }
});

function searchValidate() {
    let validation = false;
    let oldKeyWord = $.trim($('[name="oldKeyWord"]').val());
    let oldLocation = $.trim($('[name="oldLocation"]').val());
    let oldCategory = $.trim($('[name="oldCategory"]').val());
    let oldDateRange = $.trim($('[name="oldDateRange"]').val());

    let keyWord = $.trim($('[name="keyWord"]').val());
    let location = $.trim($('[name="location"]').val());
    let category = $.trim($('[name="category"]').val());
    let dateRange = $.trim($('[name="dateRange"]').val());

    if(keyWord != '' || location != '' || category != '' || dateRange != '') {
        validation = true;
    }

    if((oldKeyWord != keyWord) || (oldCategory != category) || (oldLocation != location) || (oldDateRange != dateRange)) {
        updateQueryStringParam('maxPrice', 0);
        updateQueryStringParam('priceFrom', '');
        updateQueryStringParam('priceTo', '');
    }

    return validation;
}

$('.tag-search').on('change', function (e) {

    let tagsId = [];
    $('.tag-search').each(function (k, elem) {
        if($(elem).is(':checked')) {
            tagsId.push($(elem).val());
        }
    });

    $('.search-tags').val(tagsId.toString());

    $('.search-btn').click();
});

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
    $('[name="' + param + '"]').val(value);
}

