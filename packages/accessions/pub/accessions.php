<?php
/**
 * Output file for browsing by accession
 *
 * @package Archon
 * @author Robert Andrews, Mamta Singh, Paul Sorensen
 */
isset($_ARCHON) or die();

$in_Char = isset($_REQUEST['char']) ? $_REQUEST['char'] : NULL;
$in_Book = isset($_REQUEST['books']) ? true : false;
$in_Browse = isset($_REQUEST['browse']) ? true : false;

if(!$in_Book)
{
	$objAccessionsTitlePhrase = Phrase::getPhrase('accessions_title', PACKAGE_ACCESSIONS, 0, PHRASETYPE_PUBLIC);
	$strAccessionsTitle = $objAccessionsTitlePhrase ? $objAccessionsTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse By Accession Title';

	$_ARCHON->PublicInterface->Title = $strAccessionsTitle;
	$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}");
	$_ARCHON->PublicInterface->Title .= ' | ' . $_ARCHON->Repository->Name;
}
else
{
	$objBooksTitlePhrase = Phrase::getPhrase('collections_booktitle', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
	$strBooksTitle = $objBooksTitlePhrase ? $objBooksTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse By Book Title';


	$_ARCHON->PublicInterface->Title = $strBooksTitle;
	$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}&amp;books");
}

if($in_Char)
{
	$vars = accessions_listAccessionsForChar($in_Char, $in_Book);
}
elseif($in_Browse)
{
	$in_Page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

	$vars = accessions_listAllAccessions($in_Page, $in_Book);
}
else
{
	$vars = accessions_main($in_Book);
}

require_once("header.inc.php");
echo($_ARCHON->PublicInterface->executeTemplate('collections', 'CollectionsNav', $vars));
require_once("footer.inc.php");


