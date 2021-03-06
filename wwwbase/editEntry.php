<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT | PRIV_STRUCT);

$id = Request::get('id');
$saveButton = Request::has('saveButton');
$mergeButton = Request::has('mergeButton');
$cloneButton = Request::has('cloneButton');
$createTree = Request::has('createTree');
$delete = Request::has('delete');
$dissociateDefinitionId = Request::get('dissociateDefinitionId');

if ($id) {
  $e = Entry::get_by_id($id);
  if (!$e) {
    FlashMessage::add('Intrarea nu există.');
    util_redirect(util_getWwwRoot());
  }
  // Keep a copy so we can test whether certain fields have changed
  $original = Entry::get_by_id($id);
} else {
  $e = Model::factory('Entry')->create();
  $original = Model::factory('Entry')->create();
}

if ($dissociateDefinitionId) {
  EntryDefinition::dissociate($e->id, $dissociateDefinitionId);
  Log::info("Dissociated lexem {$e->id} ({$e->description}) from definition {$dissociateDefinitionId}");
  util_redirect("?id={$e->id}");
}

if ($mergeButton) {
  $mergeEntryId = Request::get('mergeEntryId');
  $other = Entry::get_by_id($mergeEntryId);

  if (!$other) {
    FlashMessage::add('Intrarea selectată nu există.');
    util_redirect("?id={$e->id}");
  } else if (!$e->id) {
    FlashMessage::add('Nu puteți face unificarea la momentul creării.');
    util_redirect(util_getWwwRoot());
  } else if ($other->id == $e->id) {
    FlashMessage::add('Nu puteți unifica intrarea cu ea însăși (serios!).');
    util_redirect("?id={$e->id}");
  }

  $e->mergeInto($other->id);

  FlashMessage::add('Am unificat intrările.', 'success');
  util_redirect("?id={$other->id}");
}

if ($cloneButton) {
  $cloneDefinitions = Request::has('cloneDefinitions');
  $cloneTrees = Request::has('cloneTrees');

  $newe = $e->_clone($cloneDefinitions, $cloneTrees);
  Log::info("Cloned entry {$e->id} ({$e->description}), new id {$newe->id}");
  FlashMessage::add('Am clonat intrarea.', 'success');
  util_redirect("?id={$newe->id}");
}

if ($createTree) {
  if (!$id) {
    FlashMessage::add('Nu puteți crea un arbore de sensuri înainte să salvați intrarea.');
    util_redirect(util_getWwwRoot());
  }
  $t = Tree::createAndSave($e->description . " (NOU)");
  TreeEntry::associate($t->id, $e->id);
  FlashMessage::add("Am creat un arbore de sensuri pentru {$e->description}.", 'success');
  util_redirect("editTree.php?id={$t->id}");
}

if ($delete) {
  $e->delete();
  FlashMessage::add('Am șters intrarea.', 'success');
  util_redirect(util_getWwwRoot());
}

if ($saveButton) {
  $e->description = Request::get('description');
  $e->structStatus = Request::get('structStatus');
  $e->structuristId = Request::get('structuristId');
  $lexemIds = Request::get('lexemIds');
  $treeIds = Request::get('treeIds');

  $errors = $e->validate($original);
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {
    // Possibly overwrite the structuristId according to the structStatus change
    if (($original->structStatus == Entry::STRUCT_STATUS_NEW) &&
        ($e->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
      $e->structuristId = session_getUserId();
    }

    $e->save();

    // dissociate the entry from the old lexems
    foreach ($e->getLexems() as $l) {
      $l->entryId = null;
      $l->save();
    }

    // associate the entry with the new lexems
    foreach ($lexemIds as $id) {
      $l = Lexem::get_by_id($id);
      $l->entryId = $e->id;
      $l->save();
    }

    // dissociate the entry from the old trees
    TreeEntry::delete_all_by_entryId($e->id);

    // associate the entry with the new trees
    foreach ($treeIds as $tid) {
      TreeEntry::associate($tid, $e->id);
    }

    FlashMessage::add('Am salvat intrarea.', 'success');
    util_redirect("?id={$e->id}");
  }
} else {
  // Viewing the page, not saving
  $lexemIds = $e->getLexemIds();
  $treeIds = $e->getTreeIds();
  $e->loadMeanings();
  RecentLink::add("Intrare: {$e->description} (ID={$e->id})");
}

// Load the distinct model types for the entry's lexems
$modelTypes = [];
foreach ($lexemIds as $lexemId) {
  $l = Lexem::get_by_id($lexemId);
  $modelTypes[] = $l->modelType;
}
$modelTypes = array_unique($modelTypes);

$definitions = Definition::loadByEntryId($e->id);
foreach ($definitions as $def) {
  $def->internalRepAbbrev = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
  $def->htmlRepAbbrev = AdminStringUtil::htmlize($def->internalRepAbbrev, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($definitions);

$ss = $e->structStatus;
$oss = $original->structStatus; // syntactic sugar

$canEdit = [
  'structStatus' => in_array($oss,
                             [ Entry::STRUCT_STATUS_NEW, Entry::STRUCT_STATUS_IN_PROGRESS ])
  || util_isModerator(PRIV_EDIT),
  'structuristId' => util_isModerator(PRIV_ADMIN),
];

$homonymIds = [];
foreach ($e->getLexems() as $l) {
  $homonymLexems = Model::factory('Lexem')
                 ->select('entryId')
                 ->where('formNoAccent', $l->formNoAccent)
                 ->where_not_equal('id', $l->id)
                 ->find_array();
  foreach ($homonymLexems as $h) {
    $homonymIds[$h['entryId']] = true;
  }
}
unset($homonymIds[$e->id]);

if (count($homonymIds)) {
  $homonyms = Model::factory('Entry')
            ->where_in('id', array_keys($homonymIds))
            ->find_many();
} else {
  $homonyms = [];
}

SmartyWrap::assign('e', $e);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('treeIds', $treeIds);
SmartyWrap::assign('modelTypes', $modelTypes);
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::assign('homonyms', $homonyms);
SmartyWrap::assign('structStatusNames', Entry::$STRUCT_STATUS_NAMES);
SmartyWrap::assign('structurists', User::getStructurists($e->structuristId));
SmartyWrap::addCss('meaningTree', 'admin');
SmartyWrap::addJs('select2Dev', 'meaningTree', 'textComplete');
SmartyWrap::display('editEntry.tpl');

?>
