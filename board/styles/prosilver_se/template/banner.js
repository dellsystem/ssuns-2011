/* Javascript goes here */
$(document).ready(function() {
    var hasClicked = false;
    var currentBanner = 0; // 0 - committees, 1 = theme, 2 = what's new
    // Takes care of the banner changing crap
    function changeBanner(newBanner) {
        $('#banner a').attr('href', newBanner);
        $('#banner img').attr('src', 'board/images/' + newBanner + '.png');
        // Fuck the alt tag
        $('#banner img').attr('alt', 'SSUNS banner');
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
    // Stop rotating once the user clicks something
    function rotateBanners() {
        window.setTimeout(function() {
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
            console.log('new banner: ' +currentBanner);
            rotateBanners();
        }, 5000);
    }
    if (!hasClicked) {
        rotateBanners();
    }
});
