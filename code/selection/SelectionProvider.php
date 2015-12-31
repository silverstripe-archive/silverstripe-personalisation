<?php

interface SelectionProvider
{
    public function getVariation(ContextProvider $context, PersonalisationSource $source);
}
