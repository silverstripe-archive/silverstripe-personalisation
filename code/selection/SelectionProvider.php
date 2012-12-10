<?php

interface SelectionProvider {
	function getVariation(ContextProvider $context, PersonalisationSource $source);
}
