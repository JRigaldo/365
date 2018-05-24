$(window).on('load', function() {
    $('.cross').each(function() {
        $(this).click(function() {
            $(this).parent().addClass('remove-bubble')
        })
    });

    $('#settings').click(function(){
      console.log('settings')
        $(this).toggleClass('open')
        $('.menu').toggleClass('open')
        if($('.timeline').hasClass('open')){
            $('#calendar').removeClass('open')
            $('.timeline').removeClass('open')
        }
    })
    $('#calendar').click(function(){
        $(this).toggleClass('open')
        $('.timeline').toggleClass('open')
        if($('#settings').hasClass('open')){
            $('#settings').removeClass('open')
            $('.menu').removeClass('open')
        }
    })


    $('#animation-button').click(function() {
        $('footer').fadeOut(300, function() {
            $(this).remove();
        });
        $('.main-title').fadeIn(300, function() {
            $(this).addClass('start')
        })
        $('#animation').fadeIn(300, function() {
            $(this).addClass('start')
        })
        $('#back').fadeIn(300, function() {
            $(this).addClass('start')
        })
        $('#particulemeter').fadeIn(300, function() {
            $(this).addClass('start')
        })
        setTimeout(function() {
            window.location = 'file:///Users/jeremy/Documents/projects/365/cc365/APPLICATION/www/anim.html#';
        }, 300);
    });
});
