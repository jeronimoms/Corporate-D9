<?php

namespace Drupal\oshwiki_import\Plugin\migrate_plus\data_fetcher;

use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\File as FileFetcher;
use Drupal\oshwiki_import\OshWikiNodeEntity;

/**
 * Obtain JSON data for migration.
 *
 * @DataFetcher(
 *   id = "oie_file_fetcher",
 *   title = @Translation("OSHWiki data fetcher")
 * )
 */

class OshWikiProcessor extends FileFetcher
{

  /**
   * {@inheritdoc}
   */
  public function getResponseContent($url) {
    $this->getAllPagesMetadata($url);
    $response = $this->getResponse($url);
    return $response;
  }

  private function getAllPagesMetadata($resultsFilePath)
  {
    $nodeCounter = 0;
    $resultsOffset = 0;
    $resultsLimit = 200;
    $hasResults = true;
//     $resultsFilePath = "C:/proj/Corporate-D9/web/sites/default/files/oshwikiMigration/fullList.json";
// $logsPath = "/var/www/html/web/sites/default/files/oshwikiMigration/log.txt";

    file_put_contents($resultsFilePath, '[');
// file_put_contents($logsPath, "");
    while($hasResults)
    {
      //$oshwikiPages = [];
      /*
       * TO EXTRACT METADATA
       */
      //$osha_wiki_hostname = \Drupal::state()->get('osha_wiki_hostname', 'https://oshwiki.eu');

      $osha_wiki_hostname = \Drupal::configFactory()->getEditable('oshwiki_import.settings')->get('oshwiki_import_api');
      $oshwikiPrintoutsUrl = "$osha_wiki_hostname/index.php?title=Special%3AAsk&limit=$resultsLimit&offset=$resultsOffset&q=%5B%5B%3A%2B%5D%5D&po=%3FModification+date%0D%0A%3FCategory%0D%0A%3FMaster+page%0D%0A%3FModification+date%0D%0A%3FLanguage+code%0D%0A%3FNP%0D%0A%3FOP%0D%0A&eq=yes&p%5Bformat%5D=json&p%5Blink%5D=subject&p%5Bsort%5D=Modification+date&p%5Border%5D%5Bdesc%5D=1&p%5Bheaders%5D=show&p%5Bmainlabel%5D=&p%5Bintro%5D=&p%5Boutro%5D=&p%5Bsearchlabel%5D=&p%5Bdefault%5D=&p%5Bsyntax%5D=standard&eq=yes";
// $oshwikiPrintoutsUrl = "$osha_wiki_hostname/index.php?title=Special%3AAsk&limit=$resultsLimit&offset=$resultsOffset&q=%5B%5BOP%3A%3AProperty%3AOSHA+54841D%5D%5D&po=%3FModification+date%0D%0A%3FCategory%0D%0A%3FMaster+page%0D%0A%3FModification+date%0D%0A%3FLanguage+code%0D%0A%3FNP%0D%0A%3FOP%0D%0A&eq=yes&p%5Bformat%5D=json&p%5Blink%5D=subject&p%5Bsort%5D=Modification+date&p%5Border%5D%5Bdesc%5D=1&p%5Bheaders%5D=show&p%5Bmainlabel%5D=&p%5Bintro%5D=&p%5Boutro%5D=&p%5Bsearchlabel%5D=&p%5Bdefault%5D=&p%5Bsyntax%5D=standard&eq=yes";
// file_put_contents($logsPath, $oshwikiPrintoutsUrl.PHP_EOL, FILE_APPEND);
      $fullResponse =  file_get_contents($oshwikiPrintoutsUrl, false, null);
// file_put_contents($logsPath, print_r("res".$nodeCounter.PHP_EOL, true), FILE_APPEND);
// file_put_contents($logsPath, print_r($oshwikiPrintoutsUrl.PHP_EOL, true), FILE_APPEND);
// file_put_contents($logsPath, print_r($fullResponse.PHP_EOL, true), FILE_APPEND);
      if(!isset($fullResponse) || is_null($fullResponse) || empty($fullResponse))
      {
        $hasResults = false;
        break;
      }
      $oshwikiNodes = json_decode($fullResponse);
      foreach($oshwikiNodes->results as $oldWikiPage)
      {
        $newWikiPage = new OshWikiNodeEntity;
        $pagePrintouts = $oldWikiPage->printouts;
        $newWikiPage->title = $oldWikiPage->fulltext;
        $newWikiPage->oshwikiURL = $oldWikiPage->fullurl;

        foreach($pagePrintouts->{'Modification date'} as $modDate)
        {
          if($newWikiPage->modificationDate < $modDate->timestamp)
          {
            $newWikiPage->modificationDate = $modDate->timestamp;
          }
        }
        foreach($pagePrintouts->Category as $pageCategories)
        {
          $newWikiPage->category[] = str_replace('Category:', '', $pageCategories->fulltext);
        }

        foreach($pagePrintouts->{'Language code'} as $pageLanguages)
        {
          $newWikiPage->language[] = $pageLanguages;
        }

        foreach($pagePrintouts->NP as $pageNP)
        {
          $newWikiPage->np[] = str_replace(' ', '.', str_replace('Property:NACE ', '', $pageNP->fulltext));
        }

        foreach($pagePrintouts->OP as $pageOP)
        {
          $newWikiPage->op[] = str_replace('Property:OSHA ', '', $pageOP->fulltext);
        }
        //TODO Array with all
        $newWikiPage->tags = [];

        $this->getSinglePageContent($newWikiPage);

        $finalObject = json_encode($newWikiPage);
        if(isset($finalObject) && !is_null($finalObject) && !empty($finalObject))
        {
          if($nodeCounter != 0)
          {
            file_put_contents($resultsFilePath, ',', FILE_APPEND);
    //        file_put_contents($logsPath, print_r("Title: ".$oldWikiPage->fulltext.": Title".PHP_EOL, true), FILE_APPEND);
          }
          file_put_contents($resultsFilePath, $finalObject, FILE_APPEND);
        }

        $nodeCounter++;
      }
      $resultsOffset += $resultsLimit;
    }
    file_put_contents($resultsFilePath, ']', FILE_APPEND);
  }

