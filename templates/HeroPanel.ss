<% require javascript(personalisation/javascript/heropanel.js) %>
<% require javascript(personalisation/javascript/jsImgSlider/themes/1/js-image-slider.js) %>

<div id="slider">
	<% loop HeroItems %>
		<a href="$HeroLink"><img src="$HeroObject.URL" alt="$Name" /></a>
	<% end_loop %>
</div>