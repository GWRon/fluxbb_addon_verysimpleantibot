<?php
// Language string for addon Very Simple AntiBot

//all of them are defined as global, because this allows to require
//this file within a function without the function needing to know
//what variables are defined here

global $lang_addon_vsab;
$lang_addon_vsab = array(
  'title'      => 'Bist Du ein Mensch oder ein Roboter ?',
  'question'   => 'Bitte beantworte folgende Aufgabe:<br /><b>%s</b>',
  'info'       => 'Verifizierung, dass diese Aktion durch eine reale Person vorgenommen wird und nicht von einem Programm.',
  'test failed'  => 'Die Verifizierung ist gescheitert. Vielleicht bist Du ein BotScript...',
);


global $lang_addon_admin_vsab;
$lang_addon_admin_vsab = array(
  'AP title' => 'Das VSAB Very Simple AntiBot-Plugin konfigurieren',
  'AP description' => 'Einstellungen entsprechend Deiner Wünsche anpassen.',
  'Enabled' => 'Aktiviert',
  'Enabled for postings' => 'Für Beiträge aktiviert',
  'Yes for all' => 'Ja für Alle',
  'Yes for guests' => 'Ja für Gäste',
  'Member minimum post count' => 'Mindestbeitragszähler für Mitglieder',
  'minimum post count hint 1' => 'Freilassen oder 0-setzen um Mitglieder unabhängig ihres Beitragszählers zu überprüfen',
  'minimum post count hint 2' => '<strong>Für Beiträge aktiviert</strong> muss auf "Ja für Alle" gesetzt sein, um diesen Modus zu aktivieren',
  'Encryption salt' => 'Verschlüsselungs-Code',
  'Leave empty to autogenerate a new salt' => 'Freilassen um einen neuen Code generieren zu lassen',
);


global $addon_vsab_questions;
$addon_vsab_questions = array(
  'Gib die fehlenden Buchstaben an: ex**ple.com'	=> "am",
  'Gib die fehlenden Buchstaben an: exam**e.com'	=> "pl",
  'Wie lautet das vierte Wort in dieser Frage?'		=> "vierte",
  'Wie lautet das sechste Wort in dieser Frage?'	=> "in",
  'Gib die fehlenden Buchstaben an: Bist du ein Mensch oder ein Comp**er?'	=> "ut",
  'Gib die fehlenden Buchstaben an: Bist du ein Mensch oder ein C**puter??'	=> "om",
);

?>