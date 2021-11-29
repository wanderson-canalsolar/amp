jQuery(document).ready(function($) {

    var owlPot = jQuery('#owl-carousel-pot');
    owlPot.owlCarousel({
        items:1,
        loop:true,
        margin:10,
        autoplay:true,
        autoplayTimeout:1000,
        autoplaySpeed: 1000,
        autoplayHoverPause:false,
        dots: false,
        nav: false
    });

    var displaygc = false;
    var displaygd = false;

    setInterval(function() {

        displaygd = !displaygd;
        displaygc = !displaygc;

        if(displaygd === true) {

            $('.pot-gd').css('display', 'flex').show('fade');
            $('.pot-gc').hide();

        }
        else {
            $('.pot-gc').css('display', 'flex').show('fade');
            $('.pot-gd').hide();
        }

    }, 2000);


     new RDStationForms('newsletter-96c8970d771d59520dff', 'UA-145443047-1').createForm();

     $("#rd-email_field-ki988xdg").attr('placeholder', 'Insira seu e-mail');
});
