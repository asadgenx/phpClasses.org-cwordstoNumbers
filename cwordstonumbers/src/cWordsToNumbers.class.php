<?php 

class cWordsToNumbers {
	
	/**
	* @name cWordsToNumbers.class.php
	* @author Asad Genx <asadgx88@gmail.com>
	* @version 1.01
	* @license MIT https://opensource.org/licenses/MIT
	* 
	* Requires PHP 5+
	* Requires curl extension optional
	* 
	*       
	*/
	
	private $strErrorMsg;
	private $strFormattedOutputNumbers;
	
	private $arrstrNumbersInWords;
	private $arrstrWordsToNumbersMapper;
	
	private $intOutputNumbers;
	
	private $boolIsIndianNumberSystem;
	private $boolEnableNumberFormatting;
	
	public function __construct() {
		
		$this->m_boolIsIndianNumberSystem 		= true;
		$this->m_boolEnableNumberFormatting		= false;
		$this->m_arrstrNumbersInWords 			= array();
		$this->m_strErrorMsgs 					= NULL;
	}
		
	/**
	*
	*	Setter Functions
	*/
	
	public function setEnableNumberFormatting( $boolEnableNumberFormatting ) {		
		$this->m_boolEnableNumberFormatting = $boolEnableNumberFormatting;
	}
	
	/**
    * Validates and then sanitizes the given user data, returns false on invalid data
	* Also scolds the user if he repeatedly keeps adding wrong inputs.
    * 
    * @return boolean
    */
	
	private function validateAndSanitizeUserInput() {

		switch(NULL) {
			default:
			
				// Checking if there is actually any input provided
				if( 0 == strlen( $this->m_strNumbersInWords ) ) {
					$this->m_strErrorMsg = 'Invalid input';
					break;
				} else {							
					$this->m_strNumbersInWords 		= str_replace( ' and ', ' ', strtolower( trim( $this->m_strNumbersInWords ) ) );
					$this->m_strNumbersInWords		= preg_replace( '/\s+/', ' ', $this->m_strNumbersInWords );
					$this->m_arrstrNumbersInWords 	= explode( ' ', $this->m_strNumbersInWords );				
					
				}
				
				// checking if the mapper json file is available in the path or not.				
				if( false == file_exists( '../config/words_to_numbers_mapper.json' ) ) {					
					$this->m_strErrorMsg = 'Dude, the Mapper File is missing. Go fix its path';
					break;
				} else {									
					$strWordsToNumberMappers 			= file_get_contents( '../config/words_to_numbers_mapper.json' );
					$this->m_arrstrWordsToNumbersMapper = json_decode( $strWordsToNumberMappers, true );
				}
				
				// checking if every words entered is present in the mapper or not.
				 $arrstrFlippedWordsToNumbersMapper = array_flip( $this->m_arrstrWordsToNumbersMapper );
				 if( 0 != count( array_diff( $this->m_arrstrNumbersInWords, $arrstrFlippedWordsToNumbersMapper ) ) ) {
					$this->m_strErrorMsg = 'Unable to recognize the input. Please verify you input.';
					if( false == isset( $_COOKIE['warning_counter'] ) ) {
						setcookie( 'warning_counter', 1, 0, "/" );
					} else {
						setcookie( 'warning_counter', ( $_COOKIE['warning_counter'] + 1 ), 0, "/" );
					}
					if( true == isset( $_COOKIE['warning_counter'] ) ) {
						if( 4 == $_COOKIE['warning_counter'] ) $this->m_strErrorMsg = 'I think you have a spelling mistake or something.';
						if( 6 == $_COOKIE['warning_counter'] ) $this->m_strErrorMsg = 'Are you trying to bullshit me?';
						if( 8 == $_COOKIE['warning_counter'] ) $this->m_strErrorMsg = 'OK, stop bullshitting me.';
						if( 10 < $_COOKIE['warning_counter'] ) $this->m_strErrorMsg = 'Seriously stop it dude, go play with something else.';
					}

					break;
				 }			
				
				// checking if there it's limit
				$intCroresCount = 0;
				$intMillionsCount = 0;
				foreach( $this->m_arrstrNumbersInWords as $strNumberInWord ) {
					if( 'crore' == $strNumberInWord ) $intCroresCount ++;
					if( 'million' == $strNumberInWord ) $intMillionsCount ++;
				}
				if( 1 < $intCroresCount || 1 < $intMillionsCount ) {
					$this->m_strErrorMsg = 'Sorry, that is too much to be processed for free.';
					break;
				}
				
				return true;
		}
		return false;
	}

	/**
    * Maps the given indian number words to its corresponding digit like lakh => 1,00,000
    * 
    * @return NULL
    */
	