function accessions_main($ShowBooks)
{
	global $_ARCHON;

	$objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
	$strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';


	$vars['strPageTitle'] = strip_tags($_ARCHON->PublicInterface->Title);
	$vars['strSubTitleClasses'] = 'bold center';
	$vars['strBackgroundID'] = '';
	$content = '<div class="center">';

	/*
	 * TODO
	 * This should never execute.
	 */
	if($ShowBooks)
	{
		$objBrowseBooksBeginningPhrase = Phrase::getPhrase('collections_browsebooksbeginning', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
		$strBrowseBooksBeginning = $objBrowseBooksBeginningPhrase ? $objBrowseBooksBeginningPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Books Beginning With';

		$arrCollectionCount = $_ARCHON->countBooks(true);
		$vars['strSubTitle'] = $strBrowseBooksBeginning.":";
	}
	else
	{
		$objBrowseHoldingsBeginningPhrase = Phrase::getPhrase('collections_browseholdingsbeginning', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
		$strBrowseHoldingsBeginning = $objBrowseHoldingsBeginningPhrase ? $objBrowseHoldingsBeginningPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Holdings Beginning With';

		$arrAccessionCount = $_ARCHON->countAccessions(true, false, $_SESSION['Archon_RepositoryID']);
		$vars['strSubTitle'] = $strBrowseHoldingsBeginning.":";
	}

	if(!empty($arrAccessionCount['#']))
	{
		$href = "?p={$_REQUEST['p']}&amp;char=" . urlencode('#');
		if($ShowBooks)
		{
			$href .= "&amp;books";
		}
		$content .= "<a href='$href'>-#-</a>" . INDENT;
	}
	else
	{
		$content .= "-#-" . INDENT;
	}

	for($i = 65; $i < 91; $i++)
	{
		$char = chr($i);

		if(!empty($arrAccessionCount[encoding_strtolower($char)]))
		{
			$href = "?p={$_REQUEST['p']}&amp;char=$char";
			/*
			 * TODO
			 * Should not execute.
			 */
			if($ShowBooks)
			{
				$href .= "&amp;books";
			}
			$content .= "<a href='$href'>-$char-</a>" . INDENT;
		}
		else
		{
			$content .= "-$char-" . INDENT;
		}

		if($char == 'M')
		{
			$content .= "<br /><br />\n";
		}


	}
	$bookurl = ($ShowBooks) ? '&amp;books' : '';
	$content .= "<br /><br /><a href='?p={$_REQUEST['p']}&amp;browse{$bookurl}'>{$strViewAll}</a>";

	/*
	 * TODO
	 * Should not execute.
	 */
	if($ShowBooks)
	{
		$objBrowseAllCollectionsPhrase = Phrase::getPhrase('collections_browseallcollections', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
		$strBrowseAllCollections = $objBrowseAllCollectionsPhrase ? $objBrowseAllCollectionsPhrase->getPhraseValue(ENCODE_HTML) : 'Browse All Collections';

		$content .= "<a href='?p={$_REQUEST['p']}'>{$strBrowseAllCollections}</a>";
	}
	else
	{
		if($_ARCHON->countBooks())
		{
			$objBrowseBooksCollectionPhrase = Phrase::getPhrase('collections_browsebookscollection', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
			$strBrowseBooksCollection = $objBrowseBooksCollectionPhrase ? $objBrowseBooksCollectionPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Books Collection';
			$content .= "<a href='?p={$_REQUEST['p']}&amp;books'>{$strBrowseBooksCollection}</a>";
		}
	}

	if(CONFIG_COLLECTIONS_ENABLE_PUBLIC_EAD_LIST)
	{
		$objViewEADListPhrase = Phrase::getPhrase('collections_vieweadlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
		$strViewEADList = $objViewEADListPhrase ? $objViewEADListPhrase->getPhraseValue(ENCODE_HTML) : 'List links to EAD files';

		$content .=  "<br /><br /><a rel='external' href='?p=collections/eadlist'>{$strViewEADList}</a>";
	}

	$content .= '</div>';

	$vars['content'] = $content;
	return $vars;
}


function accessions_listAllAccessions($Page, $ShowBooks)
{
	global $_ARCHON;

	$RepositoryID = $_SESSION['Archon_RepositoryID'] ? $_SESSION['Archon_RepositoryID'] : 0;

	/*
	 * TODO
	 * This block will always be executed.
	 */
	if(!$ShowBooks)
	{
		$arrAccessions = $_ARCHON->searchAccessions($_REQUEST['q'], SEARCH_ACCESSIONS, 0, 0, 0, 0, CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page-1)*CONFIG_CORE_PAGINATION_LIMIT); //$_ARCHON->searchCollections($_REQUEST['q'], SEARCH_COLLECTIONS, 0, 0, 0, $RepositoryID, 0, 0, NULL, NULL, NULL, CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page-1)*CONFIG_CORE_PAGINATION_LIMIT);
		$bookurl = '';
		$template = 'AccessionList';
		$objectName = 'objAccession';

	}
	/*
	 * TODO
	 * This block should never be executed. Need to clean this up
	 * to reflect this.
	 */
	else
	{
		$arrAccessions = $_ARCHON->searchBooks($_REQUEST['q'], 0, 0, 0, CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page-1)*CONFIG_CORE_PAGINATION_LIMIT);
		$bookurl = '&amp;books';
		$template = 'BookList';
		$objectName = 'objBook';


	}
	if(count($arrAccessions) > CONFIG_CORE_PAGINATION_LIMIT)
	{
		$morePages = true;
		array_pop($arrAccessions);
	}

	// Set up a URL for any prev/next buttons or in case $Page
	// is too high
	$paginationURL = 'index.php?p=' . $_REQUEST['p'].'&browse'.$bookurl;

	if(empty($arrAccessions) && $Page != 1)
	{
		header("Location: $paginationURL");
	}



	$objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
	$strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

	$_ARCHON->PublicInterface->addNavigation($strViewAll);


	if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template])
	{
		$_ARCHON->declareError("Could not list Accessions: AccessionList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
	}

	$vars['strPageTitle'] = strip_tags($_ARCHON->PublicInterface->Title);
	$vars['strSubTitleClasses'] = 'listitemhead bold';
	$vars['strBackgroundID'] = ' id="listitemwrapper"';
	$content = '';

	if(!$_ARCHON->Error)
	{
		if(!empty($arrAccessions))
		{

			$vars['strSubTitle'] = $strViewAll;

			foreach($arrAccessions as ${$objectName})
			{
				ob_start();
				eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template]);
				$content .= ob_get_contents();
				ob_end_clean();
			}

		}

		if($Page > 1 || $morePages)
		{
			$pages = '<div class="paginationnav">';

			if($Page > 1)
			{
				$prevPage = $Page - 1;
				$prevURL = encode($paginationURL . "&page=$prevPage", ENCODE_HTML);
				$pages .= "<span class='paginationprevlink'><a href='$prevURL'>Prev</a></span>";
			}
			if($morePages)
			{
				$nextPage = $Page + 1;
				$nextURL = encode($paginationURL . "&page=$nextPage", ENCODE_HTML);
				$pages .= "<span class='paginationnextlink'><a href='$nextURL'>Next</a></span>";
			}

			$pages .= '</div>';
		}
	}

	$vars['content'] = $content;
	if(isset($pages)){ $vars['pages'] = $pages; }
	return $vars;
}


