<?php

function extract_translation_array_from_node($node)
{
    $translationArray = array();
    $sectionNodes = $node->childNodes;
    foreach ($sectionNodes as $sectionNode)
    {
        if( XML_ELEMENT_NODE === $sectionNode->nodeType)
        {
            $translationNodes = $sectionNode->childNodes;
            foreach ($translationNodes as $translationNode)
            {
                if( XML_ELEMENT_NODE === $translationNode->nodeType )
                {
                    $subsectionNodes = $translationNode->childNodes;
                    $isSubsection = FALSE;
                    foreach ( $subsectionNodes as $subsectionNode )
                    {
                        if( XML_ELEMENT_NODE === $subsectionNode->nodeType )
                        {
                            $translationArray[$sectionNode->nodeName.'/'.$translationNode->nodeName.'/'.$subsectionNode->nodeName] = $subsectionNode->textContent ;
                            $subsection = TRUE;
                        }
                    }
                    if ( FALSE === $isSubsection )
                    {
                        $translationArray[$sectionNode->nodeName.'/'.$translationNode->nodeName] = $translationNode->textContent ;
                    }
            
                }
            }
        }
    }
    return $translationArray;
}

require('lib/class_translations.php');

$referenceLang = 'en-GB';
$toCheckLanguages = array_diff( Translations::$registeredLanguages , array($referenceLang) ) ;
//print_r($toCheckLanguages);

$toCheckArray = array();

$file = realpath('lang/translations.xml');
if( FALSE === file_exists($file) ){
     echo 'Translation file "',$file,'" not found !';
     return ;
}
$domDoc = new DOMDocument();
if( FALSE === $domDoc->load($file)){
    echo 'Unable to load XML file "',$file,'" !';
    return;
}
$rootNode = $domDoc->documentElement;
$languageNodes = $rootNode->childNodes;
foreach ($languageNodes as $langNode)
{
    if( XML_ELEMENT_NODE === $langNode->nodeType)
    {
        $name = $langNode->nodeName;
        if( $referenceLang === $name ){
            $referenceFound = TRUE;
            $referenceArray = extract_translation_array_from_node($langNode);
            //print_r( array_keys( $referenceArray ) );
            $refNumber = count($referenceArray);
            echo 'reference count :  ',$refNumber,'<br/>';
            
        }
        if( TRUE === in_array($name,$toCheckLanguages ) ) 
        {
            $checkedFound = TRUE;
            $translations = extract_translation_array_from_node($langNode);
            $toCheckNumber = count($translations);
            echo $name,' translation count :  ',$toCheckNumber,'<br/>';
            $toCheckArray[$name] = $translations;
        }
    }
}
if( $referenceFound === FALSE ){
    echo 'Unable to find reference "',$referenceLang,'" !';
    return;
}
if( $checkedFound === FALSE ){
    echo 'Unable to find language to check "',$checkedLang,'" !';
    return;
}

foreach( $toCheckLanguages as $language)
{
    // $missingTranslations = array_diff_key($toCheckArray[$language],$referenceArray);
    //print_r($missingTranslations);
    // if( 0 !== count ($missingTranslations) )
    // {   
        // echo 'There is missing translations for ',$language,"<ul>\n";
        // foreach ($missingTranslations as $missingTranslation => $text )
        // {
            // echo '<li>',$missingTranslation,"</li>\n";
            
        // }
        // echo "</ul>\n";
    // }
    $missingTranslations = array_diff_key($referenceArray,$toCheckArray[$language]);
    //print_r($missingTranslations);
    if( 0 !== count ($missingTranslations) )
    {   
        echo 'There is missing translations for ',$language,"<ul>\n";
        foreach ($missingTranslations as $missingTranslation => $text )
        {
            echo '<li>',$missingTranslation,"</li>\n";
            
        }
        echo "</ul>\n";
    }
    
    
}


