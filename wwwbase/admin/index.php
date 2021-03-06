<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_ANY);
util_assertNotMirror();

$reports = array(
  array('text' => 'Definiții nemoderate',
        'url' => 'admin/viewPendingDefinitions',
        'count' => Model::factory('Definition')->where('status', Definition::ST_PENDING)->count(),
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Definiții cu greșeli de tipar',
        'url' => 'admin/viewTypos',
        'count' => Model::factory('Typo')->select('definitionId')->distinct()->count(),
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Definiții cu abrevieri ambigue',
        'url' => 'admin/randomAbbrevReview',
        'count' => Definition::countAmbiguousAbbrevs(),
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Definiții OCR neverificate',
        'url' => 'admin/definitionEdit',
        'count' => sprintf("%d (disponibile: %d)",
                           Model::factory('OCR')->where('status', 'raw')->count(),
                           OCR::countAvailable(session_getUserId())),
        'privilege' => PRIV_EDIT
  ),
  // this takes about 300 ms
  array('text' => 'Definiții neasociate cu nicio intrare',
        'url' => 'admin/viewUnassociatedDefinitions',
        'count' => Definition::countUnassociated(),
        'privilege' => PRIV_EDIT
  ),
  // this takes about 500 ms (even though the query is similar to the one for unassociated definitions)
  array('text' => 'Intrări neasociate cu nicio definiție',
        'url' => 'admin/viewUnassociatedEntries',
        'count' => Entry::countUnassociated(),
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Lexeme cu comentarii',
        'url' => 'admin/viewLexemsWithComments',
        'count' => Model::factory('Lexem')->where_not_null('comment')->count(),
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Lexeme fără accent',
        'url' => 'admin/viewLexemsWithoutAccents',
        'count' => Model::factory('Lexem')->where('consistentAccent', 0)->count(),
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Lexeme ambigue',
        'url' => 'admin/viewAmbiguousLexems',
        'count' => 'numărătoare dezactivată',
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Lexeme fără paradigme',
        'url' => 'admin/viewTemporaryLexems',
        'count' => Model::factory('Lexem')->where('modelType', 'T')->count(),
        'privilege' => PRIV_EDIT
  ),
  array('text' => 'Mențiuni despre arbori nestructurați',
        'url' => 'admin/viewTreeMentions',
        'count' => Model::factory('Mention')->where('objectType', Mention::TYPE_TREE)->count(),
        'privilege' => PRIV_EDIT
  ),
);

SmartyWrap::assign('structStatusNames', Entry::$STRUCT_STATUS_NAMES);
SmartyWrap::assign('structurists', User::getStructurists());
SmartyWrap::assign('reports', $reports);
SmartyWrap::addCss('admin');
SmartyWrap::addJs('select2Dev', 'modelDropdown');
SmartyWrap::display('admin/index.tpl');
?>