function accessions_listAccessionsForChar($Char, $ShowBooks)
{

	global $_ARCHON;

	$objBeginningWithPhrase = Phrase::getPhrase('collections_beginningwith', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
	$strBeginningWith = $objBeginningWithPhrase ? $objBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Beginning With "$1"';


	$_ARCHON->PublicInterface->addNavigation(str_replace('$1', encoding_strtoupper($Char), $strBeginningWith), "?p={$_REQUEST['p']}&amp;char=$Char");


	/*
	 * TODO
	 * ShowBooks should always be false. Need to update code to reflect this.
	 */
	$template = (!$ShowBooks) ? 'AccessionList' : 'BookList';

	if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template])
	{
		$_ARCHON->declareError("Could not list Accessions: AccessionList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
	}


	$vars['strPageTitle'] = strip_tags($_ARCHON->PublicInterface->Title);
	$vars['strSubTitleClasses'] = 'listitemhead bold';
	$vars['strBackgroundID'] = ' id="listitemwrapper"';
	$content = '';

	if(!$_ARCHON->Error)
	{
		if(!$ShowBooks)
		{
			$arrAccessions = $_ARCHON->getAccessionsForChar($Char, $_SESSION['Archon_RepositoryID'], array('ID', 'Title', 'InclusiveDates'));
			$objectName = 'objAccession';
		}
		/*
		 * TODO
		 * This code should never execute. Update to reflect this.
		 */
		else
		{
			$arrAccessions = $_ARCHON->getBooksForChar($Char);
			$objectName = 'objBook';
		}

		if(!empty($arrAccessions))
		{

			if(!$ShowBooks)
			{
				$objHoldingsBeginningWithPhrase = Phrase::getPhrase('collections_holdingsbeginningwithlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
				$strHoldingsBeginningWith = $objHoldingsBeginningWithPhrase ? $objHoldingsBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Holdings Beginning With "$1"';
				$strBeginningWithHeader = str_replace('$1', encoding_strtoupper($Char), $strHoldingsBeginningWith);
			}
			else
			{
				$objBooksBeginningWithPhrase = Phrase::getPhrase('collections_booksbeginningwithlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
				$strBooksBeginningWith = $objBooksBeginningWithPhrase ? $objBooksBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Books Beginning With "$1"';
				$strBeginningWithHeader = str_replace('$1', encoding_strtoupper($Char), $strBooksBeginningWith);
			}

			$vars['strSubTitle'] = $strBeginningWithHeader;

			foreach($arrAccessions as ${$objectName})
			{
				ob_start();
				eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template]);
				$content .= ob_get_contents();
				ob_end_clean();
			}
		}
	}

	$vars['content'] = $content;
	return $vars;
}

?>
