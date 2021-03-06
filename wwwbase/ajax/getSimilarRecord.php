<?php
/* Htmlizes the definition and comment, then builds the SimilarRecord */
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$definitionId = Request::get('definitionId');
$definitionInternalRep = Request::get('definitionInternalRep');
$commentInternalRep = Request::get('commentInternalRep');
$sourceId = Request::get('sourceId');
$entryIds = Request::get('entryIds', []);

$d = Definition::get_by_id($definitionId);
$d->internalRep = AdminStringUtil::internalizeDefinition($definitionInternalRep, $sourceId);
$d->htmlRep = AdminStringUtil::htmlize($d->internalRep, $sourceId);
$d->sourceId = $sourceId;

$commentInternalRep = AdminStringUtil::internalizeDefinition($commentInternalRep, $sourceId);
$commentHtmlRep = AdminStringUtil::htmlize($commentInternalRep, $sourceId);

$sim = SimilarRecord::create($d, $entryIds);

$data = $sim->getJsonFriendly();
$data['htmlRep'] = $d->htmlRep;
$data['commentHtmlRep'] = $commentHtmlRep;

print json_encode($data);

?>
