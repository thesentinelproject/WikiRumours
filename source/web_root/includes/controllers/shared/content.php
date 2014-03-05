<?php

	function retrieveContentBlock($slug) {
		$block = retrieveFromDb('cms', array('slug'=>$slug, 'page_or_block'=>'b'), null, null, null);
		return $block[0]['content'];
	}