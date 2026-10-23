/* 
 * All kind of standard widely used functions
 */

/**
 * set a cookie by name, value and expiration days after now
 * @param cookie_name KEY of the cookie
 * @param value
 * @param expire_days
 * @param path specific path wherefore the cookie is used (optional)
 */
function setCookie(cookie_name, value, expire_days, path)
{
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expire_days);
    var c_value = escape(value) + ((expire_days == null) ? "" : "; expires=" + exdate.toUTCString());
    c_value += (path ? 'path=' + path : 'path=/')
    document.cookie = cookie_name + "=" + c_value;
}

/**
 * get a cookie by name
 * @param cookie_name KEY of the cookie
 */
function getCookie(cookie_name) {
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i = 0; i < ARRcookies.length; i++)
    {
        x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
        y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
        x = x.replace(/^\s+|\s+$/g, "");
        if (x == cookie_name)
        {
            return unescape(y);
        }
    }
    return null;
}

/**
 * do show cookie warnin if necessary
 * @param message
 */
function showCookieWarning(message) {
    setCookie("testCookie", "test", 1);

    if (getCookie("testCookie") != "test") {
        alert(message);
    }

}

/**
 * log value to console
 * @param sValue mixed
 */
function _d(mValue) {
    if (console) {
        if (console.log) {
            console.log(mValue);
        }
    } else {
        return false;
    }
    return true;
}

/**
 * fit text to the height of an element within the element where this plugin is used on (could be buggy with borders, not tested yet)
 */
(function($) {
    $.fn.fitTextToHeight = function(options) {

        var defaults = {
            accuracy: '5', // number of character per cycle discarded (exclusive moreSign length)
            moreSign: '&hellip;', // sign shown after discarded text
            paragraphTag: 'p' // element to fit text from
        };

        var o = $.extend(defaults, options);

        return this.each(function(index) {

            var height = $(this).height();
            var maxH = height;

            var Helements = 0;

            // count height of all elements that are children of parent of paragraphTag
            $(this).children(':not(' + o.paragraphTag + ')').each(function() {
                Helements += $(this).outerHeight(true);
            });

            var p = $(this).children(o.paragraphTag);
            var maxHp = maxH - Helements;

            $(this).append('<div id="moreSignLenghtTest_' + index + '" style="display:none;"></div>');
            var moreSignLength = $('#moreSignLenghtTest_' + index).html(o.moreSign).html().length;
            $('#moreSignLenghtTest_' + index).remove();

            // accuracy smaller then 1 creates infinite loop
            if (o.accuracy < 1)
                o.accuracy = 1;

            var currHp = p.outerHeight(true);
            var pText = p.html();
            p.data('original', pText); // save original in data attribute
            while (currHp > maxHp) {
                p.html(pText.slice(0, -(moreSignLength + parseInt(o.accuracy))) + o.moreSign);
                currHp = p.outerHeight(true);
                pText = p.html();
            }
        });
    };
})(jQuery);

/**
 * make simple tabs but keep symantics intact
 */
