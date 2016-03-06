<?php
/**
 * Thanks to http://stackoverflow.com/questions/8990007/display-post-excerpts-limited-by-word-count
 */
// just the excerpt
function first_n_words($text, $number_of_words) {
   // Where excerpts are concerned, HTML tends to behave
   // like the proverbial ogre in the china shop, so best to strip that
   $text = strip_tags($text);

   // \w[\w'-]* allows for any word character (a-zA-Z0-9_) and also contractions
   // and hyphenated words like 'range-finder' or "it's"
   // the /s flags means that . matches \n, so this can match multiple lines
   $text = preg_replace("/^\W*((\w[\W'-]*\b\W*){1,$number_of_words}).*/ms", '\\1', $text);

   // strip out newline characters from our excerpt
   return str_replace("\n", "", $text);
}

// excerpt plus link if shortened
function truncate_to_n_words($text, $number_of_words, $url, $readmore = 'Read More') {
   $text = strip_tags($text);
   $excerpt = first_n_words($text, $number_of_words);
   // we can't just look at the length or try == because we strip carriage returns
   if( str_word_count($text) !== str_word_count($excerpt) ) {
      $excerpt .= '... <br><a href="'.$url.'">'.$readmore.'</a>';
   }
   return $excerpt;
}

/**
 * Determine the baseurl of the current script.
 * Used for determining the absolute url of return and
 * cancel urls.
 * @return string
 */
function getBaseUrl() {

   $protocol = 'http';
   if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
      $protocol .= 's';
   }

   $host = $_SERVER['HTTP_HOST'];
   $request = $_SERVER['PHP_SELF'];
   return dirname($protocol . '://' . $host . $request);
}


/**
 * Utility method that returns the first url of a certain
 * type. Returns empty string if no match is found
 * 
 * @param array $links
 * @param string $type 
 * @return string
 */
function getLink(array $links, $type) {
   foreach($links as $link) {
      if($link->getRel() == $type) {
         return $link->getHref();
      }
   }
   return "";
}

/**
 * Utility function to pretty print API error data
 * @param string $errorJson
 * @return string
 */
function parseApiError($errorJson) {
   $msg = '';
   
   $data = json_decode($errorJson, true);
   if(isset($data['name']) && isset($data['message'])) {
      $msg .= $data['name'] . " : " .  $data['message'] . "<br/>";
   }
   if(isset($data['details'])) {
      $msg .= "<ul>";
      foreach($data['details'] as $detail) {
         $msg .= "<li>" . $detail['field'] . " : " . $detail['issue'] . "</li>"; 
      }
      $msg .= "</ul>";
   }
   if($msg == '') {
      $msg = $errorJson;
   }  
   return $msg;
}