  private function getSinglePageContent(&$newWikiPagePar)
  {
    /*
     * TO EXTRACT CONTENT
     */
    $pageTitle = urlencode($newWikiPagePar->title);
    //$oswikiContentsUrl = "https://oshwiki.eu/api.php?action=query&generator=recentchanges&grclimit=1&grcend=2021-03-19T08:02:30Z&grcdir=older&prop=revisions&rvprop=content|comment|tags&format=json";
    //$osha_wiki_hostname = \Drupal::state()->get('osha_wiki_hostname', 'https://oshwiki.eu');

    //$osha_wiki_hostname = $config->get('oshwiki_import_api');

    $osha_wiki_hostname = \Drupal::configFactory()->getEditable('oshwiki_import.settings')->get('oshwiki_import_api');
    $oswikiContentsUrl = "$osha_wiki_hostname/api.php?action=parse&page=$pageTitle&prop=text&formatversion=2&format=json&prop=wikitext";
    $fullResponseContent =  file_get_contents($oswikiContentsUrl, false, null);
    $oshwikiNodesContent = json_decode($fullResponseContent);
    $newWikiPagePar->content = $oshwikiNodesContent->parse->wikitext;
    $newWikiPagePar->summary = $this->getWikiSummaryFromBody($newWikiPagePar->content);
//     $newWikiPagePar->summary =
//       $this->getWikiSummaryFromBody($newWikiPagePar->content).
//       PHP_EOL.$this->osha_wiki_block_content_template($newWikiPagePar->oshwikiURL);
    $newWikiPagePar->content = $this->cleanString($newWikiPagePar->content);
  }

