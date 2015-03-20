<?php
	require_once 'Config_path.php';
	require_once 'CDatabase.class.php';
	
	$objDatabase = new CDatabase();
	$objDatabase->connectDatabase();
	$objDatabase->setDatabase(DATABASE_NAME);
	
	/**************************************************************
	********** Function to auto include used class files **********
	**************************************************************/
	
	function redirect( $strRedirectPath ) {
		header( 'location:' . SITE_PATH . $strRedirectPath );
		exit;
	}
	
	
	
	/*********************************************************************
	 ********** Global function to reload page using javascript **********
	*********************************************************************/
	
	function reload() {
		?>
			<script type="text/javascript">
				window.parent.location.href = window.parent.location;
			</script>
		<?php
		exit;
	}
	
	function truncate( $strText,$intNumber ) {
		$strText = html_entity_decode( $strText, ENT_QUOTES );
	
		if( strlen( $strText ) > $intNumber ) {
			$strText = substr( $strText, 0, $intNumber );
			$strText = substr( $strText,0,strrpos( $strText,' ' ) );
	
		  if(( substr( $strText, -1 ) ) == '.') {
		    $strText = substr( $strText, 0, ( strrpos( $strText, '.' ) ));
		  }
	
			$strText = $strText . '...';
		}
	
		return htmlentities( $strText, ENT_QUOTES );
	}
	
	function display( $strMixVar ) {
		echo '<pre>';
		print_r( $strMixVar );
		echo '</pre>';
	}
	
	function merge_intersect_array( $arr1, $arr2 ) {
	
		if( false == is_array ( $arr1 ) || false == is_array ( $arr2 ) ) return array();
	
		$arrResult = array_merge( $arr1, $arr2 );
	
	  if( true == is_array( $arrResult ) ) {
	    foreach( $arrResult as $strKey => $strValue ) {
	      if( false == array_key_exists( $strKey, $arr1 ) ) {
	        unset( $arrResult[$strKey] );
	      }
	    }
	  }
	
	  return $arrResult;
	}
	
	function rekeyObjects( $strKeyFieldName, $arrobjUnkeyedData ) {
		if( false == valArr( $arrobjUnkeyedData ) ) return $arrobjUnkeyedData;
	
		$arrobjRekeyedData = array();
	
		if( "index" != $strKeyFieldName ) {
			$strGetFunction = "get" . $strKeyFieldName;
	
			foreach( $arrobjUnkeyedData as $objUnkeyedData ) {
				$arrobjRekeyedData[$objUnkeyedData->$strGetFunction()] = $objUnkeyedData;
			}
	
		} else {
	
			foreach( $arrobjUnkeyedData as $objUnkeyedData ) {
				$arrobjRekeyedData[] = $objUnkeyedData;
			}
		}
	
		return $arrobjRekeyedData;
	}
	
	function rekeyArray( $strKeyFieldName, $arrarrUnkeyedData ) {
		if( false == valArr( $arrarrUnkeyedData ) ) return $arrarrUnkeyedData;
	
		$arrarrRekeyedData = array();
	
		if( "index" != $strKeyFieldName ) {
			foreach( $arrarrUnkeyedData as $arrmixUnkeyedData ) {
				$arrarrRekeyedData[$arrmixUnkeyedData[$strKeyFieldName]] = $arrmixUnkeyedData;
			}
	
		} else {
	
			foreach( $arrarrUnkeyedData as $arrmixUnkeyedData ) {
				$arrarrRekeyedData[] = $arrmixUnkeyedData;
			}
		}
	
		return $arrarrRekeyedData;
	}
	
	function updateObjects( $obj1, $obj2, $intCompanyUserId, $objDatabase ) {
	
		$boolIsValid = true;
		$boolIsValid &= $obj1->validate( VALIDATE_UPDATE, $objDatabase );
		$boolIsValid &= $obj2->validate( VALIDATE_UPDATE, $objDatabase );
	
		if( false == $boolIsValid ) {
			return $boolIsValid;
		}
	
		$objDatabase->begin();
	
		if( false == $obj1->update( $intCompanyUserId, $objDatabase ) ) {
			$objDatabase->rollback();
			$boolIsValid = false;
			return $boolIsValid;
		}
	
		if( false == $obj2->update( $intCompanyUserId, $objDatabase ) ) {
			$objDatabase->rollback();
			$boolIsValid = false;
			return $boolIsValid;
		}
	
		$objDatabase->commit();
	
		return $boolIsValid;
	}
	
	function updateReorderedObjects( $arrobjOldRecords, $arrobjNewRecords, $intCompanyUserId, $objDatabase ) {
	
		$boolIsValid = true;
	
		$objDatabase->begin();
	
		foreach( $arrobjOldRecords AS $intIndex => $objOldRecord ) {
	
			if( false != isset( $arrobjNewRecords[$intIndex] ) && $objOldRecord->getId() != $arrobjNewRecords[$intIndex]->getId() ) {
	
				$objNewRecord = $arrobjNewRecords[$intIndex];
	
				$boolIsValid = true;
				$boolIsValid &= $objNewRecord->validate( VALIDATE_UPDATE, $objDatabase );
				if( false == $boolIsValid ) {
					return $boolIsValid;
				}
	
				if( false == $objNewRecord->update( $intCompanyUserId, $objDatabase ) ) {
					$objDatabase->rollback();
					return false;
				}
			}
		}
	
		$objDatabase->commit();
	
		return $boolIsValid;
	}
	
	function getArrayElementByKey( $strKey, $arrElements ) {
		$strKey = trim( $strKey );
		return ( true == isset( $strKey[0] ) && true == valArr( $arrElements ) && true == array_key_exists( $strKey, $arrElements ) ) ? $arrElements[$strKey] : NULL;
	}
	
	function checkSessionTimeout( $strPortalName, $intInactiveTime, $strRedirectUrl ) {
		if( 0 === $intInactiveTime ) $intInactiveTime = determineDefaultSessionTimeOut( $strPortalName );
	
		$strPortalSessionLoadTime	= strtolower( str_replace( " ", "_", $strPortalName ) ) . '_session_load_time';
		$intInactiveTime 			= $intInactiveTime * 60;
	
		if( false == isset( $_SESSION[$strPortalSessionLoadTime] ) ) {
		  $intStartTime = time();
		  $_SESSION[$strPortalSessionLoadTime] = $intStartTime;
		}
	
		$intSessionLife = time() - $_SESSION[$strPortalSessionLoadTime];
	
		if( $intSessionLife > $intInactiveTime ) {
			session_destroy();
	
			if( 'resident_works' == strtolower( str_replace( " ", "_", $strPortalName ) )) {
				echo "<script language='javascript'>window.location = '" . $strRedirectUrl . "';</script>";
				exit;
			} else {
				header( "Location:" . $strRedirectUrl );
				exit;
			}
		} else {
			$intStartTime = time();
		  $_SESSION[$strPortalSessionLoadTime] = $intStartTime;
		}
	}
	
	function urlExists( $strUrl ) {
	  $arrStrHeaders = @get_headers( $strUrl );
	  return is_array( $arrStrHeaders ) ? preg_match( '/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $arrStrHeaders[0] ) : false;
	}
	
	function setArrayDataToObject( $arrstrData, $object ) {
		if( false == valArr( $arrstrData ) || false == is_object( $object ) ) return false;
		$boolRequiresUpdate = false;
	
		foreach( $arrstrData as $key => $value ) {
	
			if( NULL === $value || true == valStr( $value ) ) {
				$strGetFunctionName = 'get' . $key;
				$strSetFunctionName = 'set' . $key;
	
				if( true == method_exists( $object, $strGetFunctionName ) && strtolower( trim( $value ) ) != strtolower( $object->$strGetFunctionName() ) ) {
					display( 'Change in that ::' );
					display( $value );
					$object->$strSetFunctionName( $value );
					$boolRequiresUpdate = true;
				}
			}
		}
	
		return $boolRequiresUpdate;
	}
	
	function valFile( $strFile, $intFileSize = 1 ) {
		return ( true == file_exists( $strFile ) && $intFileSize <= filesize( $strFile ) ) ? true : false;
	}
	
	function valStr( $str, $intLen = 1 ) {
		$str = trim( (string ) $str );
		return ( true == isset( $str[0] ) && $intLen <= strlen( $str ) ) ? true : false;
	}
	
	function valArr( $arr, $intCount = 1, $boolCheckForEquality = false ) {
		$boolIsValid = ( true == is_array( $arr ) && $intCount <= count( $arr ) ) ? true : false;
		if( true == $boolCheckForEquality && true == $boolIsValid ) $boolIsValid = ( $intCount == count( $arr ) ) ? true : false;
		return $boolIsValid;
	}
	
	function valIntArr( $arrMixValues ) {
		$arrintValues = array();
	
		if( true == valArr( $arrMixValues ) ) {
	
			foreach( $arrMixValues as $value ) {
	
				if( true == preg_match( '/^\d+$/', $value ) ) {
					$arrintValues[] = $value;
				}
			}
		}
	
		return $arrintValues;
	}
	
	function valObj( $obj, $strClass, $strMethod = NULL, $strValue = NULL ) {
		$boolIsValid = ( true == is_object( $obj ) && true == ( $obj instanceof $strClass ) ) ? true : false;
	
		if( true == $boolIsValid && NULL !== $strMethod ) {
			$boolIsValid &= ( ( true == method_exists( $obj, 'get' . $strMethod ) && true == valStr( $obj->{'get' . $strMethod}() ) )) ? true : false;
		}
	
		if( true == $boolIsValid && NULL !== $strValue ) {
			$boolIsValid &= ( (string) $strValue === (string) $obj->{'get' . $strMethod}() ) ? true : false;
		}
	
		return $boolIsValid;
	}
	
	function assertObj( $obj, $strClass ) {
		$boolIsValid = ( true == is_object( $obj ) && true == ( $obj instanceof $strClass ) ) ? true : false;
	
		if( false == $boolIsValid ) {
			trigger_error( 'Object of ' . $strClass . ' did not load', E_USER_ERROR );
			exit;
		}
	
		return $boolIsValid;
	}
	
	function getValuesWithoutEmptyString( $strValue ) {
		if( true == is_string( $strValue ) && false != preg_match( '/^[\'\s a-zA-Z0-9\#\-\+\[\]\/\'\.]{1,}$/', stripslashes( $strValue ) ) ) {
			return true;
		}
	}
	
	function getValuesWithSpecialCharacters( $strValue ) {
		if( true == is_string( $strValue ) && false != preg_match( '/[^a-zA-Z0-9\s\#\-\+\[\]\/\'\.]/', stripslashes( $strValue ) ) ) {
			return true;
		}
	}
	
	function usortCallbackAsc( $arr1, $arr2 ) {
		if( $arr1['cmp_value'] == $arr2['cmp_value'] ) return 0;
	
		return ( $arr1['cmp_value'] < $arr2['cmp_value'] ) ? -1 : 1;
	}
	
	function usortCallbackDsc( $arr1, $arr2 ) {
		if( $arr1['cmp_value'] == $arr2['cmp_value'] ) return 0;
	
		return ( $arr1['cmp_value'] > $arr2['cmp_value'] ) ? -1 : 1;
	}
	
	function sortNestedArrayAssoc( $array ) {
	  ksort( $array );
	
	  foreach( $array as $key => $value ) {
	  	if( true == is_array( $value ) ) {
	    	//sortNestedArrayAssoc( $value );
	    	krsort( $value );
	
	    	$array[$key] = $value;
	   	}
		}
	
		return $array;
	}
	
	function arrayValuesExistsInArray( $arrmixSearchValues, $arrmixMasterArray, $boolMatchAll = false ) {
	
		if( false == valArr( $arrmixSearchValues ) || false == valArr( $arrmixMasterArray ) ) return false;
	
		if( true == $boolMatchAll ) {
	
			$boolHasMatch = true;
	
			foreach( $arrmixSearchValues as $arrmixSearchValue ) {
				if( false == in_array( $arrmixSearchValue, $arrmixMasterArray ) ) {
					$boolHasMatch = false;
					break;
				}
			}
	
			return $boolHasMatch;
		} else {
	
			foreach( $arrmixSearchValues as $arrmixSearchValue ) {
				if( true == in_array( $arrmixSearchValue, $arrmixMasterArray ) ) {
					return true;
				}
			}
	
			return false;
		}
	}
	
	function arrayKeysExistsInArray( $arrmixSearchValues, $arrmixMasterArray, $boolMatchAll = false ) {
	
		if( false == valArr( $arrmixSearchValues ) || false == valArr( $arrmixMasterArray ) ) return false;
	
		if( true == $boolMatchAll ) {
	
			$boolHasMatch = true;
	
			foreach( $arrmixSearchValues as $arrmixSearchValue ) {
				if( false == array_key_exists( $arrmixSearchValue, $arrmixMasterArray ) ) {
					$boolHasMatch = false;
					break;
				}
			}
	
			return $boolHasMatch;
		} else {
	
			foreach( $arrmixSearchValues as $arrmixSearchValue ) {
				if( true == array_key_exists( $arrmixSearchValue, $arrmixMasterArray ) ) {
					return true;
				}
			}
	
			return false;
		}
	}
	
	function getArrayValue( $arrstrNeedle, $arrHaystack ) {
	  $value = $arrHaystack;
	
	  if( true == is_array ( $arrstrNeedle ) ) {
	    foreach ( $arrstrNeedle as $strKey ) {
	      if( true == array_key_exists($strKey, $value) ) {
	        $value = $value[$strKey];
	      } else {
	        return NULL;
	      }
	    }
	  }
	
	  return $value;
	}
	
	function getRemoteIpAddress() {
	
		if( false == empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//When client have any proxy, the address string contans multiple ips separated hy commas.
			$arrstrIps = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			return $arrstrIps[0];
	
		} else {
			return ( true == isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' );
		}
	}
	
	// This function will convert the XML string to array
	
	function xmlToArray( $strContent, $getAttributes = 1 ) {
	  if( !$strContent ) return array();
	
	  if( !function_exists( 'xml_parser_create' ) ) {
	    return array();
	  }
	
	  //Get the XML parser of PHP - PHP must have this module for the parser to work
	  $parser = xml_parser_create();
	  xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
	  xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
	  xml_parse_into_struct( $parser, $strContent, $xmlValues );
	  xml_parser_free( $parser );
	
	  if( !$xmlValues ) return;
	
	  //Initializations
	  $xmlArray = array();
	  $parents = array();
	  // $opened_tags = array();
	  $arr = array();
	
	  $current = &$xmlArray;
	
	  //Go through the tags.
	  foreach( $xmlValues as $data ) {
	    unset( $attributes, $value );//Remove existing values, or there will be trouble
	    extract( $data );//We could use the array by itself, but this cooler.
	
	    $result = '';
	    if( $getAttributes ) {//The second argument of the function decides this.
	      $result = array();
	      if( true == isset( $value ) ) $result['value'] = $value;
	
	      //Set the attributes too.
	      if( true == isset( $attributes ) ) {
	        foreach( $attributes as $attr => $val ) {
	          if( 1 == $getAttributes ) $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
	          // :TODO: should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too
	        }
	      }
	    } elseif( true == isset( $value ) ) {
	      $result = $value;
	    }
	
	    //See tag status and do the needed.
	    if( 'open' == $type ) { //The starting of the tag '<tag>'
	      $parent[$level-1] = &$current;
	
	      if( !is_array( $current ) || ( !in_array( $tag, array_keys( $current ) )) ) { //Insert New tag
	        $current[$tag] = $result;
	        $current = &$current[$tag];
	
	      } else { //There was another element with the same tag name
	        if( true == isset( $current[$tag][0] ) ) {
	          array_push( $current[$tag], $result );
	        } else {
	          $current[$tag] = array( $current[$tag], $result );
	        }
	        $last = count( $current[$tag] ) - 1;
	        $current = &$current[$tag][$last];
	      }
	
	    } elseif( 'complete' == $type ) { //Tags that ends in 1 line '<tag />'
	      //See if the key is already taken.
	      if( !isset( $current[$tag] ) ) { //New Key
	        $current[$tag] = $result;
	
	      } else { //If taken, put all things inside a list(array)
	        if(( is_array( $current[$tag] ) and 0 == $getAttributes )//If it is already an array...
	            or ( isset( $current[$tag][0] ) and is_array( $current[$tag][0] ) and 1 == $getAttributes ) ) {
	          array_push( $current[$tag], $result ); // ...push the new element into that array.
	        } else { //If it is not an array...
	          $current[$tag] = array( $current[$tag], $result ); //...Make it an array using using the existing value and the new value
	        }
	      }
	
	    } elseif( 'close' == $type ) { //End of tag '</tag>'
	      $current = &$parent[$level-1];
	    }
	  }
	
	  return( $xmlArray );
	}
	
	function decodePath( $strUri ) {
		$strUri = urldecode( $strUri );
	
		$intPosition = strpos( $strUri, '://' );
	
		$strScheme = false;
		if( false != $intPosition ) {
			$strScheme = substr( $strUri, 0, $intPosition );
		}
	
		if( true == isExternalUrl( $strUri ) ) {
			return $strUri;
		} else {
			if( '/' == substr( $strUri, 0, 1 ) ) {
				return $strUri;
			}
		}
	
		return '/' . $strUri;
	}
	
	function isExternalUrl( $strUri ) {
	
		$intPosition = strpos( $strUri, '://' );
	
		$strScheme = false;
		if( false != $intPosition ) {
			$strScheme = substr( $strUri, 0, $intPosition );
		}
	
		if( $strScheme == 'http' || $strScheme == 'https' ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the first non-null valued argument or NULL if all are NULL
	 *
	 * @return var
	 */
	function coalesce() {
	
	  $arrstrArgs = func_get_args();
	
		foreach ( $arrstrArgs as $strArg ) {
	    if( true == isset( $strArg ) ) {
				return $strArg;
	    }
	  }
	
	  return NULL;
	}
	
	/**
	 * Accepts an associative array of key => value pairs and returns the first non-null valued item
	 *
	 * @return stdClass
	 */
	function coalesceArrayValues( $arrstrKeyValues ) {
	
		$objResult = new stdClass();
	
		foreach ( $arrstrKeyValues as $strKey => $strValue ) {
			if( true == isset( $strValue ) ) {
				$objResult->key = $strKey;
				$objResult->value = $strValue;
				return $objResult;
			}
		}
	
	  return $objResult;
	}
	
	/**
	 * Recursivly removes all keys with a null value from the given array. No array is returned to save
	 * on PHP memory usage
	 *
	 * @param type $arrmixValues An array that needs nulls removed
	 */
	function removeNullsFromArray( &$arrmixValues ) {
	  foreach( $arrmixValues as $mixKey => $mixValue ) {
	    if( true == is_array( $mixValue ) ) {
	      removeNullsFromArray($arrmixValues[$mixKey]);
	    }
	
	    if( true == is_null( $mixValue ) ) {
	      unset($arrmixValues[$mixKey]);
	    }
	  }
	}
	
	function determineMonth( $intMonth ) {
		return date( 'F', mktime( 0,0,0,$intMonth,1,2000 ) );
	}
	
	function getAlfaNumericValue( $strValue ) {
		return preg_replace( "/[^A-Za-z0-9]/","", $strValue );
	}
	
	function getNumericValue( $strValue ) {
		return preg_replace( "/[^0-9]/", "", $strValue );
	}
	
	function determineDefaultSessionTimeOut( $strPortalName ) {
		$intDefaultSessionTimeOut = 60;
	
		if( 'resident_works' === $strPortalName ) {
			$intDefaultSessionTimeOut = 360;
		}
		return $intDefaultSessionTimeOut;
	}
	
	function generateDateRangeArray( $strStartDate, $strEndDate ) {
	
			$arrstrDateRange = array();
	
			if( true === is_string( $strStartDate ) ) $strStartDate = strtotime( $strStartDate );
			if( true === is_string( $strEndDate ) )  $strEndDate = strtotime( $strEndDate );
	
			if( $strStartDate > $strEndDate ) return generateDateRangeArray( $strEndDate, $strStartDate );
	
			do{
				$arrstrDateRange[] = date( 'Y/m/d', $strStartDate );
				$strStartDate = strtotime( '+ 1 day' , $strStartDate );
			}
			while( $strStartDate <= $strEndDate );
	
			return $arrstrDateRange;
	}
	
	function flowerBox( $strInnerText, $intBoxLength = 80, $strFlowerChar = '*', $boolReturn = false ) {
	
		if( false == valStr( $strInnerText ) ) {
			return $strInnerText;
		}
	
		$strFlowerChar = ( true == valStr( $strFlowerChar ) ) ? $strFlowerChar[0] : '*';
		$intBoxLength = max( $intBoxLength, strlen( $strInnerText ) + 4 );
	
		$strOutput = str_repeat( $strFlowerChar, $intBoxLength ) . "\n";
		$strOutput .= str_pad( " $strInnerText ", $intBoxLength, $strFlowerChar, STR_PAD_BOTH ) . "\n";
		$strOutput .= str_repeat( $strFlowerChar, $intBoxLength ) . "\n";
	
		if( true == $boolReturn ) {
			return $strOutput;
		}
	
		echo $strOutput;
	}
	
	function formatCallPhoneNumber( $strString ) {
	
		if( false == valStr( $strString ) ) return NULL;
	
		$strString = preg_replace( '/[^a-zA-Z0-9]/', '', trim( $strString ) );
	
		if( 10 < strlen( $strString ) && 1 == $strString[0] ) {
			$strString = substr( $strString, 1, strlen( $strString ) - 1 );
		}
	
		$strString = '(' . substr( $strString, 0, 3 ) . ') ' . substr( $strString, 3, 3 ) . '-' . substr( $strString, 6, 10 );
	
		return $strString;
	}
	
	function convertNumberToWords( $fltNumber, $strUnit = 'Dollars' ) {
	
		$fltNumber 	= str_replace( ',', '', $fltNumber );
	
		if( false == is_numeric( $fltNumber ) ) {
			return false;
		}
	
		$strHyphen   = '-';
		$strConjunction = ' and ';
		$strSeparator  = ', ';
		$strNegative  = 'negative ';
		$strDecimal   = ' point ';
	
		$arrstrDecimalUnit = array( 'Dollars' => 'Cent' );
	
		$arrstrDictionary = array( 0          => 'zero',
									1          => 'one',
									2          => 'two',
									3          => 'three',
									4          => 'four',
									5          => 'five',
									6          => 'six',
									7          => 'seven',
									8          => 'eight',
									9          => 'nine',
									10         => 'ten',
									11         => 'eleven',
									12         => 'twelve',
									13         => 'thirteen',
									14         => 'fourteen',
									15         => 'fifteen',
									16         => 'sixteen',
									17         => 'seventeen',
									18         => 'eighteen',
									19         => 'nineteen',
									20         => 'twenty',
									30         => 'thirty',
									40         => 'fourty',
									50         => 'fifty',
									60         => 'sixty',
									70         => 'seventy',
									80         => 'eighty',
									90         => 'ninety',
									100         => 'hundred',
									1000        => 'thousand',
									1000000       => 'million',
									1000000000     => 'billion',
									1000000000000    => 'trillion',
									1000000000000000  => 'quadrillion',
									1000000000000000000 => 'quintillion'
								);
	
		if( $fltNumber < 0 ) {
	    return $strNegative . convertNumberToWords( abs( $fltNumber ) );
	  }
	
	  $strWords = $intFraction = NULL;
	
	  if( false !== strpos( $fltNumber, '.' ) ) {
	    list( $fltNumber, $intFraction ) = explode( '.', $fltNumber );
	  }
	
	  switch( true ) {
	    case $fltNumber < 21:
	      $strWords 			= $arrstrDictionary[$fltNumber] . ' ' . $strUnit;
	      break;
	
	    case $fltNumber < 100:
	      $intTens  			= ( ( int) ( $fltNumber / 10 ) ) * 10;
	      $intRemainder 		= $fltNumber % 10;
	      $strWords 			= $arrstrDictionary[$intTens];
	      if( 0 < $intRemainder ) {
	        $strWords 		.= $strHyphen . $arrstrDictionary[$intRemainder];
	      }
	      $strWords 			.= ' ' . $strUnit;
	      break;
	
	    case $fltNumber < 1000:
	      $intHundreds 		= $fltNumber / 100;
	      $intRemainder 		= $fltNumber % 100;
	      $strWords 			= $arrstrDictionary[$intHundreds] . ' ' . $arrstrDictionary[100];
	      if( 0 < $intRemainder ) {
	        $strWords 		.= $strConjunction . convertNumberToWords( $intRemainder );
	      } else {
	      	$strWords 		.= ' ' . $strUnit;
	      }
	      break;
	
	    default:
	      $intBaseUnit 		= pow( 1000, floor( log( $fltNumber, 1000 ) ) );
	      $intNumBaseUnits 	= ( int ) ( $fltNumber / $intBaseUnit );
	      $intRemainder 		= $fltNumber % $intBaseUnit;
	      $strWords 			= convertNumberToWords( $intNumBaseUnits, NULL ) . ' ' . $arrstrDictionary[$intBaseUnit];
	
	      if( 0 < $intRemainder ) {
	        $strWords 		.= $intRemainder < 100 ? $strConjunction : $strSeparator;
	        $strWords 		.= convertNumberToWords( $intRemainder );
	      } else {
	      	$strWords 		.= ' ' . $strUnit;
	      }
	      break;
	  }
	
	  if( NULL !== $intFraction && is_numeric( $intFraction ) && 0 < $intFraction ) {
	  	if( true == is_null( $strUnit ) ) $strWords .= $strDecimal;
	
	    $arrWords = array();
	    foreach( str_split( ( string ) $intFraction ) as $fltNumber ) {
	      $arrWords[] = $arrstrDictionary[$fltNumber];
	    }
	    $strWords .= ' ' . implode( ' ', $arrWords );
	
	    if( true == array_key_exists( $strUnit, $arrstrDecimalUnit ) )  $strWords .= ' ' . $arrstrDecimalUnit[$strUnit];
	  }
	
	  return ucwords( $strWords );
	}
	
	function ordinalize( $intNumber ) {
	
		if( false == in_array( ( $intNumber % 100 ), array( 11,12,13 ) ) ) {
	
			switch( $intNumber % 10 ) {
				// Handle 1st, 2nd, 3rd
				case 1: return $intNumber . 'st';
				case 2: return $intNumber . 'nd';
				case 3: return $intNumber . 'rd';
			}
		}
	
		return $intNumber . 'th';
	}
	
	function getTimelineFormat( $strDate ) {
		require_once( PATH_LIBRARY_COMMON . 'CDates.class.php' );
	
		$intDateTimestamp			= is_string( $strDate ) ? strtotime( $strDate ) : $strDate;
		$intCurrentTimestamp		= time();
		$arrstrDateDifferenceParts 	= CDates::getDateDifference( $intDateTimestamp, $intCurrentTimestamp );
	
		if( $intDateTimestamp > $intCurrentTimestamp ) {
			$strTense = 'from now';
		} else {
			$strTense = 'ago';
		}
	
		if( 0 < $arrstrDateDifferenceParts['days_total'] ) {
			if( 1 == $arrstrDateDifferenceParts['days_total'] ) {
				$strTimelineFormat = $arrstrDateDifferenceParts['days_total'] . ' day ' . $strTense;
			} else {
				$strTimelineFormat = $arrstrDateDifferenceParts['days_total'] . ' days ' . $strTense;
			}
	
			return $strTimelineFormat;
		}
	
		if( 0 < $arrstrDateDifferenceParts['hours_total'] ) {
			if( 1 == $arrstrDateDifferenceParts['hours_total'] ) {
				$strTimelineFormat = $arrstrDateDifferenceParts['hours_total'] . ' hour ' . $strTense;
			} else {
				$strTimelineFormat = $arrstrDateDifferenceParts['hours_total'] . ' hours ' . $strTense;
			}
	
			return $strTimelineFormat;
		}
	
		if( 0 < $arrstrDateDifferenceParts['minutes_total'] ) {
			if( 1 == $arrstrDateDifferenceParts['minutes_total'] ) {
				$strTimelineFormat = $arrstrDateDifferenceParts['minutes_total'] . ' minute ' . $strTense;
			} else {
				$strTimelineFormat = $arrstrDateDifferenceParts['minutes_total'] . ' minutes ' . $strTense;
			}
	
			return $strTimelineFormat;
		}
	
	
		if( 1 == $arrstrDateDifferenceParts['seconds_total'] ) {
			$strTimelineFormat = $arrstrDateDifferenceParts['seconds_total'] . ' second ' . $strTense;
		} else {
			$strTimelineFormat = $arrstrDateDifferenceParts['seconds_total'] . ' seconds ' . $strTense;
		}
	
		return $strTimelineFormat;
	}
	
	function fetchOrCacheData( $strSql, $intSpecificLifetime = false, $strCacheKey = NULL, $objDatabase, $boolIsDebug = false ) {
	
		if( true == defined( CONFIG_CACHE_ENABLED ) || false == CONFIG_CACHE_ENABLED ) {
			return fetchData( $strSql, $objDatabase );
		}
	
		$strKey = sha1( ( true == is_null( $strCacheKey ) ? $strSql : $strCacheKey ) );
	
		if( false === ( $arrstrMixData = CCache::getCacheData( $strKey, DATA_CACHE_MEMORY ) ) ) {
	
			$objDataset = $objDatabase->createDataset();
	
			if( true == $objDataset->execute( $strSql ) ) {
				if( 0 < $objDataset->getRecordCount() ) {
					while( false == $objDataset->eof() ) {
						$arrstrMixData[] = $objDataset->fetchArray();
						$objDataset->next();
					}
				}elseif( 0 < $objDataset->getAffectedRows() ) {
					$arrstrMixData[] = $objDataset->getAffectedRows();
				}
			}
	
			if( true == $boolIsDebug ) {
				logText( '[' . date( 'm-d-Y H:i:s' ) . ']' . 'From Database' );
			}
	
			if( false == is_array( $arrstrMixData ) ) {
				$arrstrMixData = array();
			}
	
			CCache::setCache( $strKey, $arrstrMixData, $intSpecificLifetime , array(), DATA_CACHE_MEMORY );
	
		} elseif( true == $boolIsDebug ) {
			logText( '[' . date( 'm-d-Y H:i:s' ) . ']' . 'From Cache' );
		}
	
		return $arrstrMixData;
	}
	
	function logText( $strText ) {
	
		$strFileName = 'lead_alert_debug_output_2138_' . date( 'd' ) . '.log';
		$strFilePath = PATH_NON_BACKUP_MOUNTS . 'web_service_logs/lead_logs/' . date( 'Y' ) . '/' . date( 'm' ) . '/';
	
		require_once( PATH_LIBRARY_COMMON . 'CLogger.class.php' );
		require_once( PATH_LIBRARY_COMMON 	. 'CFileIo.class.php' );
	
		// We need to create the folder with the use of recursiveMakeDir function from CFileIo, else the proper permissions of the www run user won't be set.
		if( false == is_dir( $strFilePath ) ) {
			if( false == CFileIo::recursiveMakeDir( $strFilePath ) ) {
				return true;
			}
		}
	
		$objLogger = new CLogger( $strFileName, $strFilePath );
		$objLogger->writeLine( $strText );
	}
	
	function logOpeningRequest( $intPsProductId, $strModuleName = NULL, $strActionName = NULL, $objManagementCompany = NULL, $objWebsite = NULL, $objCompanyUser = NULL ) {
	
		//if ( CPsProduct::PS_PRODUCT_CLIENT_ADMIN != $intPsProductId ) return;
		if( 'production' != CConfig::get( 'environment' ) ) return;
		if( 'production' == CConfig::get( 'environment' ) && ( 5 != rand ( 2, 300 ) ) ) return;
	
		if( false == defined( 'CONFIG_SQL_LOGGING_JOB_QUEUE_SERVER' ) || false == defined( 'CONFIG_SQL_LOGGING_JOB_QUEUE_URI' ) ) return;
	
		$arrstrParams = array( "ps_product_id" => $intPsProductId,
									"module_name" => $strModuleName,
									"action_name" => $strActionName,
									"management_company_id" => ( ( true == valObj ( $objManagementCompany, 'CManagementCompany' ) ) ? $objManagementCompany->getId() : NULL ),
									"website" => ( ( true == valObj ( $objWebsite, 'CWebsite' ) ) ? $objWebsite->getId() : NULL ),
									"company_user" => ( ( true == valObj ( $objCompanyUser, 'CCompanyUser' ) ) ? $objCompanyUser->getId() : NULL ) );
	
		$arrstrOptions = array( "name"=>"page_entrance_logs" );
	
		// Let's return if we can't connect to the zend queue.
		$intDefaultSocketTimeOut = ini_get( 'default_socket_timeout' );
	
		if ( true == defined( 'CONFIG_SQL_LOGGING_JOB_QUEUE_URI' ) && 0 < strlen ( CONFIG_SQL_LOGGING_JOB_QUEUE_URI ) ) {
			ini_set('default_socket_timeout', 1);
			$strResponse = file_get_contents(CONFIG_SQL_LOGGING_JOB_QUEUE_URI,FALSE,NULL,0,4);
	
			if ( 'live' != $strResponse ) {
				trigger_error( 'Couldnt connect to the queue.[' . $strResponse . ']', E_USER_WARNING );
				ini_set('default_socket_timeout', $intDefaultSocketTimeOut );
				return;
			}
	
			ini_set('default_socket_timeout', $intDefaultSocketTimeOut );
	
			if ( 0 < strlen ( CONFIG_SQL_LOGGING_JOB_QUEUE_URI ) ) {
				$objQueue = new ZendJobQueue( CONFIG_SQL_LOGGING_JOB_QUEUE_SERVER );
				$objQueue->createHttpJob( CONFIG_SQL_LOGGING_JOB_QUEUE_URI . "?module=sql_log_jobs&action=process_entrance_logs", $arrstrParams, $arrstrOptions );
			}
		}
	
		return;
	}
	
	function logClosingRequest( $intPsProductId, $strModuleName = NULL, $strActionName = NULL, $objManagementCompany = NULL, $objWebsite = NULL, $objCompanyUser = NULL ) {
	
		//if ( CPsProduct::PS_PRODUCT_CLIENT_ADMIN != $intPsProductId ) return;
		if( 'production' != CConfig::get( 'environment' ) ) return;
		if( 'production' == CConfig::get( 'environment' ) && ( 5 != rand ( 2, 300 ) ) ) return;
	
		if( false == defined( 'CONFIG_SQL_LOGGING_JOB_QUEUE_SERVER' ) || false == defined( 'CONFIG_SQL_LOGGING_JOB_QUEUE_URI' ) ) return;
	
		if ( true == isset ( $GLOBALS['query_times'] )
				&& true == isset ( $GLOBALS['end_times'] )
				&& true == isset ( $GLOBALS['queries'] )
				&& true == isset ( $GLOBALS['query_databases'] ) ) {
	
			$intLengthOfGlobalArray=sizeof($GLOBALS['queries']);
			for($intCounter =0; $intCounter < $intLengthOfGlobalArray; $intCounter++) {
				$GLOBALS['queries'][$intCounter] = preg_replace( "/'.+'/", "", trim( $GLOBALS['queries'][$intCounter] ) );
			}
			$arrstrParams = array( "query_times" => $GLOBALS['query_times'],
									"end_times" => $GLOBALS['end_times'],
									"queries" => $GLOBALS['queries'],
									"query_databases" => $GLOBALS['query_databases'],
									"ps_product_id" => $intPsProductId,
									"module_name" => $strModuleName,
									"action_name" => $strActionName,
									"management_company_id" => ( ( true == valObj ( $objManagementCompany, 'CManagementCompany' ) ) ? $objManagementCompany->getId() : NULL ),
									"website" => ( ( true == valObj ( $objWebsite, 'CWebsite' ) ) ? $objWebsite->getId() : NULL ),
									"company_user" => ( ( true == valObj ( $objCompanyUser, 'CCompanyUser' ) ) ? $objCompanyUser->getId() : NULL ) );
	
			$arrstrOptions = array( "name"=>"page_exit_logs" );
	
			if( 0 < strlen( CONFIG_SQL_LOGGING_JOB_QUEUE_URI ) ) {
				$objQueue = new ZendJobQueue( CONFIG_SQL_LOGGING_JOB_QUEUE_SERVER );
				$objQueue->createHttpJob( CONFIG_SQL_LOGGING_JOB_QUEUE_URI . '?module=sql_log_jobs&action=process_exit_logs', $arrstrParams, $arrstrOptions );
			}
		}
	
		return;
	}
	
	function formattedPercentage( $intNumerator, $intDenominator, $intDecimalPrecision = 2 ) {
	
		$fltFormattedPercentage = number_format( ( ( $intNumerator * 100 ) / $intDenominator ), $intDecimalPrecision, '.', '' );
	
		return ( 0 < $fltFormattedPercentage ) ? $fltFormattedPercentage : 0;
	}
	
	function sortTwoDimensionalArray( $arrMixData, $strIndex, $strOrder, $boolNatSort = false, $boolCaseSensitive = false ) {
	
		if( false == valArr( $arrMixData ) || 0 == count( $arrMixData ) ) return $arrMixData;
	
		foreach( array_keys( $arrMixData ) as $intkey ) {
			$arrReKeyData[$intkey] = $arrMixData[$intkey][$strIndex];
		}
	
		if( false == $boolNatSort ) {
			if( 'asc' == $strOrder ) {
				asort( $arrReKeyData );
			} else {
				arsort( $arrReKeyData );
			}
		} else {
			if( true === $boolCaseSensitive ) {
				natsort( $arrReKeyData );
			} else {
				natcasesort( $arrReKeyData );
			}
	
			if( 'asc' != $strOrder ) {
				$arrReKeyData = array_reverse( $arrReKeyData, true );
			}
		}
	
		foreach( array_keys( $arrReKeyData ) as $intkey ) {
			if( false != is_numeric( $intkey ) ) {
				$arrReKeyDataSorted[] = $arrMixData[$intkey];
			} else {
				$arrReKeyDataSorted[$intkey] = $arrMixData[$intkey];
			}
		}
	
		return $arrReKeyDataSorted;
	}
	
	function segregatedArrayObjects( $strIndexKey, $arrmixArray, $strKey ) {
		if( false == valArr( $arrmixArray ) ) return false;
		
		$arrmixReturnData = array();
		
		if( true == valStr( $strIndexKey ) && true == valStr( $strKey ) ) {
			foreach( $arrmixArray as $arrData ) {
				$strIndexMethod = 'get' . $strIndexKey;
				$strKeyMethod 	= 'get' . $strKey;
				$arrmixReturnData[$arrData->$strIndexMethod()][$arrData->$strKeyMethod()] = $arrData;
			}
		}

		return $arrmixReturnData;
	}

?>