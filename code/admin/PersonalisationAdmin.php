<?php

class PersonalisationAdmin extends ModelAdmin {

	static $url_segment = 'personalisation';

	static $menu_title = 'Personalisation';

	public static $managed_models = array('PersonalisationScheme', 'VaryingPersonalisationScheme');
}
