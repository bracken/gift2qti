<?php

$QTI = simplexml_load_file('xml/assessment.xml');
$uuid = uniqid();
$offset=100;
unset($QTI->assessment);
$QTI->addChild("assessment");
$QTI->assessment->addAttribute("ident", $uuid);
$title = isset($_SESSION['title']) ? $_SESSION['title'] : 'Converted by the Gift2QTI Converter';
$QTI->assessment->addAttribute("title", $title);
$section = $QTI->assessment->addChild('section');
$section->addAttribute("ident", "root_section");

foreach($questions as $question) {
    $item = $section->addChild("item");
    $item->addAttribute("title",$question->name);
    $item->addAttribute("ident",$uuid.'_'.$offset++);
    $itemmetadata = $item->addChild("itemmetadata");
    $qtimetadata = $itemmetadata->addChild("qtimetadata");
    $qtimetadatafield = $qtimetadata->addChild("qtimetadatafield");
    $qtimetadatafield->addChild("fieldlabel", "question_type");
    $qtimetadatafield->addChild("fieldentry", $question->type);
    $qtimetadatafield = $qtimetadata->addChild("qtimetadatafield");
    $qtimetadatafield->addChild("fieldlabel", "points_possible");
    $qtimetadatafield->addChild("fieldentry", "1");
    $qtimetadatafield = $qtimetadata->addChild("qtimetadatafield");
    $qtimetadatafield->addChild("fieldlabel", "assessment_question_identifierref");
    $qtimetadatafield->addChild("fieldentry", $uuid.'_'.$offset++);

    $presentation = $item->addChild("presentation");
    $material = $presentation->addChild("material");
    $mattext = $material->addChild("mattext", $question->question);
    $mattext->addAttribute("texttype", "text/plain");

    if ( $question->type == 'true_false_question' ) {

        $response_lid = $presentation->addChild('response_lid');
        $response_lid->addAttribute("ident", "response1");
        $response_lid->addAttribute("rcardinality", "Single");
        $render_choice = $response_lid->addChild('render_choice');
        $trueval = $offset++;
        $response_label = $render_choice->addChild('response_label');
        $response_label->addAttribute('ident', $trueval);
        $material = $response_label->addChild("material");
        $mattext = $material->addChild("mattext", "True");
        $mattext->addAttribute("texttype", "text/plain");
        $falseval = $offset++;
        $response_label = $render_choice->addChild('response_label');
        $response_label->addAttribute('ident', $falseval);
        $material = $response_label->addChild("material");
        $mattext = $material->addChild("mattext", "False");
        $mattext->addAttribute("texttype", "text/plain");

        $resprocessing = $item->addChild("resprocessing");
        $outcomes = $resprocessing->addChild("outcomes");
        $decvar = $outcomes->addChild("decvar");
        $decvar->addAttribute("maxvalue", "100");
        $decvar->addAttribute("minvalue", "0");
        $decvar->addAttribute("varname", "SCORE");
        $decvar->addAttribute("vartype", "Decimal");
        $respcondition = $resprocessing->addChild("respcondition");
        $respcondition->addAttribute("continue", "No");
        $conditionvar = $respcondition->addChild("conditionvar");
        $ans = strtolower($question->answer);
        $val = strpos($ans,"t") === 0 ? $trueval : $falseval;
        $varequal = $conditionvar->addChild("varequal",$val);
        $varequal->addAttribute("respident", "response1");
        $setvar = $respcondition->addChild("setvar", 100);
        $setvar->addAttribute("action", "Set");
        $setvar->addAttribute("varname", "SCORE");
    }

    if ( $question->type == 'multiple_choice_question' ) {

        $response_lid = $presentation->addChild('response_lid');
        $response_lid->addAttribute("ident", "response1");
        $response_lid->addAttribute("rcardinality", "Single");
        $render_choice = $response_lid->addChild('render_choice');

        $correct = null;
        foreach ( $question->parsed_answer as $parsed_answer )  {
            $val = $offset++;
            if ( $parsed_answer[0] === true ) $correct = $val;
            $response_label = $render_choice->addChild('response_label');
            $response_label->addAttribute('ident', $val);
            $material = $response_label->addChild("material");
            $mattext = $material->addChild("mattext", $parsed_answer[1]);
            $mattext->addAttribute("texttype", "text/plain");
        }

        $resprocessing = $item->addChild("resprocessing");
        $outcomes = $resprocessing->addChild("outcomes");
        $decvar = $outcomes->addChild("decvar");
        $decvar->addAttribute("maxvalue", "100");
        $decvar->addAttribute("minvalue", "0");
        $decvar->addAttribute("varname", "SCORE");
        $decvar->addAttribute("vartype", "Decimal");
        $respcondition = $resprocessing->addChild("respcondition");
        $respcondition->addAttribute("continue", "No");
        $conditionvar = $respcondition->addChild("conditionvar");
        $varequal = $conditionvar->addChild("varequal",$correct);
        $varequal->addAttribute("respident", "response1");
        $setvar = $respcondition->addChild("setvar", 100);
        $setvar->addAttribute("action", "Set");
        $setvar->addAttribute("varname", "SCORE");
    }

    if ( $question->type == 'essay_question' ) {

        $response_str = $presentation->addChild('response_str');
        $response_str->addAttribute("ident", "response1");
        $response_str->addAttribute("rcardinality", "Single");
        $render_fib = $response_str->addChild('render_fib');
        $response_label = $render_fib->addChild('response_label');
        $response_label->addAttribute('ident', $offset++);
        $response_label->addAttribute('rshuffle', "No");

        $resprocessing = $item->addChild("resprocessing");
        $outcomes = $resprocessing->addChild("outcomes");
        $decvar = $outcomes->addChild("decvar");
        $decvar->addAttribute("maxvalue", "100");
        $decvar->addAttribute("minvalue", "0");
        $decvar->addAttribute("varname", "SCORE");
        $decvar->addAttribute("vartype", "Decimal");
        $respcondition = $resprocessing->addChild("respcondition");
        $respcondition->addAttribute("continue", "No");
        $conditionvar = $respcondition->addChild("conditionvar");
        $other = $conditionvar->addChild("other");

    }

}

$DOM = new DOMDocument('1.0');
$DOM->preserveWhiteSpace = false;
$DOM->formatOutput = true;
$DOM->loadXML($QTI->asXML());
echo "\nValidating (may take a few seconds)...\n";
libxml_use_internal_errors(true);
if ( ! $DOM->schemaValidate('xml/ims_qtiasiv1p2p1.xsd') ) {
    echo "\nWarning: Quiz XML Not Valid\n";
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        echo "Error:", libxml_display_error($error), "\n";
    }
    libxml_clear_errors();
} else { 
    echo "\nQuiz XML validated\n";
}

