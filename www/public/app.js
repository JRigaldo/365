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

/*

var configResults = dataRoute();
 console.log(configResults);
 configResults.done(function(data){
   getPartials(data)
   console.log('data', data);
 });

 function dataRoute(){
   var route = {};
   return $.getJSON('public/config.json', function(routes){
     route = routes.title
   });
   console.log(route);
 }

function loadContent(hash){
  var results = $.get('views/pages/' + hash + '.ejs', function(view) {
    html = ejs.render(view, {});
    $('section').html(html);
  });
  results.done(function(){
    console.log('test results function loadContent');
  });
}

var url = window.location.href;
var hash = url.substring(url.indexOf('#')+1);

if(hash === ''){
  hash = 'home'
}else if(hash === url){
  hash = 'home';
}

var splashView = $.get('views/pages/' + hash + '.ejs', function(view) {
  html = ejs.render(view, {});
  $('section').html(html);
});
splashView.done(function(){
  console.log('test results first load');
});

function getPartials(){
  var header = $.get('views/partials/header.ejs', function(view) {
    html = ejs.render(view, {});
    $('header').html(html);
  }).done(function(){
    console.log('header loaded');
  });
  var footer = $.get('views/partials/footer.ejs', function(view) {
    html = ejs.render(view, {});
    $('footer').html(html);
  }).done(function(){
    console.log('footer loaded');
  });
}

function onchange(){
  if(hash === ''){
    hash = 'home'
  }
  loadContent(location.hash.slice(1));
  getPartials();
}

$(window).on('hashchange', onchange());

*/