(function($) {
    $.fn.prestatieTabs = function(options) {

        var defaults = {
            tabsHolder: '.tabsHolder', // holder for the generated tabs
            tabDetails: '.tabDetails', // holder for tab title and tab
            tabLabel: '.tabLabel', // label used for text in generated tab 
            tabContent: '.tabContent', // content to show on click tab
            tabClass: 'tab', // content to show on click tab (! do not take same name as tabLabel!)
            onAfterTabOpen: function() {
            }
        };

        var o = $.extend(defaults, options);

        // open single tab and close all others
        function openTab(element, tabsContainer, o) {
            var tabIndex = element.attr('data-prestatietabs-index');
            tabsContainer.find('.' + o.tabClass + ',' + o.tabLabel).removeClass('active'); // remove class active states for all tabs and labels
            tabsContainer.find(o.tabContent).removeClass('opened'); // close all content parts
            tabsContainer.find(o.tabContent + '[data-prestatietabs-index="' + tabIndex + '"]').addClass('opened'); // open specific tab
            tabsContainer.find('.' + o.tabClass + '[data-prestatietabs-index="' + tabIndex + '"]' + ',' + o.tabLabel + '[data-prestatietabs-index="' + tabIndex + '"]').addClass('active'); // add class active to tab and label

            o.onAfterTabOpen.call(this, element);
        }

        // open tab if closed and vice versa
        function openCloseTab(element, tabsContainer, o) {
            var tabIndex = element.attr('data-prestatietabs-index');
            if (element.hasClass('active')) {
                tabsContainer.find(o.tabContent + '[data-prestatietabs-index="' + tabIndex + '"]').removeClass('opened'); // open specific tab
                tabsContainer.find('.' + o.tabClass + '[data-prestatietabs-index="' + tabIndex + '"]' + ',' + o.tabLabel + '[data-prestatietabs-index="' + tabIndex + '"]').removeClass('active'); // add class active to tab and label
            } else {
                tabsContainer.find(o.tabContent + '[data-prestatietabs-index="' + tabIndex + '"]').addClass('opened'); // open specific tab
                tabsContainer.find('.' + o.tabClass + '[data-prestatietabs-index="' + tabIndex + '"]' + ',' + o.tabLabel + '[data-prestatietabs-index="' + tabIndex + '"]').addClass('active'); // add class active to tab and label

                o.onAfterTabOpen.call(this, element);
            }
        }

        return this.each(function(index) {
            var tabsContainer = $(this);
            var tabsHolder = tabsContainer.find(o.tabsHolder);
            var tabsDetails = tabsContainer.find(o.tabDetails);

            // generate tabs based on labels
            var tabsHTML = '<ul class="cf">';
            for (i = 0; i < tabsDetails.size(); i++) {
                var tabDetail = $(tabsDetails[i]);
                tabsHTML += '<li data-prestatietabs-name="' + tabDetail.attr('data-prestatietabs-name') + '" data-prestatietabs-index="' + i + '" class="' + o.tabClass + '"><span class="label">' + tabDetail.find(o.tabLabel).html() + '</span></li>';
                tabDetail.find(o.tabLabel).attr('data-prestatietabs-index', i);
                tabDetail.find(o.tabContent).attr('data-prestatietabs-index', i);
            }

            // add tabs to holder
            tabsHolder.html(tabsHTML + '</ul>');

            // make generated tabs and original labels clickable
            tabsContainer.find('li.' + o.tabClass).click(function(e) {
                openTab($(this), tabsContainer, o);
                e.preventDefault();
            });
            tabsContainer.find(o.tabLabel).click(function(e) {
                openCloseTab($(this), tabsContainer, o);
                e.preventDefault();
            });

            tabsHolder.find('li:first').click();
        });
    };
})(jQuery);

/**
 * show the status update
 * @param message string
 */
function showStatusUpdate(message, element) {

    if (!element)
        element = '#statusUpdate';

    var mb = new Array();
    $(element).each(function(index, element) {
        // message box
        mb[index] = $(element)
                .html(message)
                .stop(true, true).fadeIn(250);
        //if delay is set, clear delay
        if (mb[index].data('delay'))
            clearTimeout(mb[index].data('delay'));

        // set delay again
        mb[index].data('delay', setTimeout(function() {
            mb[index].fadeOut(1000);
        }, 3500));
    });
}

/**
 * Check if a url is valid
 * 
 * @param {string} str
 * @returns {Boolean}
 */
function isValidUrl(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
            '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
    if (!pattern.test(str)) {
        return false;
    } else {
        return true;
    }
}

/**
 * Check if an url corresponds to a valid youtube video
 * 
 * @param {string} videoUrl
 * @returns {boolean}
 */
function isYoutubeVideo(videoUrl) {
    var p = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
    var result = videoUrl.match(p);

    if (!result) {
        return false;
    }

    var videoId = RegExp.$1;
    var valid = false;
    
    $.ajax({
        url: "https://gdata.youtube.com/feeds/api/videos/" + videoId + "?v=2&alt=json",
        async: false,
        success: function (data) {

            valid = true;
        }
    });
    
    return valid;
}

/**
 * Check if an email address is valid
 * 
 * @param {type} email
 * @returns {Boolean}
 */
function isValidEmailAddress(email) {
    var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    return filter.test(email);
}