	private function wordsToIndianNumberMapper( $arrstrNumbersInWords ) {

		$this->m_intOutputNumber = 0;
		$intLeftOver = 0;
		foreach( $arrstrNumbersInWords as $strWords ) {
			
			switch( $strWords ) {
				
				case 'crore':
						if( 0 == $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = 1 * $this->m_arrstrWordsToNumbersMapper[$strWords];
						} else if(  0 != $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = $this->m_intOutputNumber + $this->m_arrstrWordsToNumbersMapper[$strWords];
						} else {
							$this->m_intOutputNumber = $this->m_intOutputNumber + ( $intLeftOver * $this->m_arrstrWordsToNumbersMapper[$strWords]);
						}
						$intLeftOver = 0;
						break;
				case 'hundred':
				case 'thousand':
				case 'lakh':
						if( 0 == $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = 1 * $this->m_arrstrWordsToNumbersMapper[$strWords];
						} else if(  0 != $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = $this->m_intOutputNumber + $this->m_arrstrWordsToNumbersMapper[$strWords];
						} else {
							$this->m_intOutputNumber = $this->m_intOutputNumber + ( $intLeftOver * $this->m_arrstrWordsToNumbersMapper[$strWords]);
						}
						$intLeftOver = 0;
					break;
					
				default:
					$intLeftOver = $intLeftOver + $this->m_arrstrWordsToNumbersMapper[$strWords];
					break;
				
			}
		}
		$this->m_intOutputNumber = $this->m_intOutputNumber + $intLeftOver;
	}
	/**
    * Maps the given western number words to its corresponding digit like million => 1,000,000
    * 
    * @return NULL
    */
	
	private function wordsToWesternNumberMapper( $arrstrNumbersInWords ) {
		$this->m_intOutputNumber = 0;
		$intLeftOver = 0;
		foreach( $arrstrNumbersInWords as $strWords ) {
			
			switch( $strWords ) {

				case 'thousand':
						if( 0 == $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = 1 * $this->m_arrstrWordsToNumbersMapper[$strWords];
						} else if(  0 != $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = $this->m_intOutputNumber . substr( $this->m_arrstrWordsToNumbersMapper[$strWords], 1 );

						} else {
							$this->m_intOutputNumber = ($this->m_intOutputNumber +  $intLeftOver) * $this->m_arrstrWordsToNumbersMapper[$strWords];
						}

						$intLeftOver = 0;
						break;
				case 'hundred':
						if( 0 == $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = 1 * $this->m_arrstrWordsToNumbersMapper[$strWords];
						} else if(  0 != $this->m_intOutputNumber && 0 == $intLeftOver ) {
							$this->m_intOutputNumber = $this->m_intOutputNumber + $this->m_arrstrWordsToNumbersMapper[$strWords];
						} else {
							$this->m_intOutputNumber = $this->m_intOutputNumber + ( $intLeftOver * $this->m_arrstrWordsToNumbersMapper[$strWords]);
						}
						$intLeftOver = 0;
					break;
					
				default:
					$intLeftOver = $intLeftOver + $this->m_arrstrWordsToNumbersMapper[$strWords];
					break;
				
			}
		}
		$this->m_intOutputNumber = $this->m_intOutputNumber + $intLeftOver;
			
	}
	
	/**
    * Detects the number system and then send the flow to corresponding mapper methods
    * 
    * @return NULL
    */
	
