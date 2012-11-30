<?php
/*
Collapsing Archives version: 2.0

Copyright 2007-2010 Robert Felty

This file is part of Collapsing Archives

  Collapsing Archives is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Collapsing Archives is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Collapsing Archives; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
?>
<?php
global $collapsArchItems;
$collapsArchItems = array();
function list_archives($options) {
  global $wpdb, $month, $collapsArchItems;
  extract($options);

  include('symbols.php');
	$inExclusionsCat = array();
	if ( !empty($inExcludeCat) && !empty($inExcludeCats) ) {
		$exterms = preg_split('/[,]+/',$inExcludeCats);
    if ($inExcludeCat=='include') {
      $in='IN';
    } else {
      $in='NOT IN';
    }
		if ( count($exterms) ) {
			foreach ( $exterms as $exterm ) {
				if (empty($inExclusionsCat))
					$inExclusionsCat = "'" . sanitize_title($exterm) . "'";
				else
					$inExclusionsCat .= ", '" . sanitize_title($exterm) . "' ";
			}
		}
	}
	if ( empty($inExclusionsCat) ) {
		$inExcludeCatQuery = "";
  } else {
    $inExcludeCatQuery ="AND $wpdb->terms.slug $in ($inExclusionsCat)";
  }
	$inExclusionsYear = array();
	if ( !empty($inExcludeYear) && !empty($inExcludeYears) ) {
		$exterms = preg_split('/[,]+/',$inExcludeYears);
    if ($inExcludeYear=='include') {
      $in='IN';
    } else {
      $in='NOT IN';
    }
		if ( count($exterms) ) {
			foreach ( $exterms as $exterm ) {
				if (empty($inExclusionsYear))
					$inExclusionsYear = "'" .$exterm . "'";
				else
					$inExclusionsYear .= ", '" . $exterm . "' ";
			}
		}
	}
	if ( empty($inExclusionsYear) ) {
		$inExcludeYearQuery = "";
  } else {
    $inExcludeYearQuery ="AND YEAR($wpdb->posts.post_date) $in ($inExclusionsYear)";
  }

  if (is_array($post_type)) {
    $postTypeQuery="AND $wpdb->posts.post_type IN (";
    foreach ($post_type as $type) {
      $postTypeQuery .= "'$type',";
    }
    $postTypeQuery .= ')';
  } else if ($post_type=='all') {
    $postTypeQuery="AND $wpdb->posts.post_type NOT IN ('page', 'revision', 'nav_menu_item', 'attachment')";
  } else {
    $postTypeQuery="AND $wpdb->posts.post_type='$post_type'";
  }
	if ($defaultExpand!='') {
		$autoExpand = preg_split('/,\s*/',$defaultExpand);
  } else {
	  $autoExpand = array();
  }


  $postquery= "SELECT $wpdb->terms.slug, $wpdb->posts.ID,
    $wpdb->posts.post_name, $wpdb->posts.post_title, $wpdb->posts.post_author,
    $wpdb->posts.post_date, YEAR($wpdb->posts.post_date) AS 'year',
    MONTH($wpdb->posts.post_date) AS 'month' ,
    $wpdb->posts.post_type
    FROM $wpdb->posts LEFT JOIN $wpdb->term_relationships ON $wpdb->posts.ID =
    $wpdb->term_relationships.object_id 
		LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id =
																			$wpdb->term_relationships.term_taxonomy_id
		LEFT JOIN $wpdb->terms ON $wpdb->terms.term_id = 
		                          $wpdb->term_taxonomy.term_id 
  WHERE post_status='publish' $postTypeQuery $inExcludeYearQuery $inExcludeCatQuery 
  GROUP BY $wpdb->posts.ID 
  ORDER BY $wpdb->posts.post_date $sort";

  $allPosts=$wpdb->get_results($postquery);

  if ($debug==1) {
    echo  "<pre style='display:none' >";
    printf ("MySQL server version: %s\n", mysql_get_server_info());
    echo  "\ncollapsArch options:\n";
    print_r($options);
    echo  "POST QUERY:\n $postquery\n";
    echo  "\nPOST QUERY RESULTS\n";
    print_r($allPosts);
    echo  "</pre>";
  }

  if (!$allPosts)
    return;

  $currentYear = -1;
  $currentMonth = -1;
  $lastMonth=-1;
  $lastYear=-1;
  for ($i=0; $i<count($allPosts); $i++) {
    if ($allPosts[$i]->year != $lastyear) {
      $lastYear=$allPosts[$i]->year;
    }
    if ($allPosts[$i]->month != $lastMonth) {
      $lastMonth=$allPosts[$i]->month;
    }
    $yearCounts{"$lastYear"}++;
    $monthCounts{"$lastYear$lastMonth"}++;
  }

  $newYear = false;
  $newMonth = false;
  $closePreviousYear = false;
  $monthCount=0;
  $i=0;
  foreach( $allPosts as $archPost ) {
    $expanded=false;
    $monthStyle = "style='display:none'";
    $postStyle = "style='display:none'";

    if ($currentYear != $archPost->year ) {
      $lastYear=$currentYear;
      $lastMonth=$currentMonth;
      $currentYear = $archPost->year;
      $theID = "collapsArch-$currentYear:$number";
      /* this should fix the "sparse year" problem
       * Thanks to Aishda
       */
      $currentMonth = 0;
      $newYear = true;
      if ($showYearCount) {
        $yearCount = ' <span class="yearCount">(' .
            $yearCounts{"$currentYear"} . ")</span>\n";
      }
      else {
        $yearCount = '';
      }
      $ding = $expandSym;
      $yearRel = 'expand';
      $monthRel = 'expand';
      $yearTitle= __('click to expand', 'collapsArch');
      $monthTitle= __('click to expand', 'collapsArch');
      /* rel = expand means that it will be hidden, and clicking on the
       * triangle will expand it. rel = collapse means that is will be shown, and
       * clicking on the triangle will collapse it 
       */
      if (( $expandCurrentYear AND $archPost->year == date('Y')) 
          OR ($useCookies AND $_COOKIE[$theID]==1)) {
        $ding = $collapseSym;
        $yearRel = 'collapse';
        $yearTitle= __('click to collapse', 'collapsArch');
        $monthStyle = '';
      }
      
      if($i>=1 && $allPosts[$i-1]->year != $archPost->year ) {
        if ($currentMonth==0) {
          $lastID = "collapsArch-$lastYear-$lastMonth:$number";
        } else {
          $lastID = "collapsArch-$currentYear-$currentMonth:$number";
        }
        if( $expandYears ) {
          if( $expandMonths ) {
            $expandingCurrentMonth = $expandCurrentMonth && $currentYear == date('Y')
            && $lastMonth== date('n');
            $cookieSet = $useCookies AND isset($_COOKIE[$lastID]) 
                AND $_COOKIE[$lastID]==1;
            if ($expanded)
              $archives .= "\n        </ul>\n";
            $archives .= "        </div>\n      </li> <!-- close expanded month --> \n";
          } else {
            $archives .= "      </li> <!-- close month --> \n";
          }
          $archives .= "  </ul>\n     </div>\n  </li> <!-- end year -->\n";
        } else {
          if( $expandMonths ) {
            $archives .= "   </ul>\n     </div>\n  </li> <!-- end year -->\n";
          } else {
            $archives .= "  </li> <!-- end year -->\n";
          }
        }
      }
      $home = get_settings('home');
      if( $expandYears  || $expandMonths) {
        $archives .= "  <li class='collapsing archives $yearRel'><span title='$yearTitle' " .
            "class='collapsing archives $yearRel'>" .
            "<span class='sym'>$ding</span>";
      } else {
        $archives .= "  <li class='collapsing archives item'>\n";
      }
      if (in_array($post_type, array('all', 'post'))) {
        $yearLink = "<a href='".get_year_link($archPost->year). "'>$currentYear $yearCount</a>\n";
      } else {
        $yearLink = "<a href='". add_query_arg('post_type', $post_type, get_year_link($archPost->year)). "'>$currentYear $yearCount</a>\n";
      }
      if ($linkToArch) {
        $archives .=  "</span>";
        $archives .=  $yearLink;
      } else {
        $archives .=  $yearLink;
        $archives .= "</span>";
      }
    if( $expandYears || $expandMonths ) {
      $archives .= "    <div $monthStyle id='$theID'>\n";
      //if ($expandCurrentYear AND $currentYear==date('Y'))
        $archives .= "    <ul>\n";
    }
    $newYear = false;
  }

  if ($currentMonth != $archPost->month) {
    $i++;
    if ($currentMonth==0) {
      $lastID = "collapsArch-$lastYear-$lastMonth:$number";
    } else {
      $lastID = "collapsArch-$currentYear-$currentMonth:$number";
    }
    $lastMonth = $currentMonth;
    $currentMonth = $archPost->month;
    $newMonth = true;
    if ($expandYears) 
      $theID = "collapsArch-$currentYear-$currentMonth:$number";
    if ($i>1) {
      if ($monthText!='')
        $monthText = "<ul>$monthText</ul>";
      $collapsArchItems[$lastID] = "$monthText";
      $monthText='';
    }
    if ($expandCurrentMonth AND $currentYear == date('Y') 
        AND $lastMonth== date('n')
        OR ($useCookies AND isset($_COOKIE[$lastID]) AND $_COOKIE[$lastID]==1))
      $expanded=true;
    else
      $expanded=false;
    if($newYear == false) { #close off last month
      $newYear=true; 
    } else {
      if ($expandYears) {
        if ($expandMonths) {
          if ($expanded)
            $archives .= "\n        </ul>\n";
          $archives .= "       </div>\n      </li> <!-- close expanded month --> \n";
        } else {
          $archives .= "      </li> <!-- close month --> \n";
        }
      }
    }

    if ($showMonthCount) {
      $monthCount = ' <span class="monthCount">(' .
          $monthCounts{"$currentYear$currentMonth"} . ")</span>\n";
    } else {
      $monthCount = '';
    }
    if( $expandYears ) {
      $text = sprintf('%s', $month[zeroise($currentMonth,2)]);

      $text = wptexturize($text);
      $title_text = wp_specialchars($text,1);

      if (in_array($post_type, array('all', 'post'))) {
        $monthLink = get_month_link( $currentYear, $currentMonth );
      } else {
        $monthLink = add_query_arg('post_type', $post_type,get_month_link( $currentYear, $currentMonth ));
      }
      if ($expandMonths ) {
        $link = 'javascript:;';
        $monthCollapse = 'collapsing archives';
        if(( $expandCurrentMonth && $currentYear == date('Y')
            AND $currentMonth == date('n')) 
            OR ($useCookies AND $_COOKIE[$theID]==1)) {
          $monthRel = 'collapse';
          $monthTitle= __('click to collapse', 'collapsArch');
          $postStyle = '';
          $ding = $collapseSym;
        } else {
          $monthRel = 'expand';
          $monthTitle= __('click to expand', 'collapsArch');
          $ding = $expandSym;
        }
        $the_span = "<span title='$monthTitle' " .
            "class='$monthCollapse $monthRel'>" ;
        $the_ding="<span class='sym'>$ding</span>";
        if ($linkToArch) {
          $the_link= "$the_span$the_ding</span>";
          $the_link .="<a href='$monthLink' title='$title_text'>";
          $the_link .="$text $monthCount</a>\n";
        } else {
          $the_link ="$the_span$the_ding<a href='$monthLink'>$text $monthCount</a>";
          $the_link.="</span>";
        }
      } else {
        $monthRel = '';
        $monthTitle = '';
        $monthCollapse = 'collapsing archives';
        $the_link ="<a href='$monthLink' title='$title_text'>";
        $the_link .="$text $monthCount</a>\n";
      }

      $archives .= "      <li class='collapsing archives $monthRel'>".$the_link;

    }
    if ($expandYears && $expandMonths ) {
      $archives .= "        <div $postStyle " . 
          "id='$theID'>\n";
      if (($expandCurrentMonth AND $currentYear == date('Y') 
          AND $currentMonth== date('n'))
          OR ($useCookies AND isset($_COOKIE[$theID]) AND $_COOKIE[$theID]==1))
        $archives .= "        <ul>\n";
      if ($expanded)
      $text = '';
    }
  } else {
    if( $expandYears && $expandMonths ) {
      $text = '';
    }
  }
  $text = '';
  if( $showPostNumber ) {
    $text .= '#'.$archPost->ID;
  }

    if ($showPostTitle  && $expandMonths) {

      //$title_text = htmlspecialchars(strip_tags(__($archPost->post_title)), ENT_QUOTES);
      $title_text = apply_filters('post_title', $archPost->post_title);
      if(strlen(trim($title_text))==0) {
        $title_text = $noTitle;
      }
      $tmp_text = '';
      if ($postTitleLength>0 && strlen($title_text)>$postTitleLength ) {
        $tmp_text = substr($title_text, 0, $postTitleLength );
        $tmp_text .= ' &hellip;';
      }

      $text .= ( $tmp_text == '') ? $title_text : $tmp_text;
      if ($showPostDate ) {
        $theDate = mysql2date($postDateFormat, $archPost->post_date );
        if ($postDateAppend=='after') { 
          $text .= ( $text == '' ? $theDate : " $theDate" );
        } else {
          $text = ( $text == '' ? $theDate : "$theDate " ) . $text;
        }
      }

      if ($showCommentCount ) {
        $commcount = ' ('.get_comments_number($archPost->ID).')';
      }

      $link = get_permalink($archPost);
      $monthText .= "          <li class='collapsing archives item'><a href='$link' " .
          "title='$title_text'>$text</a>$commcount</li>\n";
      if (($expandCurrentMonth  AND $expandYears
            AND $currentYear == date('Y') AND $currentMonth == date('n')) 
            OR ($useCookies AND $_COOKIE[$theID]==1)) {
        $archives .= "$monthText";
        $monthText='';
      } elseif (($expandCurrentYear  && $expandMonths
           && $currentYear == date('Y')) 
           OR ($useCookies AND $_COOKIE[$theID]==1)) {
      }
    }
  }
  if ($expandMonths) {
    if ((!$currentYear == date('Y') && !$currentMonth == date('n')) 
        OR ($useCookies AND !$_COOKIE[$theID]==1)) {
      $archives .= '';
    }
    $archives .= "        </div>
    </li> <!-- close month -->\n";
    $archives .= "  </ul></div>";
    if ($monthText!='')
      $monthText = "<ul>$monthText</ul>";
    $collapsArchItems[$theID] = $monthText;
    $monthText='';
  }
  if (!$expandYears && $expandMonths) {
  }
 
  if ($expandYears && !$expandMonths) {
    $archives .= "  </li> <!-- close month --></div><!-- close year -->\n";
    $collapsArchItems[$theID] = $theID;
  }
  if ($expandYears) {
    $archives .= "</li> <!-- end of collapsing archives -->";
  }
  return($archives);
}
?>
