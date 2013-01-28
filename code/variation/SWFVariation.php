<?php

class SWFVariation extends PersonalisationVariation{

	static $db = array(
		"EmbedSWF" => "HTMLText",
		"SWFWidth" => "Int",
		"SWFHeight" => "Int"
	);

	static $has_one = array(
		"SWFFile" => "File",
		"FallBackImage" => "Image"
 	);

	static function addExtraFields(){
		$fields = new FieldList();
//		$internalSWF = new UploadField("SWFFileID",  "Choose swf from assets" );
//		$internalSWF->getValidator()->setAllowedExtensions(array('swf'));
		$internalSWF = new ReadonlyField('SwfVariation', 'SWF Variation From Assets', 'Links to swfs in files and assets can be added after you have saved for the first time');

//		$internalSWF = new TreeDropdownField("SWFFileID", "Choose swf from assets", "File", "ID", "Title");
		$fields->push($internalSWF);
		$externalSWF = new HtmlEditorField("EmbedSWF", "Link to External SWF");
		$fields->push($externalSWF);
		$fallBackImage = new ReadonlyField('Variation', 'Fall Back Image', ' Fall Back Images can be added after you have saved for the first time');
		$fields->push($fallBackImage);
		return $fields;
	}

	function render(ContextProvider $context, Controller $controller = null) {
		$swfdetails = new ArrayList();
		if($this->SWFFileID && $swf = File::get_by_id("File", $this->SWFFileID)){
			$swfURL = $swf->getURL();
		}else{
			$swfURL = $this->EmbedSWF;
		}

		if($this->FallBackImageID && $fallBack = File::get_by_id("Image", $this->FallBackImageID)){
			$fallBackImage = $fallBack->getURL();
		}else{
			$fallBackImage = null;
		}

		$html = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width=" ' .  $this->SWFWidth . ' " height=" ' . $this->SWFHeight . '"> ' .
			'<param name="movie" value="' . $swfURL .  '" /> ' .
			' <param name="wmode" value="transparent"> ' .
			' <!--[if !IE]>--> ' .
			' <object type="application/x-shockwave-flash" data="' .$swfURL .  '" width=" ' . $this->SWFWidth . ' " height="' . $this->SWFHeight . '"> ' .
			' 	<param name="wmode" value="transparent"> ' .
			' 	<!--<![endif]--> ';
			if(!is_null($fallBackImage)){
				$html .= ' 	<img src="' . $fallBackImage . '" alt="$AlternativeText" /> ' ;
			}

		$html .= ' <!--[if !IE]>--> ' .
			' </object> ' .
			' <!--<![endif]--> ' .
			' </object>';

		return $html;
	}
}