var particulesSize = 5;
var easeInspiration = 'easeOutCubic';
var dureeInspiration = 6500;
var easeExpiration = 'easeOutCubic';
var dureeExpiration = 3500;
var dureeAttente = 3500;




$(window).on('load', function() {
    $('#animation-button').click(function(){
      $('footer').fadeOut(300, function() {
        $(this).remove();
      })
      setTimeout(function() {
        animateParticules();
      }, 300);
    })

});

$(window).on('resize', function() {
    animateParticules();
});


var animdist = 0;

var particuleIsSet = false;

var particules = null;

var particulesPosition = [];

var animcycle = function(o, l, t) {
    $('div.particulemeter > span').stop().css({ left: 0 });
    $('div.particulemeter > span').animate({ left: 400 }, dureeInspiration, easeInspiration);
    o.animate({ left: (l * animdist), top: (t * animdist) }, dureeInspiration, easeInspiration, function() {
        $('div.particulemeter > span').stop();
        $('div.particulemeter > span').animate({ left: 0 }, dureeExpiration, easeExpiration);
        o.animate({ left: (l), top: (t) }, dureeExpiration, easeExpiration, function() {
            $('div.particulemeter > span').stop().animate({ opacity: 0 }, (dureeAttente / 2), function() {
                $('div.particulemeter > span').animate({ opacity: 1 }, (dureeAttente / 2));
            });
            setTimeout(function() { animcycle(o, l, t) }, dureeAttente);
        });
    });
}


var animateParticules = function() {
    var wWidth = $(window).width();

    animdist = wWidth / 50;

    if (!particuleIsSet) {
        particules = $('div.particules > span');

        particules.css({ width: particulesSize, height: particulesSize });
    }

    $.each(particules, function(k, v) {
        if (!particuleIsSet) {
            var posLeft = $(this).data('posleft');
            var posTop = $(this).data('postop');

            particulesPosition[k] = { obj: $(this), posLeft: posLeft, posTop: posTop };
        }

        $(this).css({ left: particulesPosition[k].posLeft, top: particulesPosition[k].posTop });
    });

    particuleIsSet = true;

    $.each(particulesPosition, function(k, v) {
        var l = particulesPosition[k].posLeft;
        var t = particulesPosition[k].posTop;
        var o = particulesPosition[k].obj;
        o.stop();
        animcycle(o, l, t);
    });
}