	private function wordsToNumberMapper() {
		
		$arrstrPreCroreSplit 	= array();
		$arrstrPostCroreSplit 	= array();
		$boolIsCroreFound 		= false;
		
		// detecting number system		
		if( true == in_array( 'crore', $this->m_arrstrNumbersInWords ) || true == in_array( 'lakh', $this->m_arrstrNumbersInWords ) ) {
			$this->m_boolIsIndianNumberSystem = true;
		} else {
			$intHundredPos = array_search( "hundred", $this->m_arrstrNumbersInWords );
			$intThousandPos = array_search( "thousand", $this->m_arrstrNumbersInWords );			
			if( $intHundredPos < $intThousandPos || true == in_array( 'million', $this->m_arrstrNumbersInWords ) 
				|| true == in_array( 'billion', $this->m_arrstrNumbersInWords )|| true == in_array( 'trillion', $this->m_arrstrNumbersInWords ) 
				|| true == in_array( 'quadrillion', $this->m_arrstrNumbersInWords ) ) $this->m_boolIsIndianNumberSystem = false;
		}
		
		if( true == $this->m_boolIsIndianNumberSystem ) {
			
			//check there is a crore in the value
			if( true == in_array( 'crore', $this->m_arrstrNumbersInWords ) ) {
				
				foreach( $this->m_arrstrNumbersInWords as $strNumberInWord ) {
					if( 'crore' == $strNumberInWord ) $boolIsCroreFound = true;
					if( false == $boolIsCroreFound ) $arrstrPreCroreSplit[] = $strNumberInWord;
					else $arrstrPostCroreSplit[] = $strNumberInWord;					
				}
				if( 0 == count( $arrstrPreCroreSplit ) )  $arrstrPreCroreSplit[] = 'one';
				
				// final calculation				
				$this->wordsToIndianNumberMapper( $arrstrPreCroreSplit );
				$intPreCroreTotal = $this->m_intOutputNumber;
				
				$this->wordsToIndianNumberMapper( $arrstrPostCroreSplit );
				$intPostCroreTotal = $this->m_intOutputNumber;
				
				$this->m_intOutputNumber = $intPreCroreTotal . substr( $intPostCroreTotal, 1 );
			} else {
				$this->wordsToIndianNumberMapper( $this->m_arrstrNumbersInWords );
			}
			
		} else {
			$strNumbersInWords = implode( ' ', $this->m_arrstrNumbersInWords );
			$intBillion = 0;
			$arrstrBillionPreSplit = array();
			$arrstrBillionPostSplit = array();
			$intMillion = 0;
			$arrstrMillionPreSplit = array();
			$arrstrMillionPostSplit = array();
			
			if( true == strstr( $strNumbersInWords, 'billion' ) ) {
				$arrstrBillionSplit = explode( 'billion', $strNumbersInWords );
				$arrstrBillionPreSplit = ( 0 == strlen( $arrstrBillionSplit[0] ) ) ? array( 'one' ) : array_filter(  explode( ' ',$arrstrBillionSplit[0] ) );
				$this->wordsToWesternNumberMapper( $arrstrBillionPreSplit );
				$intBillion = $this->m_intOutputNumber * $this->m_arrstrWordsToNumbersMapper['billion'];
				$arrstrBillionPostSplit = $arrstrBillionSplit[1];
			}
			if( true == strstr( $strNumbersInWords, 'million' ) ) {
				$strNumbersInWords = ( 0 != count( $arrstrBillionPostSplit ) ) ?  $arrstrBillionPostSplit : $strNumbersInWords;
				$arrstrMillionSplit = explode( 'million', $strNumbersInWords );
				$arrstrMillionPreSplit = ( 0 == strlen( trim( $arrstrMillionSplit[0] ) )  ) ? array( 'one' ) : array_filter(  explode( ' ', $arrstrMillionSplit[0] ) );
				$this->wordsToWesternNumberMapper( $arrstrMillionPreSplit );
				$intMillion = $this->m_intOutputNumber * $this->m_arrstrWordsToNumbersMapper['million'];
				$arrstrBillionPostSplit = array();
				$arrstrMillionPostSplit = $arrstrMillionSplit[1];
			}
			if( 0 == count( $arrstrBillionPostSplit) && 0 != count( $arrstrMillionPostSplit) ) {
				$arrstrNumbersInWords =  array_filter(  explode( ' ', $arrstrMillionPostSplit ) );
			} else if(  0 != count( $arrstrBillionPostSplit) && 0 == count( $arrstrMillionPostSplit) ) {
				$arrstrNumbersInWords =  array_filter(  explode( ' ', $arrstrBillionPostSplit ) );
			} else {
				$arrstrNumbersInWords = $this->m_arrstrNumbersInWords;
			}
			$this->wordsToWesternNumberMapper( $arrstrNumbersInWords );
			
			$this->m_intOutputNumber = $intBillion + $intMillion + $this->m_intOutputNumber;
				
		}	
			
		if( true == $this->m_boolEnableNumberFormatting ) $this->formatNumbersBasedOnDetectedNumberSystem();		
	}
	
	/**
    * Formats the obtained result based on the detected number system, only if the formatting is enabled.
    * 
    * @return NULL
    */
	
	private function formatNumbersBasedOnDetectedNumberSystem() {
		
		$arrstrFormattedNumbers = array();
		$arrstrOutputNumbers 	= array_reverse( str_split( $this->m_intOutputNumber ) );
		
		$intCounter = 0;
		for( $intIterator = 0; $intIterator < count( $arrstrOutputNumbers ); $intIterator ++ ) {
			
			if( true == $this->m_boolIsIndianNumberSystem ) {
				if( 3 == $intCounter || ( 1 == $intCounter % 2 && 1 != $intCounter && 9 > $intCounter )
					||	10 == $intCounter || 12 == $intCounter ) $arrstrFormattedNumbers[] = ",";
				$arrstrFormattedNumbers[] = $arrstrOutputNumbers[$intIterator];
			} else {
				if(  0 == $intCounter % 3  && 0 != $intCounter  ) $arrstrFormattedNumbers[] = ",";
				$arrstrFormattedNumbers[] = $arrstrOutputNumbers[$intIterator];
			}
			$intCounter++;
		}
		
		$this->m_strFormattedOutputNumbers = implode('', array_reverse( $arrstrFormattedNumbers ) );
			
	}
	
	/**
    * The main function that collects the input from the user and sends it for processing.
    * 
    * @return string
    */
	
	public function displayNumbers( $strNumbersInWords ) {
		
		$this->m_strNumbersInWords = $strNumbersInWords;
		
		if( false == $this->validateAndSanitizeUserInput() ) return $this->m_strErrorMsg;
		
		$this->wordsToNumberMapper();
		
		return ( true == $this->m_boolEnableNumberFormatting ) ? $this->m_strFormattedOutputNumbers : $this->m_intOutputNumber;
	}
}
?>