  private function getWikiSummaryFromBody($body, $convert_category = TRUE)
  {
//     $osha_wiki_hostname = variable_get('osha_wiki_hostname', 'https://oshwiki.eu');
//    $osha_wiki_hostname = 'https://oshwiki.eu';
    //$osha_wiki_hostname = \Drupal::state()->get('osha_wiki_hostname', 'https://oshwiki.eu');

    //$osha_wiki_hostname = $config->get('oshwiki_import_api');
    $osha_wiki_hostname = \Drupal::configFactory()->getEditable('oshwiki_import.settings')->get('oshwiki_import_api');
    $osh_wiki_prefix = $osha_wiki_hostname . '/wiki/';

    $patterns = array();
    $replacements = array();
    // Remove {{...}} [[Category]] <categorytree>..</categorytree>
    $patterns[0] = '/({{[^}]*}}\s*)*(\[\[[^\]]*\]\]\s*)*(<categorytree.*<\/categorytree>\s*)*/s';
    $replacements[0] = '';
    // Use limit because there are other [[wiki links]] in the text.
    $wiki_text = preg_replace($patterns, $replacements, $body, 1);

    // Removes Internal {{LangTempl...}}, {{Template...}}, {{Metadata...}} etc...
    $patterns[0] = '/{{+(.*)}}/s';
    $replacements[0] = '';
    $wiki_text = preg_replace($patterns, $replacements, $wiki_text);

    $patterns[0] = '/(<ref[^<]*<\/ref>)/s';
    $replacements[0] = '';
    // Don't use limit for removal of <ref>..</ref>, there are many.
    $wiki_text = preg_replace($patterns, $replacements, $wiki_text);

    // Split in lines which will become <p>..</p>.
    $lines = preg_split('/\n/', $wiki_text);

    // Replace wiki formatting with HTML.
    $patterns_pre = array();
    // == Headings ==
    $patterns_pre[0] = '/====\s*([^=]*)\s*====/';
    $patterns_pre[1] = '/===\s*([^=]*)\s*===/';
    $patterns_pre[2] = '/==\s*([^=]*)\s*==/';
    $patterns_pre[3] = '/=\s*([^=]*)\s*=/';
    // Link to files [[File:*]]
    $patterns_pre[4] = '/\[\[File:([^\]\|]*)\|*([^\]]*)\]\]/';
    // '''''bold+italic''''', '''bold''', ''italic''
    $patterns_pre[5] = "/'''([^']*)'''/";
    $patterns_pre[6] = "/''([^']*)''/";

    $patterns_post = array();
    // Replace [[Category:*]] with ''
    $patterns_post[7] = '/\[\[Image:([\|a-zA-Z\s\:\-\.])*\]\]/';
    $patterns_post[8] = '/\[\[Category:([\|a-zA-Z\s\:\-])*\]\]/';
    // Lists *
    $patterns_post[10] = "/\*+(.*)?.(;)?$/";

    // Replace [url title] with <a href="url">title</a>
    $patterns_post[12] = '/\[([a-zA-Z\:\/\.-]*)\s([\|a-zA-Z\s\:\-]*)\]/';

    $callback_patterns = array();
    $callback_patterns[] = '/\[\[:Category:([\|a-zA-Z\s\:\-\_]*)\|([\|a-zA-Z\s\:\-\_,]*)\]\]/';
    $callback_patterns[] = '/\[\[([–a-zA-Z\s\:\-\_\(\),]*)\|([\|a-zA-Z\s\:\-\_,\.\(\),]*)\]\]/';
    // Replace [[Wiki page title|url]] with <a href='https://oshwiki.eu/Wiki page title'>Wiki page title</a>
    $callback_patterns[] = '/\[\[([a-zA-Z\s\:\-_]+)([\|a-zA-Z\s\:\-])*\]\]/';
    //    $replacements_post[9] = sprintf('<a href="%s${1}">${1}</a>', $osh_wiki_prefix);

    $replacements_pre = array();
    $replacements_pre[0] = '';
    $replacements_pre[1] = '';
    $replacements_pre[2] = '';
    $replacements_pre[3] = '';
    $replacements_pre[4] = '';
    $replacements_pre[5] = '<b>${1}</b>';
    $replacements_pre[6] = '<i>${1}</i>';


    $replacements_post = array();
    $replacements_post[7] = '';
    $replacements_post[8] = '';
    $replacements_post[10] = '<span>- ${1};</span>';

    $replacements_post[12] = '<a target="_blank" href="${1}">${2}</a>';

    $summary = '';
    $char_count = 0;
    foreach ($lines as $line) {
      if ($length = strlen($line)) {
        $char_count += $length;
        $line = preg_replace($patterns_pre, $replacements_pre, $line);

        if ($convert_category) {
          $line = preg_replace_callback(
            $callback_patterns,
            function ($matches) {
              static $osh_wiki_prefix;
              if (!$osh_wiki_prefix) {
//                   $osha_wiki_hostname = variable_get('osha_wiki_hostname', 'https://oshwiki.eu');
//                $osha_wiki_hostname = 'https://oshwiki.eu';
//                $osha_wiki_hostname = \Drupal::state()->get('osha_wiki_hostname', 'https://oshwiki.eu');
                $osha_wiki_hostname = \Drupal::configFactory()->getEditable('oshwiki_import.settings')->get('oshwiki_import_api');
                $osh_wiki_prefix = $osha_wiki_hostname . '/wiki/';
              }
              if (!@$matches[2]) {
                $matches[2] = $matches[1];
              }
              return '<a target="_blank" href="' . $osh_wiki_prefix . str_replace(' ','_', trim($matches[1])) . '">' . $matches[2] . '</a>';
            },
            $line
          );
        }

        $html_line = preg_replace($patterns_post, $replacements_post, $line);
        if (!empty($html_line) && strlen($html_line) >= 2 && "{$html_line[0]}{$html_line[1]}" != '<h') {
          $html_line = '<p>' . $html_line . '</p>';
        }
        //$summary .= $html_line . "\n";
        $summary .= $html_line;
        // Import hard-limit maximum char length.
        if ($char_count > 500) {
          break;
        }
      }
    }
    return $summary;
  }

  private function cleanString($stringToCleanPar)
  {
    $stringToReturn = str_replace("\n", '', $stringToCleanPar);
    $stringToReturn = str_replace("\t", '', $stringToReturn);
    return $stringToReturn;
  }

//   private function osha_wiki_block_content_template($wiki_page_url) {
//     $wiki_name = t('OSHwiki');
//     $goto_wiki = t('Go to OSHwiki');
//     $find_more = t('Find more');
//
//     $content = '<div class="OSHWiki"><div class="separatorOsHWiki">&nbsp;</div><div id="OSHWikiDivTit"><div class="imgOSHWiki"><img src="/sites/all/themes/osha_frontend/images/OSHwiki.png" alt="OSHwiki" width="26" height="26" /></div><div class="OSHWikiTitle">'
//       .$wiki_name
//       .'</div></div><div class="p2">'
//       .$find_more
//       .'<span><br /></span></div><div class="p3"><a href="'
//       .$wiki_page_url
//       .'" target="_blank">'
//       .$goto_wiki
//       .' <img src="/sites/all/themes/osha_frontend/images/flecha.png" alt="'
//       .$goto_wiki
//       .'" width="19" height="11" /></a></div></div>';
//     return $content;
//   }

}
