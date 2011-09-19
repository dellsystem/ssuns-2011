/* Javascript goes here */
$(document).ready(function() {
    var hasClicked = false;
    var currentBanner = 0; // 0 - committees, 1 = theme, 2 = what's new

    // Takes care of the banner changing crap
    function changeBanner(newBanner) {
        // First get the href attribute
        var newHref;
        if (newBanner == 'banner-committees') {
            newHref = 'committee-list';
        } else if (newBanner == 'banner-theme') {
            newHref = 'theme';
        } else {
            // What's new page
            newHref = 'whats-new';
        }
        $('#banner a').attr('href', newHref);
        $('#banner img').attr('src', 'board/images/' + newBanner + '.png');
        // Fuck the alt tag
        $('#banner img').attr('alt', 'SSUNS banner');

        // Stupid way to make sure the others aren't hovered over
        $('#banners-right').children('div').attr('class', '');

        // Make this banner menu item thing appear hovered over
        $('#' + newBanner).attr('class', 'hovered');
    }

    // Handles the clicking shit
    $('#banners-right div').click(function(event) {
        var thisID = $(this).attr('id');
        // Change the banner using that ID as a param
        changeBanner(thisID);
        hasClicked = true;
    });

    // Handle the random rotation here
    // Terrible duplication but idc
    function rotateBanners() {
        window.setTimeout(function() {
            // Stop rotating once the user clicks something
            if (!hasClicked) {
                var newBanner;
                if (currentBanner === 0) {
                    newBanner = 'banner-theme';
                } else if (currentBanner === 1) {
                    newBanner = 'banner-whats-new';
                } else {
                    newBanner = 'banner-committees';
                }
            
                changeBanner(newBanner);
                currentBanner = (currentBanner + 1) % 3;
                rotateBanners();
            }
        }, 4000);
    }

    rotateBanners();
});
