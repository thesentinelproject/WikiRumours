<?php

	class simplePieWrapper_TL {

		public function importFeedWithSimplePie($feed, $checkForEnclosures = false, $enclosuresMustBeImages = false, $maximumNumberOfResults = null, $minimumLengthOfPosts = null) {
	
			if (!$feed) {
				errorManager_TL::addError("No feed specified.");
				return false;
			}
			
			// load helper classes
				$parser = new parser_TL();
				$fileManager = new fileManager_TL();
			
			// create array
				$feedArray = array();
				$feedArray['DateTimeConnected'] = date('Y-m-d H:i:s');
			
			// invoke feed parser (SimplePie)
				$feedContents = new SimplePie();
				$feedContents->set_feed_url($feed);
				$feedContents->enable_cache(false);
				$feedContents->strip_htmltags(array_merge($feedContents->strip_htmltags, array('a')));
				$feedContents->init();
				$feedContents->handle_content_type();
				$feedContents->set_cache_location ('/cache');
				if ($feedContents->error()) {
					$feedArray['Status'] = $feedContents->error;
					return $feedArray;
				}
			
			// populate array
				
				$feedArray['Title'] = $feedContents->get_title();
				$feedArray['Link'] = $feedContents->get_permalink();
				$feedArray['Description'] = $feedContents->get_description();
				$feedArray['Items'] = array();
				
				foreach ($feedContents->get_items() as $item) {
					
					if (!$maximumNumberOfResults || count($feedArray['Items']) < $maximumNumberOfResults) {
					
						if (strlen($parser->removeHTML($item->get_content(), '')) > $minimumLengthOfPosts) {
						
							// look for enclosures
									if ($checkForEnclosures == 'Y') {
								
										$foundEnclosure = $item->get_enclosure(0);
										if ($foundEnclosure) {
											$foundEnclosureLink = $foundEnclosure->get_link();
											if ($foundEnclosureLink && (!$fileManager->isImage($foundEnclosureLink))) $foundEnclosureLink = '';
										}
										else {
											$images = $parser->extractImageURLs($item->get_content() . $item->get_description(), true);
											if ($fileManager->isImage($images[0])) $foundEnclosureLink = $images[0];
										}
										
									}
			
								// save data
										$nextConsecutiveNumber = count($feedArray['Items']);
										$feedArray['Items'][$nextConsecutiveNumber] = array();
										$feedArray['Items'][$nextConsecutiveNumber]['Title'] = $item->get_title();
										$foundAuthor = $item->get_author();
										if ($foundAuthor) {
											$feedArray['Items'][$nextConsecutiveNumber]['Author'] = $foundAuthor->get_name();
											$feedArray['Items'][$nextConsecutiveNumber]['AuthorEmail'] = $foundAuthor->get_email();
										}
										$feedArray['Items'][$nextConsecutiveNumber]['Guid'] = $item->get_id();
										$feedArray['Items'][$nextConsecutiveNumber]['Link'] = $item->get_permalink();
										$feedArray['Items'][$nextConsecutiveNumber]['Summary'] = $parser->removeHTML($item->get_description(), '');
										$feedArray['Items'][$nextConsecutiveNumber]['Content'] = $parser->removeHTML($item->get_content(), '');
										if (strlen($feedArray['Items'][$nextConsecutiveNumber]['Content']) < strlen($feedArray['Items'][$nextConsecutiveNumber]['Summary'])) $feedArray['Items'][$nextConsecutiveNumber]['Content'] = parsers_TL::removeHTML($item->get_description());
										$feedArray['Items'][$nextConsecutiveNumber]['Enclosure'] = $foundEnclosureLink;
										$feedArray['Items'][$nextConsecutiveNumber]['PubDate'] = $item->get_date('Y-m-d H:i:s');
								
						}
							
					}
					
				}
	
			return $feedArray;
			
		}
		
	}
	
/*
	SimplePie Wrapper

	::	DESCRIPTION
	
		Retrieves the contents of an RSS feed using the SimplePie parser

	::	DEPENDENT ON
	
		parsers_TL
		fileManager_TL

	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/
	
?>
