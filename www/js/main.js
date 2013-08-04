$(function(){

	$('body[data-fbAppId]').each(function () {
		FB.init({
			appId: $(this).data('fb-appid'),
			status: true, // check login status
			cookie: true, // enable cookies to allow the server to access the session
			xfbml: true  // parse XFBML
		});
	});

});
