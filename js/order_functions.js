/**
 * add a product to the basket
 */
$('form.addProductToBasket').submit(function(e) {
    var thisElement = $(this);
    $.ajax({
        type: "POST",
        url: thisElement.attr('action'),
        data: thisElement.serialize() + '&ajax=1',
        success: function(data) {
            var dataObj = eval('(' + data + ')');

            if (dataObj.success == true) {
                $('div#miniBasket').html(dataObj.miniBasketContent);
                openMiniBasket();
                $('.cart-button-amount').html(dataObj.countProducts);
            }
            if (dataObj.orderStatusUpdate) {
                showStatusUpdate(dataObj.orderStatusUpdate, thisElement.find('.orderStatusUpdate'));
            }
        }
    });
    e.preventDefault();
});

/**
 * remove a product from the basket
 * @param {javascript element} form
 * @returns {Boolean}
 */
function removeProductFromBasket(form) {
    var thisElement = $(form);
    $.ajax({
        type: "POST",
        url: thisElement.attr('action'),
        data: thisElement.serialize() + '&removeProductFromBasket=1&ajax=1',
        success: function(data) {
            var dataObj = eval('(' + data + ')');

            if (dataObj.success == true) {
                $('div#miniBasket').html(dataObj.miniBasketContent);
            }
            if (dataObj.orderStatusUpdate) {
                showStatusUpdate(dataObj.orderStatusUpdate, thisElement.find('.orderStatusUpdate'));
            }
            $('.cart-button-amount').html(dataObj.countProducts);
        }
    });
    return false;
}

$('.cart-button').click(function() {
    if ($('.shopping-cart').hasClass('cart-opened')) {
        closeMiniBasket();
    } else {
        openMiniBasket();
    }
});

function openMiniBasket() {
    $('.shopping-cart').slideDown('fast').addClass('cart-opened');
}

function closeMiniBasket(speed) {
    if ($('.shopping-cart').hasClass('cart-opened')) {
        $('.shopping-cart').slideUp((typeof speed != 'undefined' ? speed : 'slow'), function() {
            $(this).removeAttr('style');
        }).removeClass('cart-opened');
    }
}