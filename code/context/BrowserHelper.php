<?php



class BrowserHelper
{

	// List of tablet devices. https://github.com/serbanghita/Mobile-Detect/blob/master/Mobile_Detect.php
   protected static $tabletDevices = array(
       'iPad' => 'iPad|iPad.*Mobile', // @todo: check for mobile friendly emails topic.
       'NexusTablet' => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
       'SamsungTablet' => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1010|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P6810|GT-P7501',
       // @reference: http://www.labnol.org/software/kindle-user-agent-string/20378/
       'Kindle' => 'Kindle|Silk.*Accelerated',
       'AsusTablet' => 'Transformer|TF101',
       'BlackBerryTablet' => 'PlayBook|RIM Tablet',
       'HTCtablet' => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
       'MotorolaTablet' => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
       'NookTablet' => 'Android.*Nook|NookColor|nook browser|BNTV250A|LogicPD Zoom2',
       // @ref: http://www.acer.ro/ac/ro/RO/content/drivers
       // @ref: http://www.packardbell.co.uk/pb/en/GB/content/download (Packard Bell is part of Acer)
       'AcerTablet' => 'Android.*\b(A100|A101|A200|A500|A501|A510|A700|A701|W500|W500P|W501|W501P|G100|G100W)\b',
       // @ref: http://eu.computers.toshiba-europe.com/innovation/family/Tablets/1098744/banner_id/tablet_footerlink/
       // @ref: http://us.toshiba.com/tablets/tablet-finder
       // @ref: http://www.toshiba.co.jp/regza/tablet/
       'ToshibaTablet' => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)',
       // @ref: http://www.nttdocomo.co.jp/english/service/developer/smart_phone/technical_info/spec/index.html
       'LGTablet' => '\bL-06C|LG-V900|LG-V909',
       'YarvikTablet' => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
       'MedionTablet' => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
       'ArnovaTablet' => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
       // @reference: http://wiki.archosfans.com/index.php?title=Main_Page
       'ArchosTablet' => 'Android.*ARCHOS|101G9|80G9',
       // @reference: http://en.wikipedia.org/wiki/NOVO7
       'AinolTablet' => 'NOVO7|Novo7Aurora|Novo7Basic|NOVO7PALADIN',
       // @todo: inspect http://esupport.sony.com/US/p/select-system.pl?DIRECTOR=DRIVER
       // @ref: Readers http://www.atsuhiro-me.net/ebook/sony-reader/sony-reader-web-browser
       'SonyTablet' => 'Sony Tablet|Sony Tablet S|EBRD1101|EBRD1102|EBRD1201',
       // @ref: db + http://www.cube-tablet.com/buy-products.html
       'CubeTablet' => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)',
       // @ref: http://www.cobyusa.com/?p=pcat&pcat_id=3001
       'CobyTablet' => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
       // @ref: http://pdadb.net/index.php?m=pdalist&list=SMiT (NoName Chinese Tablets)
       // @ref: http://www.imp3.net/14/show.php?itemid=20454
       'SMiTTablet' => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
       // @ref: http://www.rock-chips.com/index.php?do=prod&pid=2
       'RockChipTablet' => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
       // @ref: http://www.telstra.com.au/home-phone/thub-2/
       'TelstraTablet' => 'T-Hub2',
       // @ref: http://www.fly-phone.com/devices/tablets/ ; http://www.fly-phone.com/service/
       'FlyTablet' => 'IQ310|Fly Vision',
       // @ref: http://www.bqreaders.com/gb/tablets-prices-sale.html
       'bqTablet' => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)',
       // @ref: http://www.huaweidevice.com/worldwide/productFamily.do?method=index&directoryId=5011&treeId=3290
       // @ref: http://www.huaweidevice.com/worldwide/downloadCenter.do?method=index&directoryId=3372&treeId=0&tb=1&type=software (including legacy tablets)
       'HuaweiTablet' => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
       // Nec or Medias Tab
       'NecTablet' => '\bN-06D|\bN-08D',
       // @ref: https://www.nabitablet.com/
       'NabiTablet' => 'Android.*\bNabi',
       // @note: Avoid detecting 'PLAYSTATION 3' as mobile.
       'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
       'GenericTablet' => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|MID7015|BNTV250A|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|hp-tablet',
   );

	public static function is_OSX($userAgent){
		return (preg_match('/(Mac)/i', $userAgent)) ? true : false;
	}

	/**
	* List of known mobiles, found in the HTTP_USER_AGENT variable
	* @see MobileBrowserDetector::is_mobile() for how they're used.
	*
	* @return array
	*/
	private static function mobile_index_list() {
		return explode(',', '1207,3gso,4thp,501i,502i,503i,504i,505i,506i,6310,6590,770s,802s,a wa,acer,acs-,airn,alav,asus,attw,au-m,aur ,aus ,abac,acoo,aiko,alco,alca,amoi,anex,anny,anyw,aptu,arch,argo,bell,bird,bw-n,bw-u,beck,benq,bilb,blac,c55/,cdm-,chtm,capi,comp,cond,craw,dall,dbte,dc-s,dica,ds-d,ds12,dait,devi,dmob,doco,dopo,el49,erk0,esl8,ez40,ez60,ez70,ezos,ezze,elai,emul,eric,ezwa,fake,fly-,fly_,g-mo,g1 u,g560,gf-5,grun,gene,go.w,good,grad,hcit,hd-m,hd-p,hd-t,hei-,hp i,hpip,hs-c,htc ,htc-,htca,htcg,htcp,htcs,htct,htc_,haie,hita,huaw,hutc,i-20,i-go,i-ma,i230,iac,iac-,iac/,ig01,im1k,inno,iris,jata,java,kddi,kgt,kgt/,kpt ,kwc-,klon,lexi,lg g,lg-a,lg-b,lg-c,lg-d,lg-f,lg-g,lg-k,lg-l,lg-m,lg-o,lg-p,lg-s,lg-t,lg-u,lg-w,lg/k,lg/l,lg/u,lg50,lg54,lge-,lge/,lynx,leno,m1-w,m3ga,m50/,maui,mc01,mc21,mcca,medi,meri,mio8,mioa,mo01,mo02,mode,modo,mot ,mot-,mt50,mtp1,mtv ,mate,maxo,merc,mits,mobi,motv,mozz,n100,n101,n102,n202,n203,n300,n302,n500,n502,n505,n700,n701,n710,nec-,nem-,newg,neon,netf,noki,nzph,o2 x,o2-x,opwv,owg1,opti,oran,p800,pand,pg-1,pg-2,pg-3,pg-6,pg-8,pg-c,pg13,phil,pn-2,pt-g,palm,pana,pire,pock,pose,psio,qa-a,qc-2,qc-3,qc-5,qc-7,qc07,qc12,qc21,qc32,qc60,qci-,qwap,qtek,r380,r600,raks,rim9,rove,s55/,sage,sams,sc01,sch-,scp-,sdk/,se47,sec-,sec0,sec1,semc,sgh-,shar,sie-,sk-0,sl45,slid,smb3,smt5,sp01,sph-,spv ,spv-,sy01,samm,sany,sava,scoo,send,siem,smar,smit,soft,sony,t-mo,t218,t250,t600,t610,t618,tcl-,tdg-,telm,tim-,ts70,tsm-,tsm3,tsm5,tx-9,tagt,talk,teli,topl,tosh,up.b,upg1,utst,v400,v750,veri,vk-v,vk40,vk50,vk52,vk53,vm40,vx98,virg,vite,voda,vulc,w3c ,w3c-,wapj,wapp,wapu,wapm,wig ,wapi,wapr,wapv,wapy,wapa,waps,wapt,winc,winw,wonu,x700,xda2,xdag,yas-,your,zte-,zeto,aste,audi,avan,blaz,brew,brvw,bumb,ccwa,cell,cldc,cmd-,dang,eml2,fetc,hipt,http,ibro,idea,ikom,ipaq,jbro,jemu,jigs,keji,kyoc,kyok,libw,m-cr,midp,mmef,moto,mwbp,mywa,newt,nok6,o2im,pant,pdxg,play,pluc,port,prox,rozo,sama,seri,smal,symb,treo,upsi,vx52,vx53,vx60,vx61,vx70,vx80,vx81,vx83,vx85,wap-,webc,whit,wmlb,xda-');
	}

	public static function is_android() {
		return (stripos($_SERVER['HTTP_USER_AGENT'], 'android') !== false) ? true : false;
	}

	public static function is_iphone() {
		return (preg_match('/(ipod|iphone)/i', $_SERVER['HTTP_USER_AGENT'])) ? true : false;
	}

	public static function is_opera_mini() {
		return (stripos($_SERVER['HTTP_USER_AGENT'], 'opera mini') !== false) ? true : false;
	}

	public static function is_blackberry() {
		return (stripos($_SERVER['HTTP_USER_AGENT'], 'blackberry') !== false) ? true : false;
	}

	public static function is_palm() {
		return (preg_match('/(palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i', $_SERVER['HTTP_USER_AGENT'])) ? true : false;
	}

	public static function is_windows() {
		return (preg_match('/(windows ce; ppc;|windows ce; smartphone;|windows ce; iemobile)/i', $_SERVER['HTTP_USER_AGENT'])) ? true : false;
	}

	public static function is_win_phone() {
		return (stripos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone OS') !== false) ? true : false;
	}

	/**
	* Is the current HTTP_USER_AGENT a known mobile device string?
	* @see http://mobiforge.com/developing/story/setting-http-headers-advise-transcoding-proxies
	*
	* @return bool
	*/
	public static function is_mobile() {
		$isMobile = false;
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

		switch(true) {
			case(self::is_iphone()):
				$isMobile = true;
				break;
			case(self::is_android()):
				$isMobile = true;
				break;
			case(self::is_opera_mini()):
				$isMobile = true;
				break;
			case(self::is_blackberry()):
				$isMobile = true;
				break;
			case(self::is_palm()):
				$isMobile = true;
				break;
			case(self::is_win_phone()):
				$isMobile = true;
				break;
			case(self::is_windows()):
				$isMobile = true;
				break;
			case(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|pda|psp|treo)/i', $agent)):
				$isMobile = true;
				break;
			case((strpos($accept, 'text/vnd.wap.wml') !== false) || (strpos($accept, 'application/vnd.wap.xhtml+xml') !== false)):
				$isMobile = true;
				break;
			case(isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])):
				$isMobile = true;
				break;
			case(in_array(strtolower(substr($agent, 0, 4)), self::mobile_index_list())):
				$isMobile = true;
				break;
		}

		if(!headers_sent()) {
			header('Cache-Control: no-transform');
			header('Vary: User-Agent, Accept');
		}

		return $isMobile;

	}

	public static function is_tablet($userAgent){
		$isTablet = false;
		foreach(self::$tabletDevice as $regex){
			$regex = str_replace('/', '\/', $regex);
			if((bool)preg_match('/'.$regex.'/is',  $userAgent)) $isTablet = true;
		}
		return $isTablet;
	}

	public static function is_desktop($userAgent){
		$isDeskTop = false;
		$desktops = array("Macintosh", "Windows", "Linux");
		foreach($desktops as $desktop){
			if(strpos($userAgent, $desktop)) $isDeskTop = true;
		}
		return $isDeskTop;
	}

	public static function is_firefox($userAgent){
		return stripos($userAgent, 'Firefox') ? true : false;
	}

	public static function is_msie($userAgent){
		return stripos($userAgent, 'MSIE') ? true : false;
	}

	public static function is_opera($userAgent){
		return preg_match("/\bOpera\b/i", $userAgent);;
	}

	public static function is_safari($userAgent){
		return strpos($userAgent, 'Safari') ? true : false;
	}

}
