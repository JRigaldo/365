/* 
 * Performs effects and offers navigation tools in the interface
 */
var interface = function()
{
    $('a[href*="#"]').on('click', function()
    {
        $(this).preventDefault;
     
        $('*.emphasis').removeClass( 'emphasis' );  
        
        var selector = $(this).attr('href');
        
        var selectorHeight = $(selector).offset().top;    
        
        $('html, body').animate({ scrollTop:( selectorHeight - 20 ) }, 1000, function()
        {
            
            $(selector).addClass( 'emphasis' );    
            
        });
    });    
};


$(document).on('ready', function()
{ 
    interface();      
});

