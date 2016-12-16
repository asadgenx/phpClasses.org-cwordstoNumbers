<?php
	/**
    *  A simple example to use this class
	*  All you need to do is to include the class and use it right away as given below
	*  In other terms, just do ctrl+c and ctrl+v.
    */	
require '../src/cWordsToNumbers.class.php';

	
$objWordsToNumbers = new cWordsToNumbers();
$objWordsToNumbers->setEnableNumberFormatting( true );

echo  $objWordsToNumbers->displayNumbers( "nine" ) . "<br/>";
echo  $objWordsToNumbers->displayNumbers( "ninty nine" ) . "<br/>";
echo  $objWordsToNumbers->displayNumbers( "nine hundred and ninty nine" ) . "<br/>";
echo  $objWordsToNumbers->displayNumbers( "nine thousand nine hundred and ninty nine" ) . "<br/>";
echo  $objWordsToNumbers->displayNumbers( "ninty thousand nine hundred and ninty nine" ) . "<br/>";
echo "<h2>Indian Number system</h2>";

echo  $objWordsToNumbers->displayNumbers( "nine lakh nine thousand nine hundred and ninty nine" ) . "<br/>";

echo  $objWordsToNumbers->displayNumbers( "nine crore nine thousand nine hundred and ninty nine" ) . "<br/>";

echo "<h2>Western Number system</h2>";

echo  $objWordsToNumbers->displayNumbers( "nine hundred and ninty nine thousand nine hundred and ninty nine" ) . "<br/>";

echo  $objWordsToNumbers->displayNumbers( "nine million nine hundred and ninty nine thousand nine hundred and ninty nine" ) . "<br/>";
?>