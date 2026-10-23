
// validate order form
$('form.validateForm').each(function (index, form) {
    var jForm = $(form);
    jForm.validate({
        focusInvalid: true,
        errorContainer: jForm.find('.errorBox'),
        errorLabelContainer: jForm.find('.errorBox ul'),
        wrapper: "li"
    });
});

$('input.priceFloatOnly').autoNumeric({
    aSep: '',
    altDec: ','
});

$('input.numbersOnly').autoNumeric({
    aSep: '',
    mDec: '0'
});


!function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0]
    , p = /^http:/.test(d.location) ? 'http' : 'https';
            if (!d.getElementById(id)) {
        js = d.createElement(s);
        js.id = id;
        js.src = p + '://platform.twitter.com/widgets.js';
        fjs.parentNode.insertBefore(js, fjs);
    }
}(document, 'script', 'twitter-wjs');

$('#navButton').click(function (e) {
    $('#mobileMenu ul').slideToggle(350);
    e.preventDefault();
});

$('#searchButton').click(function (e) {
    $('#searchBox').toggle(150);
    e.preventDefault();
});

$('#searchButtonMobile').click(function (e) {
    var q = trim($('#mobileQ').val());

    if (q.length > 0) {
        $('#mobileSearchForm').submit();
    }
    
    e.preventDefault();
});

$('.hasSubpages').mouseenter(function () {
    $(this).find('.subPages').show();
});

$('.hasSubpages').mouseleave(function () {
    $(this).find('.subPages').hide();
});

$('.loginLink').click(function(e) {
    $('#loginForm').toggle();
    e.preventDefault();
});

$('.notClickable').click(function (e) {e.preventDefault();});

// validate form
$('form.validateForm').each(function(index, form) {
    var jForm = $(form);
    jForm.validate({
        focusInvalid: true,
        errorContainer: jForm.find('.errorBox'),
        errorLabelContainer: jForm.find('.errorBox ul'),
        wrapper: "li"
    });
});
