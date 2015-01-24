<?php
// Language string for addon Very Simple AntiBot

//all of them are defined as global, because this allows to require
//this file within a function without the function needing to know
//what variables are defined here

global $lang_addon_vsab;
$lang_addon_vsab = array(
  'title'       => 'Are you human or robot?',
  'question'    => 'Please answer the following question:<br /><b>%s</b>',
  'info'        => 'Checking if this is requested by a real person and not an automated program.',
  'test failed' => 'You answered incorrectly to the "Human or Robot" question, or you are a Bot!',
);


global $lang_addon_admin_vsab;
$lang_addon_admin_vsab = array(
  'AP title' => 'Configure VSAB Very Simple AntiBot plugin',
  'AP description' => 'Adjust the settings according to your needs.',
  'Enabled' => 'Enabled',
  'Enabled for postings' => 'Enabled for postings',
);


global $addon_vsab_questions;
$addon_vsab_questions = array(
  'Fill in the missing letters: g**ezworld.de' => "am",
  'Fill in the missing letters: ga**zworld.de' => "me",
  'Fill in the missing letters: gamez**rld.de' => "wo",
  'Fill in the missing letters: gamezw**ld.de' => "rl",
  'What is the fourth word of this question?' => "fourth",
  'What is the fifth word of this question?' => "word",
  'Fill in the missing letters: Are you a human or a comp**er??'    => "ut",
  'Fill in the missing letters: Are you a human or a c**puter??'    => "om"
);